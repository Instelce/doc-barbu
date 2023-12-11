<?php
$fichier = "./DOC_UTILISATEUR.html";
$docsMD = file("../first/DOC_UTILISATEUR.md");

$docsFinal = fopen($fichier, 'w');

$baseHTMLContent = file_get_contents("./data/baseHTML.txt");

if ($docsFinal) 
{
    fwrite($docsFinal, $baseHTMLContent . "\n");

    foreach ($docsMD as $ligne) 
    {   
        $sansRetourLigne = rtrim($ligne);

        titre($docsFinal, $ligne, $sansRetourLigne);

        if(preg_match('/^[0-9]+\./', $ligne))
        {
            listeNum($docsFinal, $ligne);//Fonction à finir jsp comment faire.
        }
        liste($docsFinal, $ligne, $sansRetourLigne);
        texte($docsFinal, $ligne);
    }
} 
else 
{
    echo "Erreur sur l'ouverture du fichier";
}

fclose($docsFinal);


function liste($docsFinal, $ligne, $sansRetourLigne)
{   
    if(preg_match('/^\-\s/', $ligne))
    {
        $pattern = '/\[(.*?)\]\((.*?)\)/';
    
        $titre = "";
        $lien = "";
    
        if (preg_match($pattern, $ligne, $matches)) 
        {
            $titre = $matches[1];
            $lien = $matches[2];
            fwrite($docsFinal, "\t\t" . "<li><a href=\"$lien\">$titre</a></li>" . "\n" . PHP_EOL);
        }
        elseif(preg_match('/\[(.*?)\](.*?)/', $ligne, $matches))
        {
            if(preg_match('/\[x\](.*?)/', $ligne, $matches))
            {
                $contenuLigne = substr($sansRetourLigne, 6);
                fwrite($docsFinal, "\t" . "<div>" . "\n\t\t" . "<input type=\"checkbox\" id=\"$contenuLigne\" name=\"$contenuLigne\" checked/>
        <label for=\"scales\">$contenuLigne</label>" . "\n\t" . "</div>" . "\n" . PHP_EOL);
            }
            else
            {
                $contenuLigne = substr($sansRetourLigne, 6);
                fwrite($docsFinal, "\t" . "<div>" . "\n\t\t" . "<input type=\"checkbox\" id=\"$contenuLigne\" name=\"$contenuLigne\"/>
        <label for=\"scales\">$contenuLigne</label>" . "\n\t" . "</div>" . "\n" . PHP_EOL);
            }

        }
        else
        {
            $contenuLigne = substr($sansRetourLigne, 2);
            fwrite($docsFinal, "\t\t" . "<li>$contenuLigne</li>" . "\n" . PHP_EOL);
        }
    }
}


//Fonction qui remplce tous les titres pas des titres en HTML correspondant à leur "niveau"
function titre($docsFinal, $ligne, $sansRetourLigne)
{
    //Place les titres de niv 1 par un h1
    if (substr($ligne, 0, 1) === "#" && substr($ligne, 1, 1) !== "#") 
    {  
        $contenuLigne = substr($sansRetourLigne, 2);

        fwrite($docsFinal, "\t" . "<h1>$contenuLigne</h1>"  . "\n"  . PHP_EOL);
    } 

    //Place les titres de niv 2 par un h2 
    if(substr($ligne, 0, 2) === "##" && substr($ligne, 2, 1) !== "#")
    {
        $contenuLigne = substr($sansRetourLigne, 3);

        fwrite($docsFinal, "\t" . "<h2>$contenuLigne</h2>" . "\n" . PHP_EOL);
    }

    //Place les titres de niv 3 par un h3
    if(substr($ligne, 0, 3) === "###" && substr($ligne, 3, 1) !== "#")
    {
        $contenuLigne = substr($sansRetourLigne, 4);
    
        fwrite($docsFinal, "\t" . "<h3>$contenuLigne</h3>" . "\n" . PHP_EOL);
    }

    //Place les titres de niv 4 par un h4
    if(substr($ligne, 0, 4) === "####" && substr($ligne, 4, 1) !== "#")
    {
        $contenuLigne = substr($sansRetourLigne, 5);
    
        fwrite($docsFinal, "\t" . "<h4>$contenuLigne</h4>" . "\n" . PHP_EOL);
    }
}

//Permet de tester si cela est un tableau et convertit en HTML si c'est le cas
function listeNum($docsFinal, $ligne)
{
    //Dur à faire on fera ensemble pour les tableaux

    $pattern = '/\[(.*?)\]\((.*?)\)/';
    
    $titre = "";
    $lien = "";

    if (preg_match($pattern, $ligne, $matches)) 
    {
        $titre = $matches[1];
        $lien = $matches[2];
    }

    fwrite($docsFinal, "\t\t" . "<li><a href=\"$lien\">$titre</a></li>" . "\n" . PHP_EOL);
}

function texte($docsFinal, $ligne)
{
    //Test si la ligne commence par des caractères (mjuscule ou minuscule pour détecter si c'est un <p>)
    if (preg_match('/^[A-Za-zàéèÀÉÈ]/', substr($ligne, 0, 4))) 
    {
        /* 
        Stock séparément tous les mots qui ont des mises en pages différents soit en gras, en italique, police monospace ou barrer
        chaque séparation par un pipe correspond a une cellule de mot dans le for each c'est a dire que le permier intervalle
        des mots entre ** et **, sont situés a la cellule 1 de mot donc mot[1] car le pattern des ** est situé en premier dans le 
        preg_match_all et ainsi de suite pour chaque pattern séparer par un pipe. [\p{L}\p{N}\s\'"]+/u cette partie la indique que l'on veut 
        garder les espaces entre les mots sinon il enlève les espaces qui entoure le mot et le stock comme sa dans le tableau mots,
        donc lors de l'écriture affiche tout les mots collé, et ensuite le +/u dit a la fonction que l'on prend tout les caractère 
        du UTF-8 sinon n'affiche pas les caractères accentués. \'" ceci dit juste de garder les ' et " sinon les prends pas en 
        compte et les écris donc pas à la fin.
        */
        preg_match_all('/\*\*(.*?)\*\*|\*(.*?)\*|\~\~(.*?)\~\~|\`(.*?)\`|[\p{L}\p{N}\s\'"]+/u', $ligne, $mots, PREG_SET_ORDER);

        fwrite($docsFinal, "<p>" . "\n");

        foreach ($mots as $mot) 
        {
            /*
            Chaque empty test si la cellule numéro n dans mot est vide ou pas (si il y eu un match du pattern ** ici pour mot[1]) si elle est vide
            cela veut dire que dans le ligne la fonction preg_match_all n'a pas match avec le pattern et donc n'a pas mit dans le cellule 1 de mots.
            */
            if (!empty($mot[1])) 
            {
                fwrite($docsFinal, "<strong>{$mot[1]}</strong>");
            } 
            elseif (!empty($mot[2]))
            {
                fwrite($docsFinal, "<em>{$mot[2]}</em>");
            }
            elseif (!empty($mot[3]))
            {
                fwrite($docsFinal, "<del>{$mot[3]}</del>");
            }
            elseif (!empty($mot[4]))
            {
                fwrite($docsFinal, "<span style=\"font-family: monospace;\">{$mot[4]}</span>");
            }
            else 
            {
                fwrite($docsFinal, $mot[0]);
            }
        }

        fwrite($docsFinal, "</p>" . "\n");
    }
}

?>
