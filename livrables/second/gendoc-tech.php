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
        "structures" => [
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
                            <h4 class='sub-item-title'>[NAME]</h4>
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
        "structures" => [
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
        $lastIndex = count(explode(DIRECTORY_SEPARATOR, $path)) - 1;
        array_push($data, [
            "name" => explode(DIRECTORY_SEPARATOR, $path)[$lastIndex],
            "path" => $path,
            "contents" => [
                "auteur" => "",
                "version" => "",
                "date" => "",
                "defines" => [],
                "types" => [],
                "var" => [],
                "structures" => [],
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
                } else if ($patternName == "structures") {
                    // ajout d'une struture si le commentaire actuel contient la variable $nomstruc
                    $isStruct = preg_match($patterns["structures"]["nomstruc"], $commentContent, $nomstruc);

                    // ajout des données de la structure
                    if ($isStruct != 0) {
                        array_push($data[$i]["contents"]["structures"], [
                            "name" => explode(" : ", $nomstruc[1])[0],
                            "components" => [],
                        ]);

                        // gestion des arguments (seulement s'il y en a)
                        if (preg_match_all($patterns["structures"]["argstruc"], $commentContent, $componantMatches)) {
                            foreach ($componantMatches[0] as $componant) {
                                preg_match($patterns["structures"]["argstruc"], $componant, $componantMatch);
    
                                array_push($data[$i]["contents"]["structures"][$structCount]["components"], [
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

    echo "Génération des fichiers...\n";

    $htmlContent = file_get_contents("./data/DOC_TECHNIQUE_TEMPLATE.html");

    addToTemplate($htmlContent, "PROJET DANS CONFIG", "PROJECT");
    addToTemplate($htmlContent, "CLIENT DANS CONFIG", "CLIENT");
    addToTemplate($htmlContent, "VERSION DANS CONFIG", "VERSION");
    addToTemplate($htmlContent, date("d/m/Y"), "DATE");
    $filesHTMLContent = "";
    $projectLinksHTMLContent = "";

    // TODO: Génération des liens de la section FICHIERS

    // génération des bloc de documentation pour tous les fichiers
    foreach ($files as $fileIndex => $file) {
        echo $file . "\n";
        $fileData = $data[$fileIndex];
        $fileContentsData = $fileData["contents"];

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
                        $content = $HTMLBlocks["structures"]["block"];
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
    fopen("./test-output/tech.json", "w");
    file_put_contents("./test-output/tech.json", json_encode($data, JSON_PRETTY_PRINT));
    file_put_contents("./test-output/DOC_TECHNIQUE.html", $htmlContent);


    function addToTemplate(&$content, $slotValue, $templateSlotName) {
        $content = str_replace("[". strtoupper($templateSlotName) ."]", $slotValue, $content);
    }
?>