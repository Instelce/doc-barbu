<?php
$fichiersMD = glob("./userdir/*.md"); // ************ CHEMINS À CHANGER ************
$docsMD = file("./userdir/{$fichiersMD[0]}"); // ************ CHEMINS À CHANGER ************

$lignePrece = "";

if ($docsMD != NULL) 
{
    ?><!DOCTYPE html>
    <html lang="fr">
    <head>
        <meta charset="UTF-8">
        <style><?php  

    echo file_get_contents("./data/baseHTML_gendoc_utilisateur.css");

    ?></style>
    </head>
<body><?php


    $listeNum = false; //Ce sont des flags pour savoir si on se trouve dans une liste numérique ou non ou dans un tableau.
    $liste = false;
    $tableau = false;

    foreach ($docsMD as $ligneCourante) //Parcourir chaque ligne du fichier markdown
    {   
        $ligneCourante = rtrim($ligneCourante); //Enlever les \n et \t mit tout seul à par le foreach.

        //Ensuite chaque ligne est traitre pour savoir si c'est du simple texte, un titre, etc... en écrivant dans le fichier final les bonnes lignes en HTML.
        if(preg_match('/^#{1,4}\s/', $lignePrece))
        {
            testSiFlagOpen();
            titre($lignePrece);
        }
        if(preg_match('/^[0-9]+\./', $lignePrece))
        {
            listeNum($lignePrece);
        }
        if(preg_match('/^\-\s/', $lignePrece))
        {
            liste($lignePrece);
        }
        if (preg_match('/^[A-Za-zàéèÀÉÈ]/', substr($lignePrece, 0, 4)))
        {
            testSiFlagOpen();
            texte($lignePrece);
        }
        if(preg_match('/^```/', $lignePrece))
        {
            testSiFlagOpen();
            commande($ligneCourante);
        }
        if(preg_match('/\|\s*([^|]+)\s*\|\s*([^|]+)\s*\|/', $lignePrece))
        {
            tableau($lignePrece, $ligneCourante);
        }

        $lignePrece = $ligneCourante;
    }
    testSiFlagOpen();

    ?></body><?php 
} 
else 
{
    echo "Erreur sur l'ouverture du fichier";
} 


//Convertit les tableau markdown en tableau HTML.
function tableau($lignePrece, $ligneCourante)
{
    global $tableau;

    $pattern = '/\|\s*([^|]+)\s*\|\s*([^|]+)\s*\|/';
    $pattern_tirets = '/^-+$/';


    if (preg_match($pattern, $lignePrece, $matches)) 
    {
        if ($tableau == false) 
        {
            ?><table> <?php
            $tableau = true;
        }

        if(preg_match($pattern, $ligneCourante, $matches_Courant))
        {
            $matches_Courant1 = trim($matches_Courant[1]);
            $matches_Courant2 = trim($matches_Courant[2]);
        }
        else
        {
            $matches_Courant1 = "";
            $matches_Courant2 = "";
        }

        if (preg_match($pattern, $lignePrece, $matches)) 
        {
            $variable1 = trim($matches[1]);
            $variable2 = trim($matches[2]);
        }

        if (preg_match($pattern_tirets, $matches_Courant1, $matches2) && preg_match($pattern_tirets, $matches_Courant2, $matches2)) 
        {    
            ?> 
    <tr>
        <th><?php echo $variable1 ?></th>
        <th><?php echo $variable2 ?></th>
    </tr> 
            <?php
        } 
        elseif (preg_match($pattern_tirets, $variable1, $matches2) && preg_match($pattern_tirets, $variable2, $matches2))
        {
        ?><tr>
            <th><?php echo $variable1 ?></th>
            <th><?php echo $variable2 ?></th>
        </tr><?php 
        }
        else 
        {
        ?><tr>
            <td><?php echo $variable1 ?></td>
            <td><?php echo $variable2 ?></td>
        </tr><?php 
        }
    }
}


//Test si on est dans une liste, une liste numériqu et un tableau et si c'est le cas fermer la balise.
function testSiFlagOpen()
{
    global $listeNum;
    global $liste;
    global $tableau;

    if($listeNum == true)
    {
        ?></ol><?php
        $listeNum = false;
    }
    if($liste == true)
    {
        ?></ul><?php
        $liste = false;
    }
    if($tableau == true)
    {
        ?></table><?php
        $tableau = false;
    }
}

//Ecris dans le fichier la ligne si cela est encadrer entre 3 back ticks.
function commande($ligneCourante)
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

    if (!empty($textWithoutChevrons) && !empty($textBetweenChevrons)) 
    {
        ?><p id="<?php echo $id?>"><em> <?php echo $textWithoutChevrons?> &#60; <?php echo $textBetweenChevrons?> &#62;</em></p><?php
    }
    else if(!empty($textWithoutChevrons) && empty($textBetweenChevrons))
    {
        ?><p id="<?php echo $id?>"><em><?php echo $textWithoutChevrons ?></em></p><?php
    }
}

