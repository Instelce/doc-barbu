<?php
    $patterns = [
        "auteur" => '/\$auteur\s+(.*)/',
        "version" => '/\$version\s+(.*)/',
        "date" => '/\$date\s+(.*)/',
        "defines" => '/\s*\$def\s*(.*)/',
        "var" => '/\s*\$var\s*(.*)/',
        "structs" => [
            "nomstruc" => '/\s*\$nomstruc\s*(.*)/',
            "argstruc" => '/\s*\$argstruc\s*(.*)/',
        ],
        "functions" => [
            "nomfn" => '/\s*\$fn\s*(.*)/',
            "paramfn" => '/\s*\$param\s*(.*)/',
            "returnfn" => '/\s*\$return\s*(.*)/'
        ],
    ];

    // plusieurs commandes
    // --dir <dirname>              Cherche tous les fichiers présents dans le dossier et génère leur documentation.
    // --main <main_program_file>   Génère la documentation du fichier principal et des fichiers importés
    // --onefile <file_name>        Génère la documentation d'un fichier
    // --help                       Donne la documentation des commandes

    $commands = ["--dir", "--main", "--onefile"];
    $files = [];

    if (count($argv) > 1) {
        $command = $argv[1];
        $commandValue = $argv[2];

        if (!in_array($command, $commands)) {
            exit("Cette commande n'existe pas.");
        } else if ($commandValue == '') {
            exit("Veuillez saisir la valeur de la commande.");
        } else {
            if ($command == "--dir") {
                $files = glob(getcwd() . DIRECTORY_SEPARATOR . $commandValue . DIRECTORY_SEPARATOR . "*.c");

                echo getcwd() . "\n";
                // print_r($files);
            }

            if (file_exists($commandValue)) {
                if ($command == "--onefile") {
                    array_push($files, $commandValue);
                }

                if ($command == "--main") {
                    $path = explode(DIRECTORY_SEPARATOR, $commandValue);
                    array_pop($path);
                    $path = join(DIRECTORY_SEPARATOR, $path);
                    echo "\n" . $path . "\n";

                    array_push($files, $commandValue);

                    // récupère les includes
                    $includePattern = '/#include\s+"([^"]*)"/m';
                    preg_match_all($includePattern, file_get_contents($commandValue), $includeMatches);

                    foreach ($includeMatches[0] as $include) {
                        echo str_replace("\"", "", explode(" ", $include)[1]) . "\n";
                        $headerFileName = str_replace("\"", "", explode(" ", $include)[1]);
                        $cFileName = str_replace("h", "c", $headerFileName);

                        array_push($files, $path . DIRECTORY_SEPARATOR . $headerFileName);

                        if (file_exists($path . DIRECTORY_SEPARATOR . $cFileName)) {
                            array_push($files, $path . DIRECTORY_SEPARATOR . $cFileName);
                        }
                    }
                }
            } else {
                exit("Le fichier n'existe pas !");
            }

            echo "Génération de la documentation...\n";
        }
    } else {
        exit("Aucune commande trouvée.");
    }

    // $files = ["../first/src1.c", "../first/src2.c", "../first/src3.c"];
    $data = [];

    // initialisation des données
    foreach ($files as $path) {
        $lastIndex = count(explode("/", $path)) - 1;
        array_push($data, [
            "name" => explode("/", $path)[$lastIndex],
            "path" => $path,
            "contents" => [
                "auteur" => "",
                "version" => "",
                "date" => "",
                "defines" => [],
                "var" => [],
                "structs" => [],
                "functions" => []
            ]
        ]);
    }

    // récupération des données des commentaires pour tous les fichiers
    foreach ($files as $i => $filePath) {
        $content = file_get_contents($filePath);

        // récupère tous les commentaires
        $commentPattern = '/(\/\/.*$|\/\*[\s\S]*?\*\/)/m';
        preg_match_all($commentPattern, $content, $matches);

        $structCount = 0;
        $fnCount = 0;

        foreach ($matches[0] as $comment) {
            // supprime '/*' et '*/' des commentaires
            $commentContent = preg_replace('/^\/\/\s?|^\/\*\s?|\*\/$/', '', $comment);
            // echo '--' . $commentContent . "\n";

            foreach ($patterns as $patternName => $p) {

                // Check pour les fonctions
                if($patternName == "functions") {
                    $isFunc = preg_match($patterns["functions"]["nomfn"], $commentContent, $nomfn) ;
                    
                    if ($isFunc != 0) {
                        array_push($data[$i]["contents"]["functions"], [
                            "name" => explode(" ", $nomfn[1])[0],
                            "parameters" => [],
                            "return" => []
                        ]);

                        // gestion des parmaètres de la fonction/procédure
                        preg_match_all($patterns["functions"]["paramfn"], $commentContent, $paramMatches);

                        foreach($paramMatches[0] as $paramInfo) {
                            preg_match($patterns["functions"]["paramfn"], $paramInfo, $paramMatch);

                            array_push($data[$i]["contents"]["functions"][$fnCount]["parameters"], [
                                "name" => explode(' : ', $paramMatch[1])[0],
                                "description" => explode(' : ', $paramMatch[1])[1],
                            ]);
                        }

                        // gestion du return de la fonction
                        preg_match($patterns["functions"]["paramfn"], $paramInfo, $returnInfo);

                        array_push($data[$i]["contents"]["functions"][$fnCount]["return"], [
                            "name" => explode(' : ', $returnInfo[1])[0],
                            "description" => explode(' : ', $returnInfo[1])[1],
                        ]);

                        $fnCount++;

                    }


                } else if ($patternName == "structs") {
                    // ajout d'une struture si le commentaire actuel contient la variable $nomstruc
                    $isStruct = preg_match($patterns["structs"]["nomstruc"], $commentContent, $nomstruc);

                    // ajout des données de la structure
                    if ($isStruct != 0) {
                        array_push($data[$i]["contents"]["structs"], [
                            "name" => explode(" : ", $nomstruc[1])[0],
                            "components" => [],
                        ]);

                        preg_match_all($patterns["structs"]["argstruc"], $commentContent, $componantMatches);

                        foreach ($componantMatches[0] as $componant) {
                            preg_match($patterns["structs"]["argstruc"], $componant, $componantMatch);

                            array_push($data[$i]["contents"]["structs"][$structCount]["components"], [
                                "name" => explode(" : ", $componantMatch[1])[0],
                                "description" => explode(" : ", $componantMatch[1])[1]
                            ]);
                        }

                        $structCount++;
                    }
                } else {
                    // récupère le match du pattern
                    $isMatching = preg_match($p, $commentContent, $patternMatch);

                    // sauvegarde un match si il y en a un
                    if ($isMatching != 0) {
                        // [1] car on veut uniquement les données du groupe : (.*)
                        // echo $patternName . " : " . $patternMatch[1] . "\n";
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

    $htmlContent = file_get_contents("./data/DOC_TECHNIQUE_TEMPLATE.html");

    echo $data[0]["contents"]["auteur"] . "\n";

    // si on a un fichier
    if (count($data) == 1) {
        $fileData = $data[0];
        $htmlContent = str_replace("[CLIENT]", $fileData["contents"]["auteur"], $htmlContent);
        $htmlContent = str_replace("[VERSION]", $fileData["contents"]["version"], $htmlContent);
        $htmlContent = str_replace("[DATE]", $fileData["contents"]["date"], $htmlContent);

        foreach ($patterns as $patternName => $_) {
            // création du html
            $innerHtml = "";
            if (gettype($fileData["contents"][$patternName]) == 'array') {
                echo $patternName . "\n";

                if ($patternName == "structs") {

                } else {
                    foreach ($fileData["contents"][$patternName] as $value) {
                        $innerHtml = $innerHtml . "<div class='item'>
                            <h3 class='item-title'>". "METTER NOM DEFINE" ."</h3>
                            <p>". $value ."</p>
                        </div>";
                    }
                }
            }

        }
    } else {
        foreach ($files as $file) {

        }
    }



    file_put_contents("./data/tech.json", json_encode($data, JSON_PRETTY_PRINT));
    file_put_contents("./data/DOC_TECHNIQUE.html", $htmlContent);
?>