/**
 * $file src1.c
 * $auteur Jean Neymar
 * $version 1.0.0
 * $date 23/11/23
 * $brief Code qui demande un nom, un prénom, un âge et une adresse à un utilisateur.
 * Ensuite sont calculées et affichées les caractéristiques de ce dernier:
 * (informations, majorité, qualité du nom d'équipe :))
 * 
 */

#include <stdlib.h>
#include <stdio.h>
#include <string.h>

#define MAJORITE 18 /** $def Age de la majorité.*/
#define TAILLE 20 /** $def Taille max pour le nom et le prenom.*/


/**
 * 
 * $typedef (struct) str_utili
 * $brief Structure d'un utilisateur.
 * 
 * $param (char[]) nom : Nom de l'utilisateur.
 * $param (char[]) prenom : Prénom de l'utilisateur.
 * $param (char[]) adresse : Adresse de l'utilisateur.
 * $param (int) age : Age de l'utilisateur.
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
 * $typedef (struct) str_equipe
 * $brief Nom du nouveau type structure.
 * 
 * $param (char[]) nom : Nom de l'équipe.
 * $param (int) nombreUtili : Nombre d'utilisateur dans l'équipe.
 */
typedef struct
{
    char nom[TAILLE];
    int nombreUtili; 
}str_equipe;

/**
 * $typedef (char[]) t_nom
 * $brief lorem ipsum
 */
typedef char t_nom[TAILLE];


void afficherUtilisateur(str_utili utilisateur1, str_equipe equipe1);
void saisirUtilisateur(str_utili utilisateur1, str_equipe equipe1);

int main()
{
    str_utili s_utilisateur1; /* $var utilisateur de test (n°1)*/
    str_equipe s_equipe1; /* $var equipe dans laquelle va appartenir l'utilisateur*/

    saisirUtilisateur(s_utilisateur1, s_equipe1);
    afficherUtilisateur(s_utilisateur1, s_equipe1);
}

/**
* $fn afficherUtilisateur(str_utili s_utilisateur1, str_equipe s_equipe1) 
* $brief Affiche les caractéristiques de l'utilisateur 1.
* 
* $param (str_utili) s_utilisateur1 : Structure représentant l'utilisateur 1.
* $param (str_equipe) s_equipe1 : Structure représentant l'équipe 1.
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
* $brief Saisie les caractéristiques de l'utilisateur 1.
* $fn saisirUtilisateur(str_utili s_utilisateur1, str_equipe s_equipe1) 
*
* $param (str_utili) s_utilisateur1 : Structure représentant l'utilisateur 1.
* $param (str_equipe) s_equipe1 : Structure représentant l'équipe 1.
* $return (int)
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
