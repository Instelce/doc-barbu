<?php
    $patterns = [
        "auteur" => '/\$auteur\s+(.*)/',
        "version" => '/\$version\s+(.*)/',
        "date" => '/\$date\s+(.*)/',
        "def" => '/\*\s*\$def\s*(.*)/',
        "structs" => [
            "nomstruc" => '/\*\s*\$nomstruc\s*(.*)/',
            "argstruc" => '/\*\s*\$argstruc\s*(.*)/',
        ]
    ];

    $files = ["../first/src1.c", "../first/src2.c", "../first/src3.c"];
    $data = [];

    // initialisation des données
    foreach ($files as $path) {
        array_push($data, [
            "name" => explode("/", $path)[2],
            "path" => $path,
            "contents" => [
                "auteur" => "",
                "version" => "",
                "date" => "",
                "def" => [],
                "structs" => [
                    // [
                    //     "name": "",
                    //     "args": [
                    //         [
                    //             "nom": "",
                    //             "description": ""
                    //         ],
                    //         [
                    //             "nom": "",
                    //             "description": ""
                    //         ]
                           
                    //     ]
                    // ]
                ]
            ]
        ]);
    }

    // récupération des données des commentaires pour tous les fichiers
    foreach ($files as $i => $filePath) {
        $content = file_get_contents($filePath);

        // récupère tous les commentaires
        $pattern = '/(\/\/.*$|\/\*[\s\S]*?\*\/)/m';
        preg_match_all($pattern, $content, $matches);

        foreach ($matches[0] as $comment) {
            // supprime '/*' et '*/' des commentaires
            $commentContent = preg_replace('/^\/\/\s?|^\/\*\s?|\*\/$/', '', $comment);
            //echo '--' . $commentContent . "\n";

            foreach ($patterns as $patternName => $p) {
                // récupère le match du pattern
                
                // sauvegarde un match si il y en a un
                if ($patternName == "structs") {
                    $isStruct = preg_match($pattern["structs"]["nomstruc"], $commentContent, $nomstruc);

                    if ($isStruct != 0) {
                        array_push($data[$i]["contents"]["structs"], [
                            "name" => $nomstruc[1],
                            "components" => [],
                        ]);
                        
                        preg_match_all($pattern["structs"]["argstruc"], $commentContent, $componantMatches);

                        foreach ($componantMatches[0] as $componant) {
                            array_push($data[$i]["contents"]["structs"]["components"], $componant[1]);
                        }
                    }
                } else {
                    $isMatching = preg_match($p, $commentContent, $patternMatch);
                    if ($isMatching != 0) {
                        // [1] car on veut uniquement les données du groupe : (.*)
                        echo $patternMatch[1] . "\n";
                        // echo gettype($data[$patternName]);

                        $type = gettype($data[$i]["contents"][$patternName]);
                        if ($type == 'array') {
                            array_push($data[$i]["contents"][$patternName], $patternMatch[1]);
                        } else if ($type == 'string') {
                            $data[$i]["contents"][$patternName] = rtrim($patternMatch[1]);
                        }
                    }
                }
            }
        }

    }

    file_put_contents("./data/tech.json", json_encode($data, JSON_PRETTY_PRINT));
?>