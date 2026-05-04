import os
import re

def fix_file(file_path):
    with open(file_path, 'r', encoding='utf-8') as f:
        content = f.read()

    # Pass 1: resolve outer markers (origin/dev)
    # We want to keep everything from ======= to >>>>>>> origin/dev
    def replace_outer(match):
        part1 = match.group(1)
        part2 = match.group(2)
        return part2

    content = re.sub(r'<<<<<<< HEAD\n(.*?)\n=======\n(.*?)\n>>>>>>> origin/dev\n', replace_outer, content, flags=re.DOTALL)

    # Pass 2: resolve inner markers (9795...)
    # We want to keep everything from ======= to >>>>>>> 9795...
    def replace_inner(match):
        part1 = match.group(1)
        part2 = match.group(2)
        # Exception for ScoreCompetence GeneratedValue which was in HEAD but lost in 9795
        if 'GeneratedValue' in part1 and 'GeneratedValue' not in part2:
            # specifically for ID
            return part1.strip() + '\n' + part2.strip()
        if 'eraseCredentials' in part1 and 'eraseCredentials' not in part2:
            return part1.strip() + '\n' + part2.strip()
        return part2

    content = re.sub(r'<<<<<<< HEAD\n(.*?)\n=======\n(.*?)\n>>>>>>> [a-f0-9]{40}\n', replace_inner, content, flags=re.DOTALL)

    # Manual fixes for Planification CRUD inverses
    if 'Planification.php' in file_path:
        content = content.replace(
            '#[ORM\\ManyToOne(targetEntity: User::class)]\n    #[ORM\\JoinColumn(name: \'user_id\', referencedColumnName: \'id\', nullable: true, onDelete: \'CASCADE\')]\n    private ?User $user = null;',
            '#[ORM\\ManyToOne(targetEntity: User::class, inversedBy: "planifications")]\n    #[ORM\\JoinColumn(name: \'user_id\', referencedColumnName: \'id\', nullable: true, onDelete: \'CASCADE\')]\n    private ?User $user = null;'
        )
        content = content.replace(
            '#[ORM\\ManyToOne(targetEntity: Candidature::class)]\n    #[ORM\\JoinColumn(name: \'fk_candidature_id\', referencedColumnName: \'id_candidature\', nullable: true, onDelete: \'CASCADE\')]\n    private ?Candidature $candidature = null;',
            '#[ORM\\ManyToOne(targetEntity: Candidature::class, inversedBy: "planifications")]\n    #[ORM\\JoinColumn(name: \'fk_candidature_id\', referencedColumnName: \'id_candidature\', nullable: true, onDelete: \'CASCADE\')]\n    private ?Candidature $candidature = null;'
        )

    with open(file_path, 'w', encoding='utf-8') as f:
        f.write(content)

fix_file('src/Entity/Planification.php')
fix_file('src/Entity/Evaluation.php')
fix_file('src/Entity/User.php')
print("Fixed remaining files")
