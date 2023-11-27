/**
 * 
 * $auteur <nom de l'auteur>
 * $brevedesc Code qui demande un nom, un prénom, un âge et une adresse à un utilisateur.
 * $version v1.0
 * $date 23/11/23
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
 * $argstruc nombreUtili Nombre d'utilisateur dans l'équipe.
 * 
 */

typedef struct 
{
    char nom[TAILLE];
    int nombreUtili; 
}str_equipe; 

void afficherUtilisateur(str_utili utilisateur1, str_equipe equipe1);/** $enteteFonc Entete de la fonction afficheUtilisatauer pour que le main sache qu'elle paramètre elle attend.*/
void saisirUtilisateur(str_utili utilisateur1, str_equipe equipe1);/** $enteteFonc Entete de la fonction saisirUtilisateur pour que le main sache qu'elle paramètre elle attend.*/

int main()
{
    str_utili s_utilisateur1; /** $var s_utilisateur1 : Variable de type str_utili définis précédement. */
    str_equipe s_equipe1; /** $var s_equipe1 : Variable de type str_equipe définis précédement. */

    saisirUtilisateur(s_utilisateur1, s_equipe1);
    afficherUtilisateur(s_utilisateur1, s_equipe1);
}

/**
 * 
* $brevedesc Affiche les caractéristique de l'utilisateur 1.
* $detail Afficher un détail plus précis si nécssesaire.
*
* $return Ici ne retourne rien car cela est une procédure.
*
* $param s_utilisateur1 : Structure représentant l'utilisateur 1.
* $param s_equipe1 : Structure représentant l'équipe 1.
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
* $brevedesc Saisie les caractéristique de l'utilisateur 1.
* $detail Afficher un détail plus précis si nécssesaire.
*
* $return Ici ne retourne rien car cela est une procédure.
*
* $param s_utilisateur1 : Structure représentant l'utilisateur 1.
* $param s_equipe1 : Structure représentant l'équipe 1.
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
