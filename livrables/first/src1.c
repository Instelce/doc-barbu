/**
 * 
 * $auteur Jean Neymar
 * 
 * $version 1.0.0
 * $date 23/11/23
 * 
 * Code qui demande un nom, un prénom, un âge et une adresse à un utilisateur.
 * Ensuite sont calculées et affichées les caractéristiques de ce dernier:
 * (informations, majorité, qualité du nom d'équipe :))
 * 
 */

#include <stdlib.h>
#include <stdio.h>
#include <string.h>

#define MAJORITE 18 /** $def Age de la majorité.*/
#define TAILLE 20/** $def Taille max pour le nom et le prenom.*/


/**
 * 
 * $nomstruc str_utili : Structure d'un utilisateur.
 * 
 * $argstruc nom : Nom de l'utilisateur.
 * $argstruc prenom : Prénom de l'utilisateur.
 * $argstruc adresse : Adresse de l'utilisateur.
 * $argstruc age : Age de l'utilisateur.
 * 
 */

typedef struct 
{
    char nom[TAILLE];
    char prenom[TAILLE]; 
    char adresse[50]; 
    int age; 
}str_utili; 


/**
 * 
 * $nomstruc str_equipe : Nom du nouveau type structure.
 * 
 * $argstruc nom : Nom de l'équipe.
 * $argstruc nombreUtili : Nombre d'utilisateur dans l'équipe.
 * 
 */

typedef struct 
{
    char nom[TAILLE];
    int nombreUtili; 
}str_equipe;

void afficherUtilisateur(str_utili utilisateur1, str_equipe equipe1);
void saisirUtilisateur(str_utili utilisateur1, str_equipe equipe1);

int main()
{
    str_utili s_utilisateur1; /*$var s_utilisateur1 utilisateur de test (n°1)*/
    str_equipe s_equipe1; /*$var s_equipe equipe dans laquelle va appartenir l'utilisateur*/

    saisirUtilisateur(s_utilisateur1, s_equipe1);
    afficherUtilisateur(s_utilisateur1, s_equipe1);
}

/**
* $fn afficherUtilisateur
* $brief Affiche les caractéristiques de l'utilisateur 1.
*   
* $param s_utilisateur1 : paramètre représentant l'utilisateur 1.
* $param s_equipe1 : paramètre représentant l'équipe 1.
*
*/

void afficherUtilisateur(str_utili s_utilisateur1, str_equipe s_equipe1) 
{
    printf("\nInformations de l'utilisateur :\n");
    printf("Nom : %s\n", s_utilisateur1.nom);
    printf("Prénom : %s\n", s_utilisateur1.prenom);
    printf("Adresse : %s\n", s_utilisateur1.adresse);
    if(s_utilisateur1.age == MAJORITE)
    {
        printf("L'utilisateur 1 est majeur !\n");
    }
    else
    {
        printf("Âge : %d", s_utilisateur1.age);
    }

    printf("Fait partis de l'équipe %s", s_equipe1.nom);
}


/**
* $fn saisirUtilisateur
* $brief Pour saisir les caractéristiques de l'utilisateur 1.
* 
* $param s_utilisateur1 : Paramètre représentant l'utilisateur 1.
* $param s_equipe1 : Paramètre représentant l'équipe 1.
*
*/

void saisirUtilisateur(str_utili s_utilisateur1, str_equipe s_equipe1) 
{
    printf("Entrez le nom de l'utilisateur :\n");
    scanf("%s", s_utilisateur1.nom);

    printf("Entrez le prénom de l'utilisateur :\n");
    scanf("%s", s_utilisateur1.prenom);

    printf("Entrez l'adresse de l'utilisateur :\n");
    scanf("%s", s_utilisateur1.adresse);

    printf("Entrez l'âge de l'utilisateur :\n");
    scanf("%d", &s_utilisateur1.age);

    printf("Dans quelle équipe êtes-vous ?\n");
    scanf("%s", s_equipe1.nom);


    if(strcmp(s_equipe1.nom, "Les barbus\0") == 0)
    {
        printf("Meileur nom d'équipe !\n");
    }
    else
    {
        printf("Pas mal mais y'a mieux comme nom...\n");
    }
}
