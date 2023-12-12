
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


#define MAJORITE 18 /** $def Age de la majorité.*/
#define TAILLE 20/** $def Taille max pour le nom et le prenom.*/

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
