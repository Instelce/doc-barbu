/**
 * 
 * $author Jean Neymar
 * $version 1.0.0
 * $date 23/11/23
 * 
 * $brief Code qui demande un nom, un prénom, un âge et une adresse à un utilisateur.
 * 
 */

#include <stdlib.h>
#include <stdio.h>
#include <string.h>
#include "functions.h"
#include "types.h"


int main()
{
    str_utili s_utilisateur1; /* $var s_utilisateur1 utilisateur de test (n°1)*/
    str_equipe s_equipe1; /* $var s_equipe equipe dans laquelle va appartenir l'utilisateur*/

    saisirUtilisateur(s_utilisateur1, s_equipe1);
    afficherUtilisateur(s_utilisateur1, s_equipe1);
}
