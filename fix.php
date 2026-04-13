<?php

function fix_file($file_path) {
    if (!file_exists($file_path)) return;
    echo "Fixing {$file_path}...\n";
    $content = file_get_contents($file_path);

    // Pass 1: resolve outer markers (origin/dev)
    $content = preg_replace_callback('/<<<<<<< HEAD\r?\n(.*?)\r?\n=======\r?\n(.*?)\r?\n>>>>>>> origin\/dev\r?\n/s', function($matches) {
        return $matches[2];
    }, $content);

    // Pass 2: resolve inner markers (9795...)
    $content = preg_replace_callback('/<<<<<<< HEAD\r?\n(.*?)\r?\n=======\r?\n(.*?)\r?\n>>>>>>> [a-f0-9]{40}\r?\n/s', function($matches) {
        $part1 = $matches[1];
        $part2 = $matches[2];
        if (strpos($part1, 'eraseCredentials') !== false && strpos($part2, 'eraseCredentials') === false) {
            return trim($part1) . "\n" . trim($part2);
        }
        return $part2;
    }, $content);

    // Manual fixes for Planification CRUD inverses
    if (strpos($file_path, 'Planification.php') !== false) {
        $content = str_replace(
            "#[ORM\\ManyToOne(targetEntity: User::class)]",
            "#[ORM\\ManyToOne(targetEntity: User::class, inversedBy: \"planifications\")]",
            $content
        );
        $content = str_replace(
            "#[ORM\\ManyToOne(targetEntity: Candidature::class)]",
            "#[ORM\\ManyToOne(targetEntity: Candidature::class, inversedBy: \"planifications\")]",
            $content
        );
    }
    
    // Evaluation inverse
    if (strpos($file_path, 'Evaluation.php') !== false) {
        $content = str_replace(
            "#[ORM\\ManyToOne(targetEntity: User::class)]",
            "#[ORM\\ManyToOne(targetEntity: User::class, inversedBy: \"evaluationsAsCandidat\")]",
            $content
        );
    }

    file_put_contents($file_path, $content);
}

fix_file('src/Entity/Planification.php');
fix_file('src/Entity/Evaluation.php');
fix_file('src/Entity/User.php');

echo "Done.\n";
