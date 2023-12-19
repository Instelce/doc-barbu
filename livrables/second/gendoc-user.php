<?php
$fichier = "./DOC_UTILISATEUR.html";
$docsMD = file("../first/DOC_UTILISATEUR.md");

$docsFinal = fopen($fichier, 'w');

$baseHTMLContent = file_get_contents("./data/baseHTML_gendoc_utilisateur.txt");

$lignePrece = "";

if ($docsFinal) 
{
    fwrite($docsFinal, $baseHTMLContent . "\n");
    $listeNum = false;
    $liste = false;
    $tableau = false;

    foreach ($docsMD as $ligneCourante) 
    {   
        $ligneCourante = rtrim($ligneCourante);

        titre($docsFinal, $ligneCourante, $lignePrece);
        if(preg_match('/^[0-9]+\./', $lignePrece))
        {
            listeNum($docsFinal, $ligneCourante, $lignePrece);
        }
        if(preg_match('/^\-\s/', $lignePrece))
        {
            liste($docsFinal, $ligneCourante, $lignePrece);
        }
        if (preg_match('/^[A-Za-zàéèÀÉÈ]/', substr($lignePrece, 0, 4))) 
        {
            texte($docsFinal, $ligneCourante, $lignePrece);
        }
        if(preg_match('/^```/', $lignePrece))
        {
            commande($docsFinal, $ligneCourante, $lignePrece);
        }

        $lignePrece = $ligneCourante;
    }

    fwrite($docsFinal, "\t" . "</body>" . "\n" . "</html>");
} 
else 
{
    echo "Erreur sur l'ouverture du fichier";
}

fclose($docsFinal);

function tableau($docsFinal, $ligneCourante, $lignePrece)
{
    global $tableau;

    if($tableau == false)
    {
        fwrite($docsFinal, "\n" . "<table>" . "\n");
        $tableau = true;
    }
    if(preg_match('//', $lignePrece))
    {

    }
}

function commande($docsFinal, $ligneCourante, $lignePrece)
{
    if(!empty($ligneCourante))
    {
        fwrite($docsFinal, "\t" . "<p id=\"commande\"><em>$ligneCourante</em></p>" . "\n");
    }
}


function liste($docsFinal, $ligneCourante, $lignePrece)
{   
    $pattern = '/\[(.*?)\]\((.*?)\)/';
    
    $titreListe = "";
    $lienListe = "";

    global $liste;

    if($liste == false)
    {
        fwrite($docsFinal, "\n" . "<ul>" . "\n");
        $liste = true;
    }
    if (preg_match($pattern, $lignePrece, $matches)) 
    {
        $titreListe = $matches[1];
        $lienListe = $matches[2];
        fwrite($docsFinal, "\t\t" . "<li><a href=\"$lienListe\">$titreListe</a></li>" . "\n" . PHP_EOL);
    }
    elseif(preg_match('/\[(.*?)\](.*?)/', $lignePrece, $matches))
    {
        if(preg_match('/\[x\](.*?)/', $lignePrece, $matches))
        {
            $contenuLigne = substr($lignePrece, 6);
            fwrite($docsFinal, "\t" . "<div>" . "\n\t\t" . "<input type=\"checkbox\" id=\"$contenuLigne\" name=\"$contenuLigne\" checked/>
    <label for=\"scales\">$contenuLigne</label>" . "\n\t" . "</div>" . "\n" . PHP_EOL);
        }
        elseif(preg_match('/\[ \](.*?)/', $lignePrece, $matches))
        {
            $contenuLigne = substr($lignePrece, 6);
            fwrite($docsFinal, "\t" . "<div>" . "\n\t\t" . "<input type=\"checkbox\" id=\"$contenuLigne\" name=\"$contenuLigne\"/>
    <label for=\"scales\">$contenuLigne</label>" . "\n\t" . "</div>" . "\n" . PHP_EOL);
        }

    }
    else
    {
        $contenuLigne = substr($lignePrece, 2);
        fwrite($docsFinal, "\t\t" . "<li>$contenuLigne</li>" . "\n" . PHP_EOL);
    }
}



