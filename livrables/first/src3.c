/**
 * 
 * $auteur Yanick OLA
 * 
 * Code qui demande un nom, un prénom, un âge et les notes d'un étudiant.
 * La moyenne de ses notes sera calculée et ses informations affichées.
 * 
 * 
 * $version 6.6.6
 * $date 23/11/23
 * 
 */

#include <stdlib.h>
#include <stdio.h>

#define NB_ETUDIANT 5 /** $def Nombre maximum d'étudiant dans la structure*/
#define NB_COURS 3 /** $def Nombre maximum de cours dans la structure*/

/**
 * 
 * $nomstruc Etudiant : Nom du nouveau type structure.
 * 
 * $argstruc nom : Nom de l'étudiant.
 * $argstruc notes : Note de l'étudiant dans chaque cours mis dans un tableau.
 * 
 */

struct Etudiant 
{
    char nom[20];
    int notes[NB_COURS];
};


/**
 * 
 * $nomstruc Cours : Nom du nouveau type structure.
 * 
 * $argstruc nom : Nom du cours.
 * 
 */

struct Cours 
{
    char nom[20];
};

void afficherInformations(struct Etudiant etudiants[], struct Cours cours[], int nbEtudiants); 
float calculerMoyenne(struct Etudiant etudiant);

int main() 
{
    struct Cours s_cours[NB_COURS] = {{"Math"}, {"Physique"}, {"Informatique"}}; /*$var s_cours variable qui contient la table des matières*/

    struct Etudiant s_etudiants[NB_ETUDIANT] = 
    {
        {"Alice", {80, 75, 90}},
        {"Bob", {65, 70, 85}},
        {"Charlie", {90, 95, 80}},
        {"David", {75, 80, 70}},
        {"Eva", {85, 90, 95}}
    }; /*$var s_etudiants variable qui contient la liste des étudiants et leurs notes*/

    afficherInformations(s_etudiants, s_cours, NB_ETUDIANT);

    printf("Moyennes des étudiants :\n");
    for (int i = 0; i < NB_ETUDIANT; i++) 
    {
        printf("%s: %.2f\n", s_etudiants[i].nom, calculerMoyenne(s_etudiants[i]));
    }

    return 0;
}


/**
* $brief Affiche les caractéristique d'un étudiant.
* $fn afficherInformations 
*
* $param s_etudiants : Paramètre représentant l'étudiant.
* $param cours : Paramètre représentant la promo.
* $param nbEtudiants : Paramètre représentant le nombre d'étudiants au total. 
*
*/

void afficherInformations(struct Etudiant s_etudiants[], struct Cours cours[], int nbEtudiants) 
{
    printf("Liste des étudiants :\n");
    for (int i = 0; i < nbEtudiants; i++) 
    {
        printf("Etudiant %d - Nom: %s\n", i + 1, s_etudiants[i].nom);
        
        for (int j = 0; j < NB_COURS; j++) 
        {
            printf("\t%s: %d\n", cours[j].nom, s_etudiants[i].notes[j]);
        }
        printf("\n");
    }
}


/**
* $brief Calcul la moyenne d'un étudiant.
* $fn calculerMoyenne
*
* $return Retourne un float (la moyenne).
*
* $param s_etudiants : Paramètre représentant un étudiant.
*
*/

float calculerMoyenne(struct Etudiant s_etudiants) 
{
    float somme = 0;

    for (int i = 0; i < NB_COURS; i++) 
    {
        somme += s_etudiants.notes[i];
    }

    return somme / NB_COURS;
}
