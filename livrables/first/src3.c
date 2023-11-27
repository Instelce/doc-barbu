/**
 * 
 * $auteur <nom de l'auteur>
 * $brevedesc Code qui demande un nom, un prénom, un âge et les notes d'un étudiant.
 * $version v1.0
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

void afficherInformations(struct Etudiant etudiants[], struct Cours cours[], int nbEtudiants); /** $enteteFonc afficherInformations : Entete de fonction pour afficher les informations d'un étudiant.*/
float calculerMoyenne(struct Etudiant etudiant);  /** $enteteFonc calculerMoyenne : Entete de fonction pour calculer la moyenne de toutes c'est notes dans différentes matières.*/

int main() 
{
    struct Cours s_cours[NB_COURS] = {{"Math"}, {"Physique"}, {"Informatique"}}; /** $var s_cours : Déclaration et initialisation d'une variable de type Cours.*/

    struct Etudiant s_etudiants[NB_ETUDIANT] =   /** $var s_etudiant : Déclaration et initialisation d'une variable de type Etudiant.*/
    {
        {"Alice", {80, 75, 90}},
        {"Bob", {65, 70, 85}},
        {"Charlie", {90, 95, 80}},
        {"David", {75, 80, 70}},
        {"Eva", {85, 90, 95}}
    };

    afficherInformations(s_etudiants, s_cours, NB_ETUDIANT);

    printf("Moyennes des étudiants :\n");
    for (int i = 0; i < NB_ETUDIANT; i++) 
    {
        printf("%s: %.2f\n", s_etudiants[i].nom, calculerMoyenne(s_etudiants[i]));
    }

    return 0;
}


/**
* $brevedesc Affiche les caractéristique d'un étudiant.
* $detail Afficher un détail plus précis si nécssesaire.
*
* $return Ici ne retourne rien car cela est une procédure.
*
* $param s_etudiants : Structure représentant l'étudiant.
* $param cours : Structure représentant la promo.
* $param nbEtudiants : Nombre d'étudiant total. 
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
* $brevedesc Calcul la moyenne d'un étudiant.
* $detail Afficher un détail plus précis si nécssesaire.
*
* $return Retourne un float (la moyenne).
*
* $param s_etudiants : Structure représentant un étudiant.
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