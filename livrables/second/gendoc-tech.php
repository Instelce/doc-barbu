<?php
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
        "name" => '/\$fn\s+(.*)/',
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

            // echo getcwd() . "\n";
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

        // echo "Génération de la documentation...\n";
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

        // parcour par matches
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

    <style>
        * {
            box-sizing: border-box;
            transition: color 80ms, background 80ms;
        }

        :root {
            --color-theme: dark;
            --color-secondary: #414853;
            --color-primary: #f66f81;
            --color-background: #1f1f1f;
            --color-text: #fff;
            --color-link: #9499ff;
        }

        body {
            width: 100vw;
            height: auto;
            margin: 0;
            overflow-x: hidden;

            background: var(--color-background);
            font-family: "Poppins", sans-serif;
            color: var(--color-text);
            font-size: 16px;
        }

        a {
            color: var(--color-link);
            text-decoration: transparent;
            transition: opacity .2s ease;
        }

        a:hover {
            opacity: .8;
        }

        p {
            margin: 0;
        }

        button {
            background: none;
            border: 0;
            color: inherit;
            font-size: inherit;
            cursor: pointer;
            padding: 0;
            font-family: "Poppins", sans-serif;
        }

        h1,
        h3,
        h3,
        h4,
        h5,
        h6 {
            margin: 0;
            font-weight: 400;
        }

        .main-title {
            font-size: 2vw;
            margin-bottom: 2rem;
            font-weight: 400;
        }

        .main-title span {
            font-size: 5vw;
            display: block;
            font-weight: 700;
            line-height: 100%;
        }

        header {
            height: 100vh;
            padding: 0 40vh;
            margin-bottom: 4rem;
            position: relative;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-direction: column;
            text-align: center;
        }

        header::after {
            content: "";
            width: 100%;
            height: 1px;
            position: absolute;
            bottom: 0;
            left: 0;
            background: #3d3d3d;
            display: inline-block;
        }

        header span {
            display: block;
            font-size: 1.8rem;
            font-weight: 500;
        }

        header h1 {
            letter-spacing: -1px;
        }

        header h3 {
            font-size: 1.2rem;
            font-weight: 400;
            margin-bottom: 1rem;
        }

        header h3 {
            margin-top: 4rem;
        }

        section h2,
        section h3 {
            width: 100%;
            padding: .5rem 1rem;
            margin-top: 0;
            margin-bottom: 2rem;
            background: var(--color-secondary);
            border-left: var(--color-primary) solid 3px;
            font-weight: 500;
            font-size: 2rem;
        }

        section h2 {
            font-size: 3rem;
            font-weight: 700;
            background: linear-gradient(90deg, var(--color-primary) 0%, var(--color-secondary) 100%);
            color: var(--color-background);
        }

        section {
            padding: 4rem 0;
        }

        .container {
            width: 100vw;
            padding: 0 20vw;
        }

        .dropdown {
            margin-bottom: 1rem;
        }

        .dropdown-trigger {
            width: 100%;
            display: block;
            padding: .6rem 1rem;
            text-align: left;
            font-size: 1.2rem;
            border-left: 2px solid var(--color-secondary);
            transition: background .2s;
            font-weight: 500;

            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        .dropdown-trigger:hover {
            background: var(--color-secondary);
            color: var(--color-link);
        }

        .dropdown-trigger:hover::after {
            opacity: 1;
        }

        .dropdown-trigger::after {
            content: "-";
            font-size: 1.4rem;
            font-weight: 700;
            opacity: 0;
        }

        .dropdown-content {
            width: 100%;
            padding: .6rem 1rem;
            border: 2px solid var(--color-secondary);
            border-top: 0;
        }

        .dropdown .dropdown-trigger {
            border: 2px solid var(--color-secondary);
        }

        .dropdown.hidden .dropdown-trigger::after {
            content: "+";
            font-size: 1.4rem;
            font-weight: 400;
        }

        .dropdown.hidden .dropdown-content {
            display: none;
        }

        .toggle-theme {
            position: fixed;
            bottom: 1rem;
            right: 1rem;
            background: var(--color-secondary);
            padding: 1rem 2rem;
            border-radius: 5px;
        }

        .toggle-theme:hover {
            opacity: .8;
        }

        .item {
            margin-bottom: 1rem;
        }

        .item>p {
            margin-bottom: 1rem;
        }

        .item-title {
            width: 100%;
            padding: .5rem 1rem;
            margin: 0;
            font-weight: 500;
            font-size: 1.2rem;
            /* border-left: var(--color-secondary) solid 2px; */
            border: 2px solid var(--color-secondary);
        }

        .item p {
            padding: .6rem 1rem;
        }

        .sub-item {
            margin: .5rem 0;
            display: flex;
            align-items: center;
        }

        .sub-item-title {
            margin-right: 2rem;
            font-size: 1.1rem;
            font-weight: 500;
        }

        .navigation {
            position: fixed;
            right: 1rem;
            top: 50%;
            transform: translateY(-50%);

            border-radius: 5px;
            border: 2px solid var(--color-secondary);
            overflow: hidden;

            pointer-events: none;
            opacity: 0;
        }

        .navigation ul {
            list-style-type: none;
            margin: 0;
            padding: 0;
        }

        .navigation li {
            border-bottom: 2px solid var(--color-secondary);
        }

        .navigation a {
            text-align: right;
            padding: .5rem 2rem;
            display: block;
            color: var(--color-text);
        }

        .navigation li:last-child {
            border-bottom: 0;
        }

        .navigation a:hover {
            opacity: .8;
            background: var(--color-secondary);
        }

        .navigation a.active {
            background: var(--color-secondary);
            color: var(--color-link);
        }

        .navigation.show {
            opacity: 1;
            pointer-events: auto;
        }
    </style>

    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Documentation Utilisateur</title>
</head>

<body>
    <header>
        <h1 class="main-title">Projet <span>[PROJECT]</span></h1>

        <h3>
            Client
            <span>[CLIENT]</span>
        </h3>

        <h3>
            Version
            <span>[VERSION]</span>
        </h3>

        <h3>
            <?php echo date("m/d/Y") ?>
        </h3>
    </header>

    <main class="container">
        <section id="description">
            <h3>Description</h3>

            <p>
                <?php echo "Super decription du fichier config" ?>
            </p>
        </section>

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
            </section>

            <section id="<?php echo $file["name"] ?>/en-tete">
                <h3>1. En-tête</h3>

                <?php echo $file["auteur"] ?>
                <?php echo $file["version"] ?>

                <p>
                    <?php echo $file["brief"] ?>
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
                                <div class="dropdown">
                                    <button class="item-title dropdown-trigger"><?php echo $itemData["name"] ?></button>
                                    <div class="dropdown-content">
                                        <p>
                                            <?php echo $itemData["brief"] ?>
                                        </p>

                                        <?php
                                        foreach ($itemData["param"] as $paramData) { ?>
                                            <div class="sub-item">
                                                <h4 class="sub-item-title"><?php echo $paramData["name"] ?></h4>
                                                <p><?php echo $paramData["brief"] ?></p>
                                            </div>
                                        <?php } ?>
                                    </div>
                                </div>
                            <?php } else { ?>
                                <div class="item">
                                    <h4 class="item-title"><?php echo $itemData["name"] ?></h4>
                                    <p><?php if (gettype($itemData["brief"]) != "array") {
                                            echo $itemData["brief"];
                                        } else {
                                            echo "pas de brief";
                                        } ?></p>
                                </div>
                            <?php } ?>
                        <?php } ?>
                    </section>

            <?php }
            } ?>
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