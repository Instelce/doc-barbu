<?php
    $patterns = [
        "auteur" => '/\$auteur\s+(.*)/',
        "version" => '/\$version\s+(.*)/',
        "date" => '/\$date\s+(.*)/',
        "def" => '/\*\s*\$def\s*(.*)/',
    ];
    $data = [
        "auteur" => "",
        "version" => "",
        "date" => "",
        "def" => [],
    ];

    // contenue du fichier
    $filename = '../first/src1.c';
    $content = file_get_contents($filename);

    // récupère tous les commentaires
    $pattern = '/(\/\/.*$|\/\*[\s\S]*?\*\/)/m';
    preg_match_all($pattern, $content, $matches);

    foreach ($matches[0] as $comment) {
        // supprime '/*' et '*/' des commentaires
        $commentContent = preg_replace('/^\/\/\s?|^\/\*\s?|\*\/$/', '', $comment);

        foreach ($patterns as $patternName => $p) {
            // récupère le match du pattern
            $isMatching = preg_match($p, $commentContent, $patternMatch);

            // sauvegarde un match si il y en a un
            if ($isMatching != 0) {
                // [1] car on veut uniquement les données du groupe : (.*)
                echo $patternMatch[1] . "\n";
                echo gettype($data[$patternName]);

                if (gettype($data[$patternName]) == 'array') {
                    array_push($data[$patternName], $patternMatch[1]);
                } else if (gettype($data[$patternName]) == 'string') {
                    $data[$patternName] = rtrim($patternMatch[1]);
                }
            }
        }
    }

    file_put_contents("./data/tech.json", json_encode($data, JSON_PRETTY_PRINT));
?>