<?php
$HTMLBlocksTemplate = [
    "section" => [
        "block" => "<section id='[SECTION-NAME]'>
                        <h3>[SECTION-INDEX]. [SECTION-NAME]</h3>

                        [SECTION-CONTENT]
                    </section>"
    ],
    "file" => [
        "block" => "<section id='[FILENAME]' class='file-section'>
                        <h2>[FILENAME]</h2>

                        <p>[FILE-BRIEF]</p>

                        <h3>Chapitres</h3>

                        <nav>
                            <ol type='I'>
                                [CHAPITRES]
                            </ol>
                        </nav>
                    </section>

                    [FILE-SECTIONS]"
    ],
    "pageLink" => [
        "block" => "<li><a href='#[NAME]'>[NAME]</a></li>"
    ],
    "refLink" => [
        "block" => "<a href='#[NAME]'>[NAME]</a>"
    ],
    "item" =>   [
        "block" =>  "<div class='item'>
                            <h4 class='item-title'>[NAME]</h4>
                            <p>[BRIEF]</p>
                        </div>",
    ],
    "dropdown" => [
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
    "type" => [
        "type" => '/\$typedef\s+\((.*)\)/',
        "name" => '/\$typedef\s+\(\w+\)\s+(\w+)/',
        "brief" => '/\$brief\s+(.*)/',
    ],
    "struct" => [
        "foundComment" => '/\$typedef\s+\(struct\)\s+/',
        "type" => '/\$typedef\s+\((.*)\)/',
        "name" => '/\$typedef\s+\(\w+\)\s+(\w+)/',
        "brief" => '/\$brief\s+(.*)/',
        "param" => [
            "found" => '/\$param\s+(.*)/',
            "type" => '/\((.*?)\)/',
            "name" => '/\)\s+(.*?)\s+\:/',
            "brief" => '/\:\s+(.*?)$/'
        ]
    ],
    "fn" => [
        "foundComment" => '/\$fn/',
        "prototype" => '/\$fn\s+(.*)/',
        "brief" => '/\$brief\s+(.*)/',
        "return" => '/\$return\s*\((.*)\)/',
        "param" => [
            "found" => '/\$param\s+(.*)/',
            "type" => '/\((.*?)\)/',
            "name" => '/\)\s+(.*?)\s+\:/',
            "brief" => '/\:\s+(.*?)$/'
        ]
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
    ],
    "structures" => [
        "struct",
    ],
    "fonctions" => [
        "fn"
    ]
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
        "brief" => "",
        "sections" => [
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
    $data[$i]["brief"] = getRegexGroup($dataPatterns["brief"], $enteteData);

    $structCount = 0;
    $fnCount = 0;

    // récupération des données du fichier
    foreach ($sections as $sectionName => $section) {

        foreach ($section as $sectionFieldName) {
            if (gettype($dataPatterns[$sectionFieldName]) == "array") {
                $inLine = [array_key_exists("foundLine", $dataPatterns[$sectionFieldName]), $sectionFieldName];
                $inComment = [array_key_exists("foundComment", $dataPatterns[$sectionFieldName]), $sectionFieldName];
            } else {
                $inLine = [false, ""];
                $inComment = [false, ""];
            }
        }

        // parcour par matches
        if ($inLine[0]) {
            echo "\nIN LINE --------------------------------\n";
            print_r($inLine);
            print_r($dataPatterns[$inLine[1]]);

            preg_match_all($dataPatterns[$inLine[1]]["foundLine"], $fileContent, $sectionMatches);

            // loop toute les lignes matchant avec la section
            foreach ($sectionMatches[0] as $sectionMatch) {
                foreach ($section as $sectionFieldName) {
                    $sectionLenght = count($data[$i]["sections"][$sectionName]);

                    foreach ($dataPatterns[$inLine[1]] as $patternName => $pattern) {
                        $data[$i]["sections"][$sectionName][$sectionLenght][$patternName] = getRegexGroup($pattern, $sectionMatch);
                    }

                    unset($data[$i]["sections"][$sectionName][$sectionLenght]["foundLine"]);
                }
            }
        } else if ($inComment[0]) {
            echo "\nIN COMMENT --------------------------------\n";
            print_r($inComment);
            print_r($dataPatterns[$inComment[1]]);

            foreach ($commentMatches[0] as $commentMatch) {
                $isInComment = preg_match($dataPatterns[$inComment[1]]["foundComment"], $commentMatch);

                if ($isInComment) {
                    $sectionLenght = count($data[$i]["sections"][$sectionName]);
                    foreach ($dataPatterns[$inComment[1]] as $patternName => $pattern) {
                        if (gettype($pattern) != 'array') {
                            $data[$i]["sections"][$sectionName][$sectionLenght][$patternName] = getRegexGroup($pattern, $commentMatch);
                        } else {
                            $isSub = preg_match_all($pattern["found"], $commentMatch, $subMatches);

                            if ($isSub) {
                                // create the array if it doesn't exist
                                if (!in_array($patternName, $data[$i]["sections"][$sectionName][$sectionLenght])) {
                                    $data[$i]["sections"][$sectionName][$sectionLenght][$patternName] = [];
                                }

                                foreach ($subMatches[1] as $subMatch) {
                                    $sectionSubLenght = count($data[$i]["sections"][$sectionName][$sectionLenght][$patternName]);

                                    foreach ($dataPatterns[$inComment[1]][$patternName] as $subPatternName => $subPattern) {
                                        $data[$i]["sections"][$sectionName][$sectionLenght][$patternName][$sectionSubLenght][$subPatternName] = getRegexGroup($subPattern, $subMatch);
                                    }

                                    unset($data[$i]["sections"][$sectionName][$sectionLenght][$patternName][$sectionSubLenght]["found"]);
                                }
                            }
                        }
                    }
                    unset($data[$i]["sections"][$sectionName][$sectionLenght]["foundComment"]);
                }
            }
        }

        // parcour par block de commentaire
        else {
            foreach ($commentMatches[0] as $commentMatch) {
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


// génération des bloc de documentation pour tous les fichiers
foreach ($files as $fileIndex => $file) {
    echo $file . "\n";
    $fileData = $data[$fileIndex];
    $fileContentsData = $fileData["sections"];

    // Génération des liens de la section FICHIERS
    $projectLink = $HTMLBlocksTemplate["pageLink"]["block"];
    addToTemplate($projectLink, $fileData["name"], "NAME");
    $projectLinksHTMLContent .= $projectLink;

    addToTemplate($htmlContent, "(A recup dans config) Lorem ipsum dolor sit amet consectetur, adipisicing elit. Molestiae sunt numquam facere? Pariatur sapiente ipsam minima atque distinctio eligendi molestias iusto impedit adipisci sint nihil ipsum optio, ea autem velit!", "PROJECT-DESCRIPTION");

    // file generation
    $fileHTMLContent = $HTMLBlocksTemplate["file"]["block"];
    addToTemplate($fileHTMLContent, $fileData["name"], "FILENAME");
    addToTemplate($fileHTMLContent, $fileData["brief"], "FILE-BRIEF");

    $fileSections = "";

    $fillSections = 1;
    foreach ($data[$fileIndex]["sections"] as $sectionName => $section) {
        if (count($section) > 0) {
            $sectionHTMLBlock = $HTMLBlocksTemplate["section"]["block"];
            $sectionContent = "";

            addToTemplate($sectionHTMLBlock, ucfirst($sectionName), "SECTION-NAME");
            addToTemplate($sectionHTMLBlock, $fillSections, "SECTION-INDEX");
            foreach ($section as $blockData) {
                if (array_key_exists("param", $blockData)) {
                } else {
                    $block = createHTMLBlock("item", $blockData);
                    $sectionContent .= $block;
                }
            }
            addToTemplate($sectionHTMLBlock, $sectionContent, "SECTION-CONTENT");

            $fillSections++;
            $fileSections .= $sectionHTMLBlock;
        }
    }

    addToTemplate($fileHTMLContent, $fileSections, "FILE-SECTIONS");

    $filesHTMLContent .= $fileHTMLContent;
}

addToTemplate($htmlContent, $filesHTMLContent, "FILES-DOCUMENTATION");
addToTemplate($htmlContent, $projectLinksHTMLContent, "FILES-LINKS");

fopen("./test-output/DOC_TECHNIQUE.html", "w");
file_put_contents("./test-output/DOC_TECHNIQUE.html", $htmlContent);

fopen("./test-output/tech.json", "w");
file_put_contents("./test-output/tech.json", json_encode($data, JSON_PRETTY_PRINT));

function convertToSingular($subject)
{
    return str_replace("s", "", $subject);
}

function convertToPlural($subject)
{
    return $subject . "s";
}

function createHTMLBlock($blockName, $data)
{
    global $HTMLBlocksTemplate;
    $block = $HTMLBlocksTemplate[$blockName]["block"];
    foreach ($data as $key => $value) {
        if (gettype($value) == "string") {
            addToTemplate($block, $value, $key);
        }
    }
    return $block;
}

function addToTemplate(&$content, $slotValue, $templateSlotName)
{
    $content = str_replace("[" . strtoupper($templateSlotName) . "]", $slotValue, $content);
}

function getTextBetweenParenthesis($text)
{
    return str_replace(")", "", str_replace("(", "", $text));
}

function matchExist($pattern, $subject)
{
    return preg_match($pattern, $subject);
}

function getRegexGroup($pattern, $subject)
{
    $isMatching = preg_match($pattern, $subject, $match);
    if ($isMatching && count($match) > 1) {
        return rtrim($match[1]);
    } else {
        return $match;
    }
}
