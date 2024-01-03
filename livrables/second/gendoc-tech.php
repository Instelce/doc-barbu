<?php
$config_pattern = [
    "client" => '/CLIENT=+(.*)/',
    "produit" => '/PRODUIT=+(.*)/',
    "version" => '/VERSION=+(.*)/',
];

$dataPatterns = [
    "auteur" => '/\$author\s+(.*)/',
    "version" => '/\$version\s+(.*)/',
    "brief" => '/\$brief\s+(.*)/',
    "define" => [
        "foundLine" => '/\#define\s+(.*)/',
        "name" => '/\#define\s+(\w+)/',
        "value" => '/\#define\s+\w+\s+(\w+)/',
        "brief" => '/\$def\s+(.*)\*/',
    ],
    "global" => [
        "foundLine" => '/.*\$var.*/',  // only to search for the element
        "type" => '/^\s*(\w+)\s+\w+\s*;/',
        "name" => '/^\s*\w+\s+(\w+)\s*;/',
        "brief" => '/\/\*\s*\$var\s+(.*)\s*\*\//',
    ],
    "type" => [
        "foundComment" => '/(?!.*\(struct).*\$typedef\s+\((.*)\)/',
        "type" => '/\$typedef\s+\((.*)\)/',
        "name" => '/\$typedef\s+\(.*\)\s+(\w+)/',
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
// --major                      \
// --minor                       --> Commandes pour incrémenter la version selon l'ordre : (major.minor.build)
// --build                      /


$commands = ["--dir", "--main", "--onefile", "--config"];
$files = [];


// récupération du texte de config
$config_content = file_get_contents("config");

$data = [];
$config_data = [];

// données de config
foreach($config_pattern as $patternName => $p) {
    $config_data[$patternName] = getRegexGroup($p, $config_content);
}

if (count($argv) > 1) {

    // gérer les updates de la version dans le fichier config
    $version = $config_data["version"];

    // Cas Major
    if (in_array("--major", $argv)) {
        $v_m = explode('.', $version)[0];
        $v_m ++;

        $config_data['version'] = "{$v_m}.0.0";
        file_put_contents('config', "CLIENT={$config_data['client']}\nPRODUIT={$config_data['produit']}\nVERSION={$config_data['version']}");
    }
    // Cas Minor
    if (in_array("--build", $argv)) {
        $v_m = explode('.', $version)[0];
        $v_mi = explode('.', $version)[1];
        $v_b = explode('.', $version)[2];
        $v_b ++;

        $config_data['version'] = "{$v_m}.{$v_mi}.{$v_b}";
        file_put_contents('config', "CLIENT={$config_data['client']}\nPRODUIT={$config_data['produit']}\nVERSION={$config_data['version']}");
    }
    // Cas Build
    if (in_array("--minor", $argv)) {
        $v_m = explode('.', $version)[0];
        $v_mi = explode('.', $version)[1];
        $v_mi ++;

        $config_data['version'] = "{$v_m}.{$v_mi}.0";
        file_put_contents('config', "CLIENT={$config_data['client']}\nPRODUIT={$config_data['produit']}\nVERSION={$config_data['version']}");
    }

    // Gérer les thèmes demandés

        // thème blanc/noir (par défaut)
    $css_path = "./themes/1.css";

        // thème playa
    if (in_array("--playa", $argv)) {
        $css_path = "./themes/playa.css";
    }

    // Autres options du programme
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

                array_push($files, $commandValue);

                // récupère les includes
                $includePattern = '/#include\s+"([^"]*)"/m';
                preg_match_all($includePattern, file_get_contents($commandValue), $includeMatches);

                foreach ($includeMatches[0] as $include) {
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
    }
} else {
    exit("Aucune commande trouvée.");
}

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
    }
}

// mettre les données dans un fichier json (au cas où)
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
            echo "Donnée non fournie";
        }
    } else {
        echo $data;
    }
}


?>

<!DOCTYPE html>
<html lang="fr">

<head>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:ital,wght@0,100;0,200;0,300;0,400;0,500;0,600;0,700;0,800;0,900;1,100;1,200;1,300;1,400;1,500;1,600;1,700;1,800;1,900&display=swap" rel="stylesheet">

    <style>
        <?php echo file_get_contents($css_path) ?>
    </style>

    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title><?php echo $config_data["produit"]?> - Documentation</title>
</head>

<body>

    <svg class="feuille-theme-jungle" xmlns="http://www.w3.org/2000/svg" height="16" width="12" viewBox="0 0 384 512">
        <path d="M384 312.7c-55.1 136.7-187.1 54-187.1 54-40.5 81.8-107.4 134.4-184.6 134.7-16.1 0-16.6-24.4 0-24.4 64.4-.3 120.5-42.7 157.2-110.1-41.1 15.9-118.6 27.9-161.6-82.2 109-44.9 159.1 11.2 178.3 45.5 9.9-24.4 17-50.9 21.6-79.7 0 0-139.7 21.9-149.5-98.1 119.1-47.9 152.6 76.7 152.6 76.7 1.6-16.7 3.3-52.6 3.3-53.4 0 0-106.3-73.7-38.1-165.2 124.6 43 61.4 162.4 61.4 162.4 .5 1.6 .5 23.8 0 33.4 0 0 45.2-89 136.4-57.5-4.2 134-141.9 106.4-141.9 106.4-4.4 27.4-11.2 53.4-20 77.5 0 0 83-91.8 172-20z"/>
    </svg>

    <header class="main-header">
        <h1 class="title">
            Projet
            <span><?php echo $config_data["produit"]?></span>
        </h1>

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

                                            <?php if (array_key_exists("return", $itemData) && !is_array($itemData["return"])) { ?>
                                                <p>Return <a href="#<?php echo $itemData["return"] ?>"><?php echo $itemData["return"] ?></a></p>
                                            <?php } ?>
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
            </section>
            <?php } ?>
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

        // Bouton d'activation du thème

            // thème light
        const toggleTheme = document.querySelector(".toggle-theme");
        const root = document.querySelector(":root")
        const rootColors = getComputedStyle(root)
        toggleTheme.addEventListener("click", (e) => {
            e.preventDefault()
            $theme = rootColors.getPropertyValue("--color-theme")
            console.log($theme);
            root.style.setProperty("--color-theme", $theme == "light" ? "dark" : "light");

            root.style.setProperty("--color-background", `var(--color-background-${$theme})`)
            root.style.setProperty("--color-text", `var(--color-text-${$theme})`)
            root.style.setProperty("--color-secondary", `var(--color-secondary-${$theme})`)
            root.style.setProperty("--color-link", `var(--color-link-${$theme})`)
            toggleTheme.innerHTML = $theme == "Light" ? "Dark" : "Light"
        })

        // generate links of navigation bar
        if (document.querySelector(".files")) {
            const filesLink = document.querySelector(".files");
            const navigationLinksList = document.querySelector(".navigation ul");

            navigationLinksList.innerHTML = filesLink.innerHTML;
        }


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