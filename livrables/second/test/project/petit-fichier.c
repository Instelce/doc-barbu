/**
 * $file src1.c
 * $auteur Jean Neymar
 * $version 1.0.0
 * $date 23/11/23
 * $brief lorem ipsum dolor sit amet non pro etiam
 * 
 */

#include <stdlib.h>
#include <stdio.h>
#include <string.h>

#define MAJORITE 18 /** $def Age de la majorité. */
#define TAILLE 20 /** $def Taille max pour le nom et le prenom. */


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
    saisirUtilisateur(s_utilisateur1, s_equipe1);
    afficherUtilisateur(s_utilisateur1, s_equipe1);
}
