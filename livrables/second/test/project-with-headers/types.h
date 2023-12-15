#define MAJORITE 18 /** $def Age de la majorité.*/
#define TAILLE 20/** $def Taille max pour le nom et le prenom.*/
#define CONSTANTE 20


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