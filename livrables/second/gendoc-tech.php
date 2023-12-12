<?php
    $HTMLBlocks = [
        "file" => [
            "depth" => 0,
            "block" => "<section>
                            <h3>Fichiers</h3>

                            <nav>
                                <ul class='files'>
                                    [FILES]
                                </ul>
                            </nav>
                        </section>

                        <section id='main.c' class='file-section'>
                            <h2>[FILENAME]</h2>

                            <h3>Chapitres</h3>

                            <nav>
                                <ol type='I'>
                                    [CHAPITRES]
                                </ol>
                            </nav>
                        </section>

                        <section id='en-tete'>
                            <h3>I. En-tête</h3>

                            <p>
                                <em>
                                    [PROJECT-DESCRIPTION]
                                </em>
                            </p>
                        </section>

                        <section id='defines'>
                            <h3>II. Defines</h3>

                            [DEFINES]
                        </section>

                        <section id='structures'>
                            <h3>III. Structures</h3>

                            [STRUCTURES]
                        </section>

                        <section id='globales'>
                            <h3>IV. Globales</h3>

                            [VARS]
                        </section>

                        <section id='fonctions'>
                            <h3>V. Fonctions</h3>

                            [FUNCTIONS]
                        </section>"
        ],
        "fileLink" => [
            "depth" => 1,
            "block" => "<li><a href='#[NOMFICHIER]'>[NOMFICHIER]</a></li>"
        ],
        "chapterLink" => [
            "depth" => 1,
            "block" => "<li><a href='#[LINK]'>[CHAPTER]</a></li>"
        ],
        "item" =>   [
            "depth" => 1,
            "block" =>  "<div class='item'>
                            <h3 class='item-title'>[NAME]</h3>
                            <p>[BRIEF]</p>
                        </div>",
        ],
        "struct" => [
            "depth" => 1,
            "block" => "<div class='dropdown'>
                            <button class='item-title dropdown-trigger'>[NAME]</button>
                            <div class='dropdown-content'>
                                <p>
                                    [BRIEF]
                                </p>

                                [SUBITEM]
                            </div>
                        </div>"
        ],
        "function" => [
            "depth" => 1,
            "block" => "<div class='dropdown'>
                            <button class='item-title dropdown-trigger'>[NAME]</button>
                            <div class='dropdown-content'>
                                <p>
                                    [BRIEF]
                                </p>

                                [SUBITEM]
                            </div>
                        </div>"
        ],
        "subitem" => [
            "depth" => 2,
            "block" =>  "<div class='sub-item'>
                            <h3 class='sub-item-title'>[NAME]</h3>
                            <p>[BRIEF]</p>
                        </div>",
        ],
    ];

    $patterns = [
        "auteur" => '/\$auteur\s+(.*)/',
        "version" => '/\$version\s+(.*)/',
        "date" => '/\$date\s+(.*)/',
        "defines" => '/\s*\$def\s*(.*)/',
        "var" => '/\s*\$var\s*(.*)/',
        "types" => '/\s*\$typedef\s*(.*)/',
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
                    // echo "\n" . $path . "\n";

                    array_push($files, $commandValue);

                    // récupère les includes
                    $includePattern = '/#include\s+"([^"]*)"/m';
                    preg_match_all($includePattern, file_get_contents($commandValue), $includeMatches);

                    foreach ($includeMatches[0] as $include) {
                        // echo str_replace("\"", "", explode(" ", $include)[1]) . "\n";
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
        echo "* " . $data[$i]["name"] . "...\n";
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

                        // gestion des parmaètres de la fonction/procédure (s'il y en a)

                        if (preg_match_all($patterns["functions"]["paramfn"], $commentContent, $paramMatches) != 0) {
                            foreach($paramMatches[0] as $paramInfo) {
                                preg_match($patterns["functions"]["paramfn"], $paramInfo, $paramMatch);
    
                                array_push($data[$i]["contents"]["functions"][$fnCount]["parameters"], [
                                    "name" => explode(' : ', $paramMatch[1])[0],
                                    "description" => explode(' : ', $paramMatch[1])[1],
                                ]);
                            }
                        }

                        // gestion du return de la fonction (s'il y en a)
                        if (preg_match($patterns["functions"]["returnfn"], $paramInfo, $returnInfo) != 0) {
                            array_push($data[$i]["contents"]["functions"][$fnCount]["return"], [
                                "name" => explode(' : ', $returnInfo[1])[0],
                                "description" => explode(' : ', $returnInfo[1])[1],
                            ]);
                        }

                        $fnCount++;
                    }
                // Check pour les structures
                } else if ($patternName == "structs") {
                    // ajout d'une struture si le commentaire actuel contient la variable $nomstruc
                    $isStruct = preg_match($patterns["structs"]["nomstruc"], $commentContent, $nomstruc);

                    // ajout des données de la structure
                    if ($isStruct != 0) {
                        array_push($data[$i]["contents"]["structs"], [
                            "name" => explode(" : ", $nomstruc[1])[0],
                            "components" => [],
                        ]);

                        // gestion des arguments (seulement s'il y en a)
                        if (preg_match_all($patterns["structs"]["argstruc"], $commentContent, $componantMatches)) {
                            foreach ($componantMatches[0] as $componant) {
                                preg_match($patterns["structs"]["argstruc"], $componant, $componantMatch);
    
                                array_push($data[$i]["contents"]["structs"][$structCount]["components"], [
                                    "name" => explode(" : ", $componantMatch[1])[0],
                                    "description" => explode(" : ", $componantMatch[1])[1]
                                ]);
                            }
                        }

                        $structCount++;
                    }
                // Check le reste
                } else {
                    // récupère le match du pattern
                    $isMatching = preg_match($p, $commentContent, $patternMatch);

                    // sauvegarde un match si il y en a un
                    if ($isMatching != 0) {
                        // [1] car on veut uniquement les données du groupe : (.*)
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

    // si on a un fichier
    if (count($data) == 1) {
        $fileData = $data[0];
        $htmlContent = str_replace("[CLIENT]", $fileData["contents"]["auteur"], $htmlContent);
        $htmlContent = str_replace("[VERSION]", $fileData["contents"]["version"], $htmlContent);
        $htmlContent = str_replace("[DATE]", date("%d-%m-%y"), $htmlContent);
        $htmlContent = str_replace("[PROJECT-DESCRIPTION]", $fileData["contents"]["date"], $htmlContent); // TODO: récupérer la description du fichier

        foreach ($patterns as $patternName => $_) {
            // création du html
            $innerHtml = "";

            // véfification du type
            if (gettype($fileData["contents"][$patternName]) == 'array') {
                // echo $patternName . "\n";

                if ($patternName == "structs") {

                } elseif ($patternName == "functions") {

                } else {
                    foreach ($fileData["contents"][$patternName] as $i => $value) {
                        // echo "ajout ". $value . "\n";

                        $innerHtml = $innerHtml . "<div class='item'>
                            <h3 class='item-title'>". "METTER NOM DEFINE" ."</h3>
                            <p>". $value ."</p>
                        </div>\n";

                        // echo $innerHtml . "\n";
                        if ($i == count($fileData["contents"][$patternName]) - 1) {
                            $htmlContent = str_replace("[". strtoupper($patternName) ."]", htmlentities($innerHtml), $htmlContent);
                        }
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