//Fonction qui remplce tous les titres pas des titres en HTML correspondant à leur "niveau"
function titre($docsFinal, $ligneCourante, $lignePrece)
{
    //Place les titres de niv 1 par un h1
    if (substr($lignePrece, 0, 1) === "#" && substr($lignePrece, 1, 1) !== "#") 
    {  
        $contenuLigne = substr($lignePrece, 2);

        fwrite($docsFinal, "\t" . "<h1>$contenuLigne</h1>"  . "\n"  . PHP_EOL);
    } 

    //Place les titres de niv 2 par un h2 
    if(substr($lignePrece, 0, 2) === "##" && substr($lignePrece, 2, 1) !== "#")
    {
        $contenuLigne = substr($lignePrece, 3);

        fwrite($docsFinal, "\t" . "<h2>$contenuLigne</h2>" . "\n" . PHP_EOL);
    }

    //Place les titres de niv 3 par un h3
    if(substr($lignePrece, 0, 3) === "###" && substr($lignePrece, 3, 1) !== "#")
    {
        $contenuLigne = substr($lignePrece, 4);
    
        fwrite($docsFinal, "\t" . "<h3>$contenuLigne</h3>" . "\n" . PHP_EOL);
    }

    //Place les titres de niv 4 par un h4
    if(substr($lignePrece, 0, 4) === "####" && substr($lignePrece, 4, 1) !== "#")
    {
        $contenuLigne = substr($lignePrece, 5);
    
        fwrite($docsFinal, "\t" . "<h4>$contenuLigne</h4>" . "\n" . PHP_EOL);<ul>
    }
}

//Permet de tester si cela est un tableau et convertit en HTML si c'est le cas
function listeNum($docsFinal, $ligneCourante, $lignePrece)
{
    $pattern = '/\[(.*?)\]\((.*?)\)/';
        
    $titreListeNum = "";
    $lienListeNum = "";

    global $listeNum;
    
    if($listeNum == false)
    {
        fwrite($docsFinal, "\n" . "<ol>" . "\n");
        $listeNum = true;
    }

    if (preg_match($pattern, $lignePrece, $matches)) 
    {
        $titreListeNum = $matches[1];
        $lienListeNum = $matches[2];
    }

    fwrite($docsFinal, "\t\t" . "<li><a href=\"$lienListeNum\">$titreListeNum</a></li>" . "\n" . PHP_EOL);
}


function texte($docsFinal, $ligneCourante, $lignePrece)
{
    /* Toute les match différents sont stockés dans une cellule différent du tableau mots */
    preg_match_all('/\*\*(.*?)\*\*|\*(.*?)\*|\~\~(.*?)\~\~|\`(.*?)\`|<mark>(.*?)<\/mark>|<u>(.*?)<\/u>|[\p{L}\p{N}\s\'".;,?:\/!]+/u', $lignePrece, $mots, PREG_SET_ORDER);

    fwrite($docsFinal, "\t" . "<p>");

    foreach ($mots as $mot) 
    {
        /*
        Chaque empty test si la cellule numéro n dans mot est vide ou pas (si il y eu un match du pattern ** ici pour mot[1]) si elle est vide
        cela veut dire que dans le ligne la fonction preg_match_all n'a pas match avec le pattern et donc n'a pas mit dans le cellule 1 de mots.
        */
        if (!empty($mot[1])) 
        {
            fwrite($docsFinal, "\t" . "<strong>{$mot[1]}</strong>");
        } 
        elseif (!empty($mot[2]))
        {
            fwrite($docsFinal, "\t" . "<em>{$mot[2]}</em>");
        }
        elseif (!empty($mot[3]))
        {
            fwrite($docsFinal, "\t" . "<del>{$mot[3]}</del>");
        }
        elseif (!empty($mot[4]))
        {
            fwrite($docsFinal, "\t" . "<span style=\"font-family: monospace;\">{$mot[4]}</span>");
        }
        elseif (!empty($mot[5]))
        {
            fwrite($docsFinal, "\t" . "<mark>{$mot[5]}</mark>");
        }
        elseif (!empty($mot[6]))
        {
            fwrite($docsFinal, "\t" . "<u>{$mot[6]}</u>");
        }
        else 
        {
            fwrite($docsFinal, $mot[0]);
        }
    }

    fwrite($docsFinal, "</p>" . "\n");
}

function dejaDans()
{
    if($liste == true)
    {
        
    }
    if($listeNum == true)
    {

    }
    if($tableau == true)
    {

    }
}

?>