//Ecris dans le fichier les lignes qui sont des simples liste et prend en compte les lien intra document.
function liste($lignePrece)
{   
    $pattern = '/\[(.*?)\]\((.*?)\)/';
    
    $titreListe = "";
    $lienListe = "";

    global $liste;

    if($liste == false)
    {
        ?><ul><?php 
        $liste = true;
    }
    if (preg_match($pattern, $lignePrece, $matches)) 
    {
        $titreListe = $matches[1];
        $lienListe = $matches[2];
        ?><li><a href="<?php echo $lienListe?> "> <?php echo $titreListe?></a></li><?php
    }
    elseif(preg_match('/\[(.*?)\](.*?)/', $lignePrece, $matches))
    {
        if(preg_match('/\[x\](.*?)/', $lignePrece, $matches))
        {
            $contenuLigne = substr($lignePrece, 6);
    ?>
        <input type="checkbox" id=" <?php echo $contenuLigne?>" name="<?php echo $contenuLigne?>" checked/>
        <label for="scales"><?php echo $contenuLigne?></label> 
    <?php
        }
        elseif(preg_match('/\[ \](.*?)/', $lignePrece, $matches))
        {
            $contenuLigne = substr($lignePrece, 6);
            ?>
            <input type="checkbox" id=" <?php echo $contenuLigne?>" name="<?php echo $contenuLigne?>"/>
            <label for="scales"><?php echo $contenuLigne?></label> 
            <?php
        }

    }
    else
    {
        $contenuLigne = substr($lignePrece, 2);
        ?><li><?php echo $contenuLigne?></li><?php 
    }
}


//Fonction qui remplce tous les titres pas des titres en HTML correspondant à leur "niveau"
function titre($lignePrece)
{
    //Place les titres de niv 1 par un h1
    if (substr($lignePrece, 0, 1) === "#" && substr($lignePrece, 1, 1) !== "#") 
    {  
        $contenuLigne = substr($lignePrece, 2);
        ?> 
            <h1><?php echo $contenuLigne?></h1>
        <?php
    } 

    //Place les titres de niv 2 par un h2 
    if(substr($lignePrece, 0, 2) === "##" && substr($lignePrece, 2, 1) !== "#")
    {
        $contenuLigne = substr($lignePrece, 3);
        ?> 
            <h2> <?php echo $contenuLigne ?></h2>
        <?php
    }

    //Place les titres de niv 3 par un h3
    if(substr($lignePrece, 0, 3) === "###" && substr($lignePrece, 3, 1) !== "#")
    {
        $contenuLigne = substr($lignePrece, 4);
        ?> 
            <h3> <?php echo $contenuLigne ?></h3>
        <?php
    }

    //Place les titres de niv 4 par un h4
    if(substr($lignePrece, 0, 4) === "####" && substr($lignePrece, 4, 1) !== "#")
    {
        $contenuLigne = substr($lignePrece, 5);
        ?> 
            <h4> <?php echo $contenuLigne ?></h4>
        <?php
    }
}

//Ecris dans le fichier la ligne en format liste "numérique".
function listeNum($lignePrece)
{
    $pattern = '/\[(.*?)\]\((.*?)\)/';
        
    $titreListeNum = "";
    $lienListeNum = "";

    global $listeNum;
    
    if($listeNum == false)
    {
        ?><ol><?php
        $listeNum = true;
    }

    if (preg_match($pattern, $lignePrece, $matches)) 
    {
        $titreListeNum = $matches[1];
        $lienListeNum = $matches[2];
    }

    ?><li><a href="<?php echo $lienListeNum?>"><?php echo $titreListeNum?></a></li><?php
}

//Ecris dans le fichier final chaque ligne de texte et prend en compte si cela est un texte en gras, etc...
function texte($lignePrece)
{
    /* Toute les match différents sont stockés dans une cellule différent du tableau mots */
    preg_match_all('/\*\*(.*?)\*\*|\*(.*?)\*|\~\~(.*?)\~\~|\`(.*?)\`|<mark>(.*?)<\/mark>|<u>(.*?)<\/u>|[\p{L}\p{N}\s\'".;,?:\/!]+/u', $lignePrece, $mots, PREG_SET_ORDER);

    ?><p><?php

    foreach ($mots as $mot) 
    {
        /*
        Chaque empty test si la cellule numéro n dans mot est vide ou pas (si il y eu un match du pattern ** ici pour mot[1]) si elle est vide
        cela veut dire que dans le ligne la fonction preg_match_all n'a pas match avec le pattern et donc n'a pas mit dans le cellule 1 de mots.
        */
        if (!empty($mot[1])) 
        {
            ?><strong> <?php echo $mot[1]?> </strong><?php
        } 
        elseif (!empty($mot[2]))
        {
            ?><em> <?php echo $mot[2]?> </em><?php
        }
        elseif (!empty($mot[3]))
        {
            ?><del> <?php echo $mot[3]?> </del><?php
        }
        elseif (!empty($mot[4]))
        {
            ?><span style="font-family: monospace;"> <?php echo $mot[4]?> </span><?php
        }
        elseif (!empty($mot[5]))
        {
            ?><mark><?php echo $mot[5]?></mark><?php
        }
        elseif (!empty($mot[6]))
        {
            ?><u><?php echo $mot[6]?></u><?php
        }
        else 
        {
            ?><?php echo $mot[0] ?><?php
        }
    }
    ?></p><?php
}
?>

