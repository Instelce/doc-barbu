<?php
    $HTMLBlocks = [
        "file" => [
            "depth" => 0,
            "block" => "<section id='[FILENAME]' class='file-section'>
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

                            [VAR]
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
                            <h4 class='item-title'>[NAME]</h4>
                            <p>[BRIEF]</p>
                        </div>",
        ],
        "dropdown" => [
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
                            <h4 class='sub-item-title'>[NAME]</h4>
                            <p>[BRIEF]</p>
                        </div>",
        ],
    ];

    $dataPatterns = [
        "auteur" => '/\$auteur\s+(.*)/',
        "version" => '/\$version\s+(.*)/',
        "brief" => '/\$brief\s+(.*)/',
        "define" => [
            "foundLine" => '/\#define\s+(.*)/',
            "name" => '/\#define\s+(\w+)/',
            "value" => '/\#define\s+\w+\s+(\w+)/',
            "brief" => '/\$def\s+(.*)/',
        ],
        "global" => [
            "foundLine" => '/.*\$var.*/',  // only to search for the element
            "type" => '/^\s*(\w+)\s+\w+\s*;/',
            "name" => '/^\s*\w+\s+(\w+)\s*;/',
            "brief" => '/\/\*\s*\$var\s+(.*)\s*\*\//',
        ],
        "type" => '/\$typedef\s+\((.*)\)/',
        "struct" => '/\$typedef\s+\(struct\)/',
        "prototype" => '/\$fn\s+(.*)/',
        "param" => '/\$param\s+(.*)/m',   // need prototype or type
        "return" => '/\$return\s*(.*)/', // need prototype
        "param+" => [
            "type" => '/\((.*?)\)/',
            "name" => '/\)\s+(.*?)\s+\:/',
            "brief" => '/\:\s+(.*?)$/'
        ]
    ];

    $sections = [
        "defines" => [
            "define"
        ],
        "globals" => [
            "global"
        ],
        "types" => [
            "type",
            "param"
        ],
        // "structures" => [
        //     "type",
        //     "param"
        // ],
        "fonctions" => [
            "prototype",
            "param",
            "return"
        ]
    ];

    $patterns = [
        "auteur" => '/\$auteur\s+(.*)/',
        "version" => '/\$version\s+(.*)/',
        "defines" => '/\s*\$def\s*(.*)/',
        "globals" => '/\s*\$var\s*(.*)/',
        "types" => '/\s*\$typedef\s*(.*)/',
        "structures" => [
            "struc" => '/\s*\$struc\s*(.*)/',
            "param" => '/\s*\$param\s*(.*)/',
        ],
        "fonctions" => [
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
        $lastIndex = count(explode(DIRECTORY_SEPARATOR, $path)) - 1;
        array_push($data, [
            "name" => explode(DIRECTORY_SEPARATOR, $path)[$lastIndex],
            "path" => $path,
            "auteur" => "",
            "version" => "",
            "contents" => [
                "defines" => [],
                "globals" => [],
                "types" => [],
                "structures" => [],
                "fonctions" => []
            ]
        ]);
    }

    // boucle les fichiers
    foreach ($files as $i => $filePath) {
        echo "* " . $data[$i]["name"] . "...\n";
        $fileContent = file_get_contents($filePath);
        $fileLines = explode("\n", $fileContent);

        // récupère tous les commentaires
        $commentPattern = '/(\/\/.*$|\/\*[\s\S]*?\*\/)/m';
        preg_match_all($commentPattern, $fileContent, $commentMatches);

        // donnees de l'entête
        $enteteData = $commentMatches[0][0];
        $data[$i]["auteur"] = getRegexGroup($dataPatterns["auteur"], $enteteData);
        $data[$i]["version"] = getRegexGroup($dataPatterns["version"], $enteteData);

        $structCount = 0;
        $fnCount = 0;

        foreach ($sections as $sectionName => $section) {
            // parcour par matches
            if ($sectionName == "defines" || $sectionName == "globals") {
                $singularSectionName = convertToSingular($sectionName);

                // vérifie si la section va contenir plusieurs objet
                if (gettype($dataPatterns[$singularSectionName]) == "array") {
                    preg_match_all($dataPatterns[$singularSectionName]["foundLine"], $fileContent, $sectionMatches);

                    // loop toute les lignes matchant avec la section
                    foreach ($sectionMatches[0] as $sectionMatch) {
                        foreach ($section as $sectionFieldName) {
                            $sectionLenght = count($data[$i]["contents"][$sectionName]);
                            foreach ($dataPatterns[$singularSectionName] as $patternName => $pattern) {
                                $data[$i]["contents"][$sectionName][$sectionLenght][$patternName] = getRegexGroup($pattern, $sectionMatch);
                            }
                        }
                    }
                }
            }

            // parcour par block de commentaire
            else {
                foreach ($commentMatches[0] as $commentMatch) {
                    foreach ($section as $sectionFieldName) {
                        $isMatching = preg_match($dataPatterns[$sectionFieldName], $commentMatch, $sectionMatch);
                        if ($section != "structures") {
                            if ($sectionFieldName == "type") {
                                // $isMatching = preg_match($dataPatterns[$sectionFieldName], $commentMatch, $paramLineMatch);
                                if ($isMatching) {
                                    echo $sectionMatch[0];

                                    if ($sectionMatch[1] == "struct") {
                                        $sectionName = "structures";
                                    } else {
                                        $sectionName = "types";
                                    }
                                    $sectionLenght = count($data[$i]["contents"][$sectionName]);

                                    $data[$i]["contents"][$sectionName][$sectionLenght][$sectionFieldName] = $sectionMatch[1];

                                    if (in_array("param", $section) && $sectionName = "structures") {
                                        if (!array_key_exists("params", $data[$i]["contents"][$sectionName][$sectionLenght])) {
                                            $data[$i]["contents"][$sectionName][$sectionLenght]["params"] = [];
                                        }
                                        echo $commentMatch;
                                        $asParams = preg_match_all($dataPatterns["param"], $commentMatch, $paramsMatches);
                                        print_r($paramsMatches);
                                        if ($asParams) {
                                            foreach ($paramsMatches[0] as $paramMatch) {
                                                $paramLenght = count($data[$i]["contents"][$sectionName][$sectionLenght]["params"]);
                                                foreach ($dataPatterns["param+"] as $paramFieldName => $pattern) {
                                                    $data[$i]["contents"][$sectionName][$sectionLenght]["params"][$paramLenght][$paramFieldName] = getRegexGroup($pattern, $paramMatch);
                                                }
                                            }
                                        }
                                    }
                                }

                            } elseif ($sectionFieldName == "param") {
                            } else {

                            }
                        }
                    }
                }
            }
        }

        // foreach ($matches[0] as $comment) {
        //     // supprime '/*' et '*/' des commentaires
        //     $commentContent = preg_replace('/^\/\/\s?|^\/\*\s?|\*\/$/', '', $comment);
        //     // echo '--' . $commentContent . "\n";

        //     foreach ($patterns as $patternName => $p) {

        //         // Check pour les fonctions
        //         if($patternName == "fonctions") {
        //             $isFunc = preg_match($patterns["fonctions"]["nomfn"], $commentContent, $nomfn) ;

        //             if ($isFunc != 0) {
        //                 array_push($data[$i]["contents"]["fonctions"], [
        //                     "name" => explode(" ", $nomfn[1])[0],
        //                     "brief" => "",
        //                     "parameters" => [],
        //                     "return" => []
        //                 ]);

        //                 // gestion des parmaètres de la fonction/procédure (s'il y en a)

        //                 if (preg_match_all($patterns["fonctions"]["paramfn"], $commentContent, $paramMatches) != 0) {
        //                     foreach($paramMatches[0] as $paramInfo) {
        //                         preg_match($patterns["fonctions"]["paramfn"], $paramInfo, $paramMatch);
    
        //                         array_push($data[$i]["contents"]["fonctions"][$fnCount]["parameters"], [
        //                             "name" => explode(' : ', $paramMatch[1])[0],
        //                             "description" => explode(' : ', $paramMatch[1])[1],
        //                         ]);
        //                     }
        //                 }

        //                 // gestion du return de la fonction (s'il y en a)
        //                 if (preg_match($patterns["fonctions"]["returnfn"], $paramInfo, $returnInfo) != 0) {
        //                     array_push($data[$i]["contents"]["fonctions"][$fnCount]["return"], [
        //                         "name" => explode(' : ', $returnInfo[1])[0],
        //                         "description" => explode(' : ', $returnInfo[1])[1],
        //                     ]);
        //                 }

        //                 $fnCount++;
        //             }
        //         // Check pour les structures
        //         } else if ($patternName == "structures") {
        //             // ajout d'une struture si le commentaire actuel contient la variable $nomstruc
        //             $isStruct = preg_match($patterns["structures"]["nomstruc"], $commentContent, $nomstruc);

        //             // ajout des données de la structure
        //             if ($isStruct != 0) {
        //                 array_push($data[$i]["contents"]["structures"], [
        //                     "name" => explode(" : ", $nomstruc[1])[0],
        //                     "brief" => "",
        //                     "components" => [],
        //                 ]);

        //                 // gestion des arguments (seulement s'il y en a)
        //                 if (preg_match_all($patterns["structures"]["argstruc"], $commentContent, $componantMatches)) {
        //                     foreach ($componantMatches[0] as $componant) {
        //                         preg_match($patterns["structures"]["argstruc"], $componant, $componantMatch);
    
        //                         array_push($data[$i]["contents"]["structures"][$structCount]["components"], [
        //                             "name" => explode(" : ", $componantMatch[1])[0],
        //                             "description" => explode(" : ", $componantMatch[1])[1]
        //                         ]);
        //                     }
        //                 }

        //                 $structCount++;
        //             }
        //         } else if ($patternName == "types") {
        //             $isType = preg_match($patterns["types"], $commentContent, $typeMatch);

        //             if ($isType) {
        //                 $currentIndex = count($data[$i]["contents"]["types"]);
        //                 echo $typeMatch[1];
        //                 $typeData = explode(" ", $typeMatch[1]);
        //                 $type = str_replace("(" || ")", "", $typeData[0]);
        //                 array_push($data[$i]["contents"]["types"], [
        //                     "type" => str_replace(")", "", str_replace("(", "", $typeData[0])),
        //                     "name" => rtrim($typeData[1]),
        //                     "brief" => "",
        //                 ]);

        //                 echo $type;
        //                 if ($type == "struct") {
        //                     echo $type;
        //                 }
        //             }
        //         }
        //         // Check le reste (i.e. n'apparait qu'une seule fois dans le fichier)
        //         else {
        //             // récupère le match du pattern
        //             $isMatching = preg_match($p, $commentContent, $patternMatch);

        //             // sauvegarde un match si il y en a un
        //             if ($isMatching != 0) {
        //                 // [1] car on veut uniquement les données du groupe : (.*)
        //                 $type = gettype($data[$i]["contents"][$patternName]);
        //                 if ($type == 'array') {
        //                     array_push($data[$i]["contents"][$patternName], $patternMatch[1]);
        //                 } else if ($type == 'string') {
        //                     $data[$i]["contents"][$patternName] = rtrim($patternMatch[1]);
        //                 }
        //             }
        //         }
        //     }
        // }

    }

    /*
    echo "Génération des fichiers...\n";

    $htmlContent = file_get_contents("./data/DOC_TECHNIQUE_TEMPLATE.html");

    addToTemplate($htmlContent, "PROJET DANS CONFIG", "PROJECT");
    addToTemplate($htmlContent, "CLIENT DANS CONFIG", "CLIENT");
    addToTemplate($htmlContent, "VERSION DANS CONFIG", "VERSION");
    addToTemplate($htmlContent, date("d/m/Y"), "DATE");
    $filesHTMLContent = "";
    $projectLinksHTMLContent = "";


    // génération des bloc de documentation pour tous les fichiers
    foreach ($files as $fileIndex => $file) {
        echo $file . "\n";
        $fileData = $data[$fileIndex];
        $fileContentsData = $fileData["contents"];

        // Génération des liens de la section FICHIERS
        $projectLink = $HTMLBlocks["fileLink"]["block"];
        addToTemplate($projectLink, $fileData["name"], "NOMFICHIER");
        $projectLinksHTMLContent .= $projectLink;

        $fileHTMLContent = $HTMLBlocks["file"]["block"];

        addToTemplate($htmlContent, $fileContentsData["date"], "PROJECT-DESCRIPTION"); // TODO: récupérer la description du fichier

        addToTemplate($fileHTMLContent, $fileData["name"], "FILENAME");

        foreach ($patterns as $patternName => $_) {

            // véfification du type
            if (gettype($fileContentsData[$patternName]) == 'array') {

                // génération des defines et des variables
                if ($patternName == "defines" || $patternName == "var") {
                    $HTMLContent = "";
                    foreach ($fileContentsData[$patternName] as $i => $value) {
                        $content = $HTMLBlocks["item"]["block"];
                        addToTemplate($content, $value, "NAME");
                        addToTemplate($content, "Ajout de la description ici", "BRIEF");
                        $HTMLContent .= $content;
                    }
                    addToTemplate($fileHTMLContent, $HTMLContent, $patternName);
                }
                // génération des structures
                if ($patternName == "structures") {
                    $HTMLContent = "";
                    echo $patternName;
                    foreach ($fileContentsData[$patternName] as $i => $structData) {
                        $content = $HTMLBlocks["dropdown"]["block"];
                        addToTemplate($content, $structData['name'], "NAME");
                        addToTemplate($content, "Ajout de la description ici", "BRIEF");

                        $subitemHTMLContent = "";
                        foreach ($structData["components"] as $componentData) {
                            $subitemContent = $HTMLBlocks["subitem"]["block"];
                            addToTemplate($subitemContent, $componentData["name"], "NAME");
                            addToTemplate($subitemContent, $componentData["description"], "BRIEF");
                            $subitemHTMLContent .= $subitemContent;
                        }
                        addToTemplate($content, $subitemHTMLContent, "SUBITEM");
                        $HTMLContent .= $content;
                    }
                    addToTemplate($fileHTMLContent, $HTMLContent, $patternName);
                }
            }
        }
        $filesHTMLContent .= $fileHTMLContent;
    }

    addToTemplate($htmlContent, $filesHTMLContent, "FILES-DOCUMENTATION");
    addToTemplate($htmlContent, $projectLinksHTMLContent, "FILES");

    fopen("./test-output/DOC_TECHNIQUE.html", "w");
    file_put_contents("./test-output/DOC_TECHNIQUE.html", $htmlContent);
*/
    fopen("./test-output/tech.json", "w");
    file_put_contents("./test-output/tech.json", json_encode($data, JSON_PRETTY_PRINT));

    function convertToSingular($subject) {
        return str_replace("s", "", $subject);
    }

    function convertToPlural($subject) {
        return $subject . "s";
    }

    function createHTMLBlock($block, $data) {

    }

    function addToTemplate(&$content, $slotValue, $templateSlotName) {
        $content = str_replace("[". strtoupper($templateSlotName) ."]", $slotValue, $content);
    }

    function getTextBetweenParenthesis($text) {
        return str_replace(")", "", str_replace("(", "", $text));
    }

    function matchExist($pattern, $subject) {
        return preg_match($pattern, $subject);
    }

    function getRegexGroup($pattern, $subject) {
        $isMatching = preg_match($pattern, $subject, $match);
        if ($isMatching && count($match) > 1) {
            return rtrim($match[1]);
        } else {
            return $match[0];
        }
    }
?>