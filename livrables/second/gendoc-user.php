<?php
$fichier = "./DOC_UTILISATEUR.html";
$docsMD = file("../first/DOC_UTILISATEUR.md");

$docsFinal = fopen($fichier, 'w');
$baseHTMLContent = file_get_contents("./data/baseHTML_gendoc_utilisateur.txt");
$lignePrece = "";

if ($docsFinal) 
{
    fwrite($docsFinal, $baseHTMLContent . "\n"); // Ecris le CSS du html au début du fichier.
    $listeNum = false; //Ce sont des flags pour savoir si on se trouve dans une liste numérique ou non ou dans un tableau.
    $liste = false;
    $tableau = false;

    foreach ($docsMD as $ligneCourante) //Parcourir chaque ligne du fichier markdown
    {   
        $ligneCourante = rtrim($ligneCourante); //Enlever les \n et \t mit tout seul à par le foreach.

        //Ensuite chaque ligne est traitre pour savoir si c'est du simple texte, un titre, etc... en écrivant dans le fichier final les bonnes lignes en HTML.
        if(preg_match('/^#{1,4}\s/', $lignePrece))
        {
            testSiFlagOpen($docsFinal);
            titre($docsFinal, $lignePrece);
        }
        if(preg_match('/^[0-9]+\./', $lignePrece))
        {
            listeNum($docsFinal, $lignePrece);
        }
        if(preg_match('/^\-\s/', $lignePrece))
        {
            liste($docsFinal, $lignePrece);
        }
        if (preg_match('/^[A-Za-zàéèÀÉÈ]/', substr($lignePrece, 0, 4)))
        {
            testSiFlagOpen($docsFinal);
            texte($docsFinal, $lignePrece);
        }
        if(preg_match('/^```/', $lignePrece))
        {
            testSiFlagOpen($docsFinal);
            commande($docsFinal, $ligneCourante);
        }
        if(preg_match('/\|\s*([^|]+)\s*\|\s*([^|]+)\s*\|/', $lignePrece))
        {
            tableau($docsFinal, $lignePrece);
        }

        $lignePrece = $ligneCourante;
    }
    testSiFlagOpen($docsFinal);

    fwrite($docsFinal, "\t" . "</body>" . "\n" . "</html>");
} 
else 
{
    echo "Erreur sur l'ouverture du fichier";
}

fclose($docsFinal);

//Convertit les tableau markdown en tableau HTML.
function tableau($docsFinal, $lignePrece)
{
    global $tableau;
    
    $pattern = '/\|\s*([^|]+)\s*\|\s*([^|]+)\s*\|/';
    
    if ($tableau == false) 
    {
        fwrite($docsFinal, "\n" . "<table>" . "\n");
        $tableau = true;
    }
    
    if (preg_match($pattern, $lignePrece, $matches)) 
    {
        $variable1 = trim($matches[1]);
        $variable2 = trim($matches[2]);
            
        fwrite($docsFinal, "\t<tr>\n");
        fwrite($docsFinal, "\t\t<td>$variable1</td>\n");
        fwrite($docsFinal, "\t\t<td>$variable2</td>\n");
        fwrite($docsFinal, "\t</tr>\n");
    }
}

//Test si on est dans une liste, une liste numériqu et un tableau et si c'est le cas fermer la balise.
function testSiFlagOpen($docsFinal)
{
    global $listeNum;
    global $liste;
    global $tableau;

    if($listeNum == true)
    {
        fwrite($docsFinal, "</ol>" . "\n");
        $listeNum = false;
    }
    if($liste == true)
    {
        fwrite($docsFinal, "</ul>" . "\n");
        $liste = false;
    }
    if($tableau == true)
    {
        fwrite($docsFinal, "</table>" . "\n");
        $tableau = false;
    }
}

//Ecris dans le fichier la ligne si cela est encadrer entre 3 back ticks.
function commande($docsFinal, $ligneCourante)
{
    $pattern = '/<([^>]+)>/';
    $id = "commande";

    if (preg_match($pattern, $ligneCourante, $chevrons)) 
    {
        $textBetweenChevrons = $chevrons[1];
    }
    else
    {
        $textBetweenChevrons = "";
    }
    $textWithoutChevrons = strip_tags($ligneCourante);

    if (!empty($textWithoutChevrons) && !empty($textBetweenChevrons)) {
        fwrite($docsFinal, "\t" . "<p id=\"$id\"><em>$textWithoutChevrons &#60;$textBetweenChevrons&#62;</em></p>" . "\n");
    }
    else if(!empty($textWithoutChevrons) && empty($textBetweenChevrons))
    {
        fwrite($docsFinal, "\t" . "<p id=\"$id\"><em>$textWithoutChevrons</em></p>" . "\n");
    }
}

//Ecris dans le fichier les lignes qui sont des simples liste et prend en compte les lien intra document.
function liste($docsFinal, $lignePrece)
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
function titre($docsFinal, $lignePrece)
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
    
        fwrite($docsFinal, "\t" . "<h4>$contenuLigne</h4>" . "\n" . PHP_EOL);
    }
}

//Ecris dans le fichier la ligne en format liste "numérique".
function listeNum($docsFinal, $lignePrece)
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

//Ecris dans le fichier final chaque ligne de texte et prend en compte si cela est un texte en gras, etc...
function texte($docsFinal, $lignePrece)
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
?>
