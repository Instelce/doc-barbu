#include "types.h"
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
