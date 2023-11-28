/**
 * 
 * $auteur Jean Passe
 * 
 * Code qui demande un nom, un prénom, un âge et les notes pour créer un étudiant.
 * Ce super programme retourne également la qualité (jugement purement objectif)
 * du nom de la promo pour un étudiant donné.
 * 
 * $version 1.0.0
 * $date 23/11/23
 * 
 */

#include <stdlib.h>
#include <stdio.h>
#include <string.h>

#define MAJORITE 18 /** $def Age de la majorité.*/
#define TAILLE 30/** $def Taille max pour le nom et le prenom.*/


/**
 *  
 * $nomstruc str_etudiant : Structure d'un étudiant. 
 * 
 * $argstruc nom : Nom de l'étudiant.
 * $argstruc prenom : Prénom de l'étudiant.
 * $argstruc moy : Moyenne de l'étudiant.
 * $argstruc age : Age de l'étudiant.
 * 
 */

typedef struct 
{
    char nom[TAILLE]; 
    char prenom[TAILLE]; 
    float moy; 
    int age; 
}str_etudiant; 


/**
 * 
 * $nomstruc str_classe : Nom du nouveau type structure.
 * 
 * $argstruc nom : Nom de la promo.
 * $argstruc matiere : Matière dans la promo.
 * 
 */

typedef struct 
{
    char nom[TAILLE]; 
    char matiere[TAILLE]; 
}str_promo; 

void afficherEtudiant(str_etudiant s_etudiant, str_promo equipe1);
void saisirEtudiant(str_etudiant s_etudiant, str_promo equipe1);


int main()
{
    str_etudiant s_etudiant; 
    str_promo s_promo; 

    saisirEtudiant(s_etudiant, s_promo);
    afficherEtudiant(s_etudiant, s_promo);
}

/**
* $brief Affiche les caractéristique de l'étudiant.
*
* $return Ici ne retourne rien car cela est une procédure.
*
* $param s_etudiant : Structure représentant l'étudiant.
* $param s_promo : Structure représentant la promo.
*
*/

void afficherEtudiant(str_etudiant s_etudiant, str_promo s_promo) 
{
    printf("\nInformations de l'utilisateur :\n");
    printf("Nom : %s\n", s_etudiant.nom);
    printf("Prénom : %s\n", s_etudiant.prenom);
    if(s_etudiant.age == MAJORITE)
    {
        printf("L'étudiant est majeur !\n");
    }
    else
    {
        printf("Âge : %d", s_etudiant.age);
    }
    printf("Moyenne : %.2f\n", s_etudiant.moy);
    printf("Fait partis de la promo %s\n", s_promo.nom);
}


/**
* $brief Saisie les caractéristique de l'étudiant.
*
* $return Ici ne retourne rien car cela est une procédure.
*
* $param s_etudiant : Structure représentant l'étudiant.
* $param s_promo ; Structure représentant la promo.
*
*/

void saisirEtudiant(str_etudiant s_etudiant, str_promo s_promo) 
{
    printf("Entrez le nom de l'étudiant :\n");
    scanf("%s", s_etudiant.nom);

    printf("Entrez le prénom de l'étudiant :\n");
    scanf("%s", s_etudiant.prenom);

    printf("Entrez la moyenne de l'étudiant :\n");
    scanf("%f", &s_etudiant.moy);

    printf("Entrez l'âge de l'étudiant :\n");
    scanf("%d", &s_etudiant.age);

    printf("Dans quelle promo êtes-vous ?\n");
    scanf("%s", s_promo.nom);

    if(strcmp(s_promo.nom, "Les barbus\0") == 0)
    {
        printf("La meilleur promo de l'IUT bon choix !\n");
    }
    else
    {
        printf("Pas mal mais y'a mieux comme promo...\n");
    }
}
