<?php

namespace App\Command;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Schema\Column;
use Doctrine\DBAL\Schema\Table;
use Symfony\Component\Console\Attribute\AsCommand;
use Doctrine\DBAL\Schema\AbstractSchemaManager;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Filesystem\Filesystem;

#[AsCommand(
    name: 'app:generate:entities',
    description: 'Automatically generates entity classes from the database schema',
)]
class GenerateEntitiesCommand extends Command
{
    private Connection $connection;
    private ?AbstractSchemaManager $schemaManager = null;
    private array $generatedRelations = [];

    public function __construct(Connection $connection, Filesystem $filesystem)
    {
        parent::__construct();
        $this->connection = $connection;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $io->title("Generating Entity Classes from Database...");

        try {
            $schemaManager = $this->getSchemaManager();
            $tables = $schemaManager->listTables();
        } catch (\Exception $e) {
            $io->error("Failed to retrieve database schema: " . $e->getMessage());
            return Command::FAILURE;
        }

        $oneToManyRelations = [];
        $manyToOneRelationsName = [];
        $oneToManyRelationsName = [];

        // Sort tables by FK count ascending so referenced tables are generated first
        $tableRelationsCount = [];
        foreach ($tables as $table) {
            $foreignKeys = $this->getForeignKeys([$table->getName()]);
            $tableRelationsCount[$table->getName()] = count($foreignKeys);
        }
        usort($tables, function (Table $a, Table $b) use ($tableRelationsCount) {
            return $tableRelationsCount[$a->getName()] <=> $tableRelationsCount[$b->getName()];
        });

        // FIX: Only generate once, not twice
        foreach ($tables as $table) {
            $this->generateEntity(
                $table,
                $oneToManyRelations,
                $manyToOneRelationsName,
                $oneToManyRelationsName
            );
            $io->success("Generated: src/Entity/" . $this->toClassName($table->getName()) . ".php");
        }

        $io->success("Entities successfully generated in src/Entity/");
        return Command::SUCCESS;
    }

    private function getSchemaManager(): AbstractSchemaManager
    {
        if ($this->schemaManager === null) {
            $this->schemaManager = $this->connection->createSchemaManager();
        }
        return $this->schemaManager;
    }

    // FIX: Convert snake_case table names to PascalCase class names
    // e.g. type_entretien -> TypeEntretien
    private function toClassName(string $tableName): string
    {
        return str_replace('', '', ucwords($tableName, ''));
    }

    // FIX: Convert snake_case to camelCase for property/method names
    // e.g. date_heure_debut -> dateHeureDebut
    private function toCamelCase(string $name): string
    {
        return lcfirst(str_replace('', '', ucwords($name, '')));
    }

    private function generateEntity(
        Table $table,
        array &$oneToManyRelations,
        array &$manyToOneRelationsName,
        array &$oneToManyRelationsName
    ): void {
        $tableName  = $table->getName();
        $className  = $this->toClassName($tableName);

        $primaryKeys = $table->getPrimaryKey()?->getColumns() ?? [];
        $foreignKeys = $this->getForeignKeys([$tableName]);

        // Detect auto-increment columns
        $autoIncrementCols = [];
        foreach ($table->getColumns() as $col) {
            if ($col->getAutoincrement()) {
                $autoIncrementCols[] = $col->getName();
            }
        }

        // --- Build property + relation blocks ---
        $propertyLines = '';
        $fkColumnNames = []; // track which columns are FKs so we skip their getters/setters

        foreach ($table->getColumns() as $column) {
            $colName      = $column->getName();
            $isPrimaryKey = in_array($colName, $primaryKeys);
            $isAutoInc    = in_array($colName, $autoIncrementCols);
            $isForeignKey = isset($foreignKeys[$colName]);

            if ($isForeignKey) {
                $fkColumnNames[] = $colName;
                $relatedTable     = $foreignKeys[$colName]['referencedTable'];
                $relatedClassName = $this->toClassName($relatedTable);
                $refColumn        = $foreignKeys[$colName]['referencedColumn'];

                // Property name = camelCase of the related class (not the FK column name)
                $propName = $this->toCamelCase($relatedClassName);

                // FIX: nullable ManyToOne (FK column may be nullable)
                $isNullable = !$column->getNotnull();
                $nullableStr = $isNullable ? ', nullable: true' : '';
                $phpType     = $isNullable ? "?$relatedClassName" : $relatedClassName;
                $defaultVal  = $isNullable ? ' = null' : '';

                $propertyLines .= "\n";
                $propertyLines .= "    #[ORM\\ManyToOne(targetEntity: {$relatedClassName}::class, inversedBy: \"" . lcfirst($className) . "s\")]\n";
                $propertyLines .= "    #[ORM\\JoinColumn(name: '$colName', referencedColumnName: '$refColumn'{$nullableStr}, onDelete: 'CASCADE')]\n";
                $propertyLines .= "    private {$phpType} \${$propName}{$defaultVal};\n";

                // Track for imports and OneToMany injection
                $manyToOneRelationsName[$className]    = $relatedClassName;
                $oneToManyRelationsName[$relatedClassName] = $className;

                $oneToManyRelations[$relatedClassName][] = [
                    'propertyName' => lcfirst($className) . 's',
                    'mappedBy'     => $colName,
                    'targetEntity' => $className,
                    'propName'     => $propName,
                ];

            } else {
                // Regular column
                $doctrineType      = $this->getDoctrineType($column);
                $phpType           = $this->getPHPTypeFromDoctrine($doctrineType);
                $isNullable        = !$column->getNotnull();
                $lengthAnnotation  = ($doctrineType === 'string' && $column->getLength())
                    ? ", length: " . $column->getLength()
                    : '';
                $nullableAnnotation = $isNullable ? ', nullable: true' : '';

                // FIX: nullable PHP types and default null
                $phpTypeHinted = $isNullable ? "?$phpType" : $phpType;
                $defaultVal    = $isNullable ? ' = null' : '';

                $propertyLines .= "\n";
                if ($isPrimaryKey) {
                    $propertyLines .= "    #[ORM\\Id]\n";
                    if ($isAutoInc) {
                        // FIX: add GeneratedValue for AUTO_INCREMENT PKs
                        $propertyLines .= "    #[ORM\\GeneratedValue]\n";
                    }
                }
                $propertyLines .= "    #[ORM\\Column(type: \"{$doctrineType}\"{$lengthAnnotation}{$nullableAnnotation})]\n";
                $propertyLines .= "    private {$phpTypeHinted} \${$this->toCamelCase($colName)}{$defaultVal};\n";
            }
        }

        // --- Build getters/setters (skip FK raw columns) ---
        $methodLines = '';
        foreach ($table->getColumns() as $column) {
            $colName = $column->getName();
            if (in_array($colName, $fkColumnNames)) {
                continue; // FK columns are exposed via their relation property
            }
            $methodLines .= $this->generateGettersAndSetters($column);
        }

        // --- Build OneToMany blocks for this entity ---
        $oneToManyLines = '';
        if (isset($oneToManyRelations[$className])) {
            $seen = [];
            foreach ($oneToManyRelations[$className] as $rel) {
                $key = $rel['targetEntity'] . '-' . $rel['mappedBy'];
                if (isset($seen[$key])) continue;
                $seen[$key] = true;

                $collPropName = lcfirst($rel['targetEntity']) . 's';
                $targetClass  = $rel['targetEntity'];

                $oneToManyLines .= "\n";
                $oneToManyLines .= "    #[ORM\\OneToMany(mappedBy: \"{$rel['mappedBy']}\", targetEntity: {$targetClass}::class, cascade: [\"persist\", \"remove\"])]\n";
                $oneToManyLines .= "    private Collection \${$collPropName};\n";

                // add/remove/get methods
                $oneToManyLines .= $this->generateRelationMethods($className, $rel['mappedBy'], $targetClass);
            }
        }

        // --- Build imports ---
        $importLines = $this->generateImports($manyToOneRelationsName, $oneToManyRelationsName, $className);

        // --- Assemble final file ---
        // FIX: Add #[ORM\Table] so Doctrine maps to the correct table name
        $entityCode  = "<?php\n\nnamespace App\\Entity;\n\n";
        $entityCode .= "use Doctrine\\ORM\\Mapping as ORM;\n";
        if (!empty($importLines)) {
            $entityCode .= $importLines;
        }
        $entityCode .= "\n#[ORM\\Entity]\n";
        $entityCode .= "#[ORM\\Table(name: \"{$tableName}\")]\n";
        $entityCode .= "class {$className}\n{\n";
        $entityCode .= $propertyLines;
        $entityCode .= $methodLines;
        $entityCode .= $oneToManyLines;
        $entityCode .= "}\n";

        $filePath = __DIR__ . "/../../src/Entity/{$className}.php";
        file_put_contents($filePath, $entityCode);
    }

    private function generateImports(
        array $manyToOneRelationsName,
        array $oneToManyRelationsName,
        string $className
    ): string {
        $imports = [];

        if (isset($manyToOneRelationsName[$className])) {
            $imports[] = "use App\\Entity\\{$manyToOneRelationsName[$className]};";
        }
        if (isset($oneToManyRelationsName[$className])) {
            $imports[] = "use Doctrine\\Common\\Collections\\Collection;";
            $imports[] = "use Doctrine\\Common\\Collections\\ArrayCollection;";
            $imports[] = "use App\\Entity\\{$oneToManyRelationsName[$className]};";
        }

        $imports = array_unique($imports);
        return empty($imports) ? '' : implode("\n", $imports) . "\n";
    }

    public function getForeignKeys(array $tables): array
    {
        $foreignKeys = [];
        $schemaManager = $this->connection->createSchemaManager();
        $dbTableNames = array_map(fn($t) => $t->getName(), $schemaManager->listTables());

        foreach ($tables as $tableName) {
            if (!in_array($tableName, $dbTableNames)) continue;

            $sql = "
                SELECT COLUMN_NAME, REFERENCED_TABLE_NAME, REFERENCED_COLUMN_NAME
                FROM INFORMATION_SCHEMA.KEY_COLUMN_USAGE
                WHERE TABLE_NAME = :tableName
                  AND TABLE_SCHEMA = DATABASE()
                  AND REFERENCED_TABLE_NAME IS NOT NULL
            ";
            // FIX: Added AND TABLE_SCHEMA = DATABASE() to avoid cross-database false positives

            $stmt = $this->connection->prepare($sql);
            $stmt->bindValue(':tableName', $tableName);
            $fks = $stmt->executeQuery()->fetchAllAssociative();

            foreach ($fks as $fk) {
                $foreignKeys[$fk['COLUMN_NAME']] = [
                    'referencedTable'  => $fk['REFERENCED_TABLE_NAME'],
                    'referencedColumn' => $fk['REFERENCED_COLUMN_NAME'],
                ];
            }
        }

        return $foreignKeys;
    }

    // FIX: Extracted type mapping to a proper method using column object (not just class name)
    private function getDoctrineType(Column $column): string
    {
        $typeClass = get_class($column->getType());
        return match (true) {
            str_contains($typeClass, 'BigIntType')     => 'bigint',
            str_contains($typeClass, 'SmallIntType')   => 'smallint',
            str_contains($typeClass, 'IntegerType')    => 'integer',
            str_contains($typeClass, 'BooleanType')    => 'boolean',
            str_contains($typeClass, 'DateTimeType')   => 'datetime',
            str_contains($typeClass, 'DateType')       => 'date',
            str_contains($typeClass, 'TimeType')       => 'time',
            str_contains($typeClass, 'TextType')       => 'text',
            str_contains($typeClass, 'JsonType')       => 'json',
            str_contains($typeClass, 'FloatType')      => 'float',
            str_contains($typeClass, 'DecimalType')    => 'decimal',
            str_contains($typeClass, 'GuidType')       => 'guid',
            str_contains($typeClass, 'BlobType')       => 'blob',
            default                                    => 'string',
        };
    }

    private function getPHPTypeFromDoctrine(string $doctrineType): string
    {
        return match ($doctrineType) {
            'integer', 'smallint'  => 'int',
            'bigint'               => 'string',
            'boolean'              => 'bool',
            'float', 'decimal'     => 'float',
            'date', 'datetime',
            'datetimetz', 'time'   => '\DateTimeInterface',
            'json'                 => 'array',
            default                => 'string',
        };
    }

    private function getPrimaryKeyColumns(string $tableName): array
    {
        $indexes = $this->getSchemaManager()->listTableIndexes($tableName);
        return isset($indexes['primary']) ? $indexes['primary']->getColumns() : [];
    }

    private function generateRelationMethods(
        string $currentEntity,
        string $mappedBy,
        string $relatedEntity
    ): string {
        $relatedClass    = $relatedEntity;
        $relatedVar      = lcfirst($relatedEntity);
        $collectionProp  = $relatedVar . 's';
        $setterName      = 'set' . ucfirst($this->toCamelCase($mappedBy));

        return "
    public function get{$relatedClass}s(): Collection
    {
        return \$this->{$collectionProp};
    }

    public function add{$relatedClass}({$relatedClass} \${$relatedVar}): static
    {
        if (!\$this->{$collectionProp}->contains(\${$relatedVar})) {
            \$this->{$collectionProp}->add(\${$relatedVar});
            \${$relatedVar}->{$setterName}(\$this);
        }
        return \$this;
    }

    public function remove{$relatedClass}({$relatedClass} \${$relatedVar}): static
    {
        if (\$this->{$collectionProp}->removeElement(\${$relatedVar})) {
            if (\${$relatedVar}->{$setterName}() === \$this) {
                \${$relatedVar}->{$setterName}(null);
            }
        }
        return \$this;
    }\n";
    }

    private function generateGettersAndSetters(Column $column): string
    {
        $colName    = $column->getName();
        $propName   = $this->toCamelCase($colName);
        $methodName = ucfirst($propName);
        $isNullable = !$column->getNotnull();
        $doctrineType = $this->getDoctrineType($column);
        $phpType    = $this->getPHPTypeFromDoctrine($doctrineType);
        $returnType = $isNullable ? "?$phpType" : $phpType;

        return "
    public function get{$methodName}(): {$returnType}
    {
        return \$this->{$propName};
    }

    public function set{$methodName}({$returnType} \$value): static
    {
        \$this->{$propName} = \$value;
        return \$this;
    }\n";
    }
}