<?php
function fix2($f) {
    if (!file_exists($f)) return;
    $content = file_get_contents($f);
    
    // Replace <<<<<<< HEAD\n...\n=======\n with empty
    $content = preg_replace('/<<<<<<< HEAD.*?=======\r?\n/s', '', $content);
    
    // Replace >>>>>>> <hash> with nothing (leaving the code intact)
    $content = preg_replace('/>>>>>>>\s+[a-f0-9]{40}/', '', $content);
    $content = preg_replace('/>>>>>>>\s+origin\/dev/', '', $content);

    file_put_contents($f, $content);
}

fix2('src/Entity/Evaluation.php');
fix2('src/Entity/Planification.php');
fix2('src/Entity/User.php');
fix2('src/Entity/ScoreCompetence.php');
echo "Done2.\n";
