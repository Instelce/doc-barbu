<?php
$dataPatterns = [
    "auteur" => '/\$author\s+(.*)/',
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
            "name" => '/\)\s+(.*?)\s+/',
            "brief" => '/\:\s+(.*?)$/'
        ]
    ],
    "fn" => [
        "foundComment" => '/\$fn/',
        "name" => '/\$fn\s+(.*)/',
        "brief" => '/\$brief\s+(.*)/',
        "return" => '/\$return\s*\((.*)\)/',
        "param" => [
            "found" => '/\$param\s+(.*)/',
            "type" => '/\((.*?)\)/',
            "name" => '/\)\s+(.*?)\s+/',
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
// --config                     Génère le fichier de configuration à renseigner
// --dir <dirname>              Cherche tous les fichiers présents dans le dossier et génère leur documentation.
// --main <main_program_file>   Génère la documentation du fichier principal et des fichiers importés
// --onefile <file_name>        Génère la documentation d'un fichier
// --help                       Donne la documentation des commandes


$commands = ["--dir", "--main", "--onefile", "--config"];
$files = [];

if (count($argv) > 1) {
    $command = $argv[1];
    $commandValue = $argv[2];

    if ($command == "--config") {
        file_put_contents("config", "CLIENT=XXX\nPRODUIT=XXX\nVERSION=X.X.X");
    } else if (!in_array($command, $commands)) {
        exit("Cette commande n'existe pas.");
    } else if ($commandValue == '') {
        exit("Veuillez saisir la valeur de la commande.");
    } else {
        if ($command == "--dir") {
            $files = glob(getcwd() . DIRECTORY_SEPARATOR . $commandValue . DIRECTORY_SEPARATOR . "*.c");
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

        // echo "Génération de la documentation...\n";
    }
} else {
    exit("Aucune commande trouvée.");
}

// récupération du texte de config
$config_content = file_get_contents("config");


// $files = ["../first/src1.c", "../first/src2.c", "../first/src3.c"];
$data = [];
$config_data = [];

// données de config
foreach($config_pattern as $patternName => $p) {
    $config_data[$patternName] = getRegexGroup($p, $config_content);
}

print_r($config_data);

// initialisation des données des fichiers
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

// boucle tous les fichiers
foreach ($files as $i => $filePath) {
    // echo "* " . $data[$i]["name"] . "...\n";
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

        // parcours par matches
        if ($inLine[0]) {
            // echo "\nIN LINE --------------------------------\n";
            // print_r($inLine);
            // print_r($dataPatterns[$inLine[1]]);

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
            // echo "\nIN COMMENT --------------------------------\n";
            // print_r($inComment);
            // print_r($dataPatterns[$inComment[1]]);

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

// fopen("./test-output/tech.json", "w");
// file_put_contents("./test-output/tech.json", json_encode($data, JSON_PRETTY_PRINT));

function convertToSingular($subject)
{
    return str_replace("s", "", $subject);
}

function convertToPlural($subject)
{
    return $subject . "s";
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

function checkValue($data)
{
    if (is_array($data)) {
        if (count($data) > 0) {
            echo $data;
        } else {
            echo "Donnée non fournit";
        }
    } else {
        echo $data;
    }
}

foreach ($data as $file) {
    foreach ($file["sections"] as $sectionName => $sectionData) {
        foreach ($sectionData as $itemData) {
            // print_r($itemData);
        }
    }
}

?>
<!DOCTYPE html>
<html lang="fr">

<head>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:ital,wght@0,100;0,200;0,300;0,400;0,500;0,600;0,700;0,800;0,900;1,100;1,200;1,300;1,400;1,500;1,600;1,700;1,800;1,900&display=swap" rel="stylesheet">

    <link rel="stylesheet" href="./theme-1.css">

    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title><?php echo $config_data["produit"]?> - Documentation</title>
</head>

<body>
    <header>
        <h1 class="main-title">Projet <span><?php echo $config_data["produit"]?></span></h1>

        <h3>
            Client
            <span><?php echo $config_data["client"]?></span>
        </h3>

        <h3>
            Version
            <span><?php echo $config_data["version"]?></span>
        </h3>

        <h3>
            <?php echo date("m/d/Y") ?>
        </h3>
    </header>

    <main class="container">
        <section id="description">
            <h3>Description</h3>

            <p class="block">
                <?php echo "Super decription du fichier config" ?>
            </p>
        </section>

        <?php if (count($data) > 1) { ?>
            <section>
                <h3>Fichiers</h3>

                <nav>
                    <ul class='files'>
                        <?php foreach ($data as $file) { ?>
                            <li><a href='#<?php echo $file["name"] ?>'><?php echo $file["name"] ?></a></li>
                        <?php } ?>
                    </ul>
                </nav>
            </section>
        <?php } ?>

        <?php
        $sectionCounter = 1;
        foreach ($data as $file) { ?>
            <section id="<?php echo $file["name"] ?>" class="file-section">
                <h2><?php echo $file["name"] ?></h2>

                <h3>Chapitres</h3>

                <nav>
                    <ol type="1">
                        <li><a href="#<?php echo $file["name"] ?>/en-tete">En-tête</a></li>

                        <?php foreach ($file["sections"] as $sectionName => $sectionData) {
                            if (count($sectionData) > 0) { ?>

                                <li><a href='#<?php echo $file["name"] . "/" . $sectionName ?>'><?php echo ucfirst($sectionName) ?></a></li>

                        <?php }
                        } ?>

                    </ol>
                </nav>

                <section id="<?php checkValue($file["name"]) ?>/en-tete">
                    <h3>1. En-tête</h3>

                    <table>
                        <tr>
                            <th>Auteur</th>
                            <td><?php checkValue($file["auteur"]) ?></td>
                        </tr>
                        <tr>
                            <th>Version</th>
                            <td><?php checkValue($file["version"]) ?></td>
                        </tr>
                    </table>

                    <p class="block">
                        <?php checkValue($file["brief"]) ?>
                    </p>
                </section>

                <?php foreach ($file["sections"] as $sectionName => $sectionData) {
                    if (count($sectionData) > 0) {
                        $sectionCounter++; ?>

                        <section id="<?php echo $file["name"] . "/" . $sectionName ?>">
                            <h3><?php echo $sectionCounter ?>. <?php echo ucfirst($sectionName) ?></h3>

                            <?php
                            foreach ($sectionData as $itemData) {
                                if (array_key_exists("param", $itemData)) { ?>
                                    <div class="dropdown" id='<?php if (array_key_exists("type", $itemData)) {
                                                                    checkValue($itemData["name"]);
                                                                } ?>'>
                                        <button class="item-title dropdown-trigger"><?php checkValue($itemData["name"]) ?></button>
                                        <div class="dropdown-content">
                                            <p>
                                                <?php checkValue($itemData["brief"]) ?>
                                            </p>

                                            <table class="sub-item">
                                                <?php
                                                foreach ($itemData["param"] as $paramData) { ?>
                                                    <tr>
                                                        <td><a href="#<?php checkValue($paramData["type"]) ?>"><?php checkValue($paramData["type"]) ?></a></td>
                                                        <th><?php checkValue($paramData["name"]) ?></th>
                                                        <td><?php checkValue($paramData["brief"]) ?></td>
                                                    </tr>
                                                <?php } ?>
                                            </table>
                                        </div>
                                    </div>
                                <?php } else { ?>
                                    <div class="item">
                                        <h4 class="item-title">
                                            <?php if (array_key_exists("type", $itemData)) { ?>
                                                <a href="#<?php echo checkValue($itemData["type"]); ?>" class="type"><?php checkValue($itemData["type"]); ?></a>
                                            <?php } ?>
                                            <?php echo $itemData["name"] ?>
                                            <?php if (array_key_exists("value", $itemData)) { ?>
                                                <span class="value"><?php checkValue($itemData["value"]); ?></span>
                                            <?php } ?>
                                        </h4>
                                        <p><?php if (gettype($itemData["brief"]) != "array") {
                                                echo $itemData["brief"];
                                            } else {
                                                echo "Pas de brief";
                                            } ?></p>
                                    </div>
                                <?php } ?>
                            <?php } ?>
                        </section>
                <?php }
                } ?>
            <?php } ?>
            </section>
    </main>

    <button class="toggle-theme">
        Light
    </button>

    <nav class="navigation">
        <ul>
        </ul>
    </nav>

    <script>
        const dropdowns = document.querySelectorAll(".dropdown");

        dropdowns.forEach(dropdown => {
            let dropdownContent = dropdown.querySelector(".dropdown-content");
            let dropdownTrigger = dropdown.querySelector(".dropdown-trigger");
            dropdownTrigger.addEventListener("click", (e) => {
                e.preventDefault()
                dropdown.classList.toggle("hidden")
            })
        })

        const toggleTheme = document.querySelector(".toggle-theme");
        const root = document.querySelector(":root")
        const rootColors = getComputedStyle(root)
        toggleTheme.addEventListener("click", (e) => {
            e.preventDefault()
            if (rootColors.getPropertyValue("--color-theme") == "dark") {
                root.style.setProperty("--color-theme", "light")
                root.style.setProperty("--color-background", "#ececec")
                root.style.setProperty("--color-text", "#1f1f1f")
                root.style.setProperty("--color-secondary", "#c4c4c4")
                root.style.setProperty("--color-link", "#664BFF")
                toggleTheme.innerText = "Dark"
            } else {
                root.style.setProperty("--color-theme", "dark")
                root.style.setProperty("--color-background", "#1f1f1f")
                root.style.setProperty("--color-text", "#ececec")
                root.style.setProperty("--color-secondary", "#414853")
                root.style.setProperty("--color-link", "#9499ff")
                toggleTheme.innerText = "Light"
            }
        })

        // generate links of navigation bar
        const filesLink = document.querySelector(".files");
        const navigationLinksList = document.querySelector(".navigation ul");

        navigationLinksList.innerHTML = filesLink.innerHTML;


        // files navigation
        const navigation = document.querySelector(".navigation");
        const navLinks = document.querySelectorAll(".navigation a");
        const sections = document.querySelectorAll("section.file-section")

        window.onscroll = () => {
            if (window.scrollY > window.screen.height) {
                navigation.classList.add("show")
            } else {
                navigation.classList.remove("show")
            }

            sections.forEach(section => {
                let top = window.scrollY
                let offset = section.offsetTop - 150
                let height = section.offsetHeight
                let id = section.getAttribute("id")

                if (top >= offset && top < offset + height) {
                    navLinks.forEach(link => {
                        if (link.getAttribute("href") == "#" + id) {
                            link.classList.add("active")
                        } else {
                            link.classList.remove("active")
                        }
                    })
                }
            })
        }
    </script>
</body>

</html>