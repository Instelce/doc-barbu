/**
 * $file sudoku.c
 * $author Célestin Besson
 * $brief Fichier du jeu sodoku
 * $version 1
 * $date 2023-10-25 to...
 *
 * $copyright Copyright (c) 2023
 *
 */

#include <stdio.h>
#include <stdlib.h>
#include <string.h>
#include <stdbool.h>
#include <stdarg.h>

// -------------------------------------------------------------------------
// Constantes
// -------------------------------------------------------------------------

#define TAILLE 9
#define N 3
#define CASE_VIDE 0

#define MAX_MSG_SIZE 500
#define MAX_SAISIE_SIZE 500

#define GRILLE_COIN_CHAR '+'
#define GRILLE_HORIZONTAL_SEP_CHAR '-'
#define GRILLE_VERTICAL_SEP_CHAR '|'
#define GRILLE_CASE_VIDE_CHAR '.'

// Code couleurs
#define GREEN_FG 32
#define RED_FG 31

// -------------------------------------------------------------------------
// Type
// -------------------------------------------------------------------------

/**
 * $typedef int t_grille[TAILLE][TAILLE]
 * $brief type matrice de taille TAILLE * TAILLE
 * 
 */
typedef int t_grille[TAILLE][TAILLE];

// -------------------------------------------------------------------------
// Déclarations des fonctions
//-------------------------------------------------------------------------

void chargerGrille(t_grille grille);
void afficherGrille(t_grille grille);
void afficheLigneTiret();
bool grilleRempli(t_grille grille);

bool possibleLigne(t_grille grille, int ligneIndice, int valeur);
bool possibleColonne(t_grille grille, int colonneIndice, int valeur);
bool possibleBloc(t_grille grille, int debutBlocLigne, int debutBlocColonne, int valeur);
bool possible(t_grille grille, int numLigne, int numColonne, int valeur);

void saisir(int *var);
bool grilleRempli(t_grille t_grille);
void printfCouleur(int couleurCaractere, int couleurFond, const char *format, ...);
void printErreur(char message[]);
void printSucces(char message[]);

int nbElementsLigne(t_grille g, int lig);
int nbElementsColonne(t_grille g, int col);


// -------------------------------------------------------------------------
// Programme principal
// -------------------------------------------------------------------------

/**
 * $brief Programme principal du sudoku
 * 
 * $return int 
 */
int main()
{
    t_grille grilleSudoku;
    int numLigne, numColonne, valeur;
    bool est_possible;

    chargerGrille(grilleSudoku);

    // boucle du jeu, ne s'arrête que quand la grille est remplie
    while (!grilleRempli(grilleSudoku))
    {
        afficherGrille(grilleSudoku);

        // saisie de la ligne
        printf("Saisir le numéro de la ligne");
        saisir(&numLigne);
        numLigne--; // convertir la valeur pour quelle soit correcte pour la matrice

        // saisie de la colonne
        printf("Saisir le numéro de la colonne");
        saisir(&numColonne);
        numColonne--; // convertir la valeur pour quelle soit correcte pour la matrice

        // si la case est vide demmander la saisie de la valeur
        if (grilleSudoku[numLigne][numColonne] == CASE_VIDE)
        {
            printSucces("Ok la case est vide.");

            printf("Saisir la valeur de la case %d:%d", numLigne + 1, numColonne + 1);
            saisir(&valeur);

            // vérification que la valeur peut être mise dans la grille
            est_possible = possible(grilleSudoku, numLigne, numColonne, valeur);
            if (est_possible)
            {
                grilleSudoku[numLigne][numColonne] = valeur;
            }
        }
        else
        {
            printErreur("La case n'est pas vide.");
        }
    }

    printSucces("Bravo, tu es fini la grille");

    return 0;
}

// -------------------------------------------------------------------------
// Fonctions lier à la GRILLE
// -------------------------------------------------------------------------

/**
 * $fn void chargerGrille(t_grille grille)
 * $brief Charge les valeurs d'un grille a partir d'un nom de fichier
 *
 * $param (t_grille) grille : grille a charger
 */
void chargerGrille(t_grille grille)
{
    char nomFichier[30];
    char cheminFichier[100];
    FILE *f;

    printf("Nom du fichier ? ");
    scanf("%s", nomFichier);

    sprintf(cheminFichier, "./grilles/%s", nomFichier);

    f = fopen(cheminFichier, "rb");

    if (f == NULL)
    {
        printf("\n ERREUR sur le fichier %s\n", nomFichier);
    }
    else
    {
        fread(grille, sizeof(int), TAILLE * TAILLE, f);
    }
    fclose(f);
}

/**
 * $fn void afficherGrille(t_grille grille)
 * $brief Affichage personalisé de la grille
 *
 * $param (t_grille) grille : grille a afficher
 */
void afficherGrille(t_grille grille)
{
    int caseValeur;

    for (int ligne = 0; ligne < TAILLE; ligne++)
    {
        // affiche les indices des collonnes
        if (ligne == 0)
        {
            printf("   ");
            for (int colonne = 0; colonne < TAILLE; colonne++)
            {
                printf(" %d ", colonne + 1);
                if (colonne % N == N - 1)
                {
                    printf(" ");
                }
            }
            printf("\n");
            afficheLigneTiret();
        }

        for (int colonne = 0; colonne < TAILLE; colonne++)
        {

            // affiche l'indice de la ligne
            if (colonne == 0)
            {
                printf("%d %c", ligne + 1, GRILLE_VERTICAL_SEP_CHAR);
            }

            // affiche la valeur
            caseValeur = grille[ligne][colonne];
            if (ligne != TAILLE + 1)
            {
                if (caseValeur == CASE_VIDE)
                {
                    printf(" %c ", GRILLE_CASE_VIDE_CHAR);
                }
                else
                {
                    printf(" %d ", caseValeur);
                }
            }

            if (colonne % N == N - 1)
            {
                printf("%c", GRILLE_VERTICAL_SEP_CHAR);
            }

        }
        printf(" (%d)", nbElementsLigne(grille, ligne));

        printf("\n");
        if (ligne % N == N - 1 || ligne == TAILLE - 1)
        {
            afficheLigneTiret(ligne);
        }
    }
    printf("   ");
    for (int col = 0; col < TAILLE; col++) {
        printf("(%d)", nbElementsColonne(grille, col));
        if (col % N == N - 1)
        {
            printf(" ");
        }
    }
    printf("\n");
}

/**
 * $fn void afficheLigneTiret()
 * $brief Affiche une ligne de tiret, est utilisé dans la fonction afficheGrille.
 *
 */
void afficheLigneTiret()
{
    printf("  %c", GRILLE_COIN_CHAR);
    for (int colonne = 0; colonne < N; colonne++)
    {
        for (int col = 0; col < 3 * N; col++)
        {
            printf("%c", GRILLE_HORIZONTAL_SEP_CHAR);
        }
        printf("%c", GRILLE_COIN_CHAR);
    }
    printf("\n");
}

/**
 * $fn bool grilleRempli(t_grille grille)
 * $brief Retourne un booleen selon si la grille est rempli ou non
 *
 * $param (t_grile) grille
 * $return true
 * $return false
 */
bool grilleRempli(t_grille grille)
{
    for (int lig = 0; lig < 9; lig++)
    {
        for (int col = 0; col < 9; col++)
        {
            // si la case est vide alors la grille n'est pas remplie
            if (grille[lig][col] == CASE_VIDE)
            {
                return false;
            }
        }
    }
    return true;
}

/**
 * $fn bool possibleLigne(t_grille grille, int ligneIndice, int valeur)
 * $brief Retourne un booleen selon si la valeur est présente dans la ligne de la grille
 *
 * $param (t_grille) grille
 * $param (int) ligneIndice
 * $param (int) valeur
 * $return int
 */
bool possibleLigne(t_grille grille, int ligneIndice, int valeur)
{
    for (int col = 0; col < TAILLE; col++)
    {
        if (grille[ligneIndice][col] == valeur)
        {
            return false;
        }
    }

    return true;
}

/**
 * $brief Compte le nombre de case rempli présent dans une ligne
 * 
 * $param (t_grille) g 
 * $param (int) lig 
 * $return int 
 */
int nbElementsLigne(t_grille g, int lig) {
    int count;

    count = 0;
    for (int col = 0; col < TAILLE; col++)
    {
        if (g[lig][col]!= CASE_VIDE)
        {
            count++;
        }
    }

    return count;
}

/**
 * $fn bool possibleColonne(t_grille grille, int colonneIndice, int valeur)
 * $brief Retourne un booleen selon si la valeur est présente dans la colonne de la grille
 *
 * $param (t_grille) grille
 * $param (int) colonneIndice
 * $param (int) valeur
 * $return int
 */
bool possibleColonne(t_grille grille, int colonneIndice, int valeur)
{
    for (int lig = 0; lig < TAILLE; lig++)
    {
        if (grille[lig][colonneIndice] == valeur)
        {
            return false;
        }
    }

    return true;
}


/**
 * $brief Compte le nombre de case rempli présent dans une colonne
 * 
 * $param g 
 * $param col 
 * $return int 
 */
int nbElementsColonne(t_grille g, int col) {
    int count;

    count = 0;
    for (int lig = 0; lig < TAILLE; lig++)
    {
        if (g[lig][col]!= CASE_VIDE)
        {
            count++;
        }
    }

    return count;
}

/**
 * $fn bool possibleBloc(t_grille grille, int debutBlocLigne, int debutBlocColonne, int valeur)
 * $brief Retourne un booleen selon si la valeur est présente dans la colonne de la grille
 *
 * $param grille
 * $param debutBlocLigne
 * $param debutBlocColonne
 * $param valeur
 * $return int
 */
bool possibleBloc(t_grille grille, int debutBlocLigne, int debutBlocColonne, int valeur)
{
    for (int i = 0; i < N; i++)
    {
        for (int j = 0; j < N; j++)
        {
            // coordonnées dans la grille pour chaque élément du bloc
            int ligne = debutBlocLigne + i;
            int colonne = debutBlocColonne + j;

            // vérifie si la valeur est présente dans le bloc
            if (grille[ligne][colonne] == valeur)
            {
                return false;
            }
        }
    }
    return true;
}

/**
 * $fn bool possible(t_grille grille, int numLigne, int numColonne, int valeur)
 * $brief Retourne vrai si la valeur au coordonné passé en paramètre n'existe pas dans la ligne, la colonne ou le block, et faux si c'est l'inverse.
 *
 * $param grille
 * $param numLigne
 * $param numColonne
 * $param valeur
 * $return true
 * $return false
 */
bool possible(t_grille grille, int numLigne, int numColonne, int valeur)
{
    bool resultat;
    char erreur[MAX_MSG_SIZE];

    int estPossibleLigne = possibleLigne(grille, numLigne, valeur);
    int estPossibleColonne = possibleColonne(grille, numColonne, valeur);
    int estPossibleBlock = possibleBloc(grille, (numLigne / N) * N, (numColonne / N) * N, valeur);

    if (estPossibleLigne && estPossibleColonne && estPossibleBlock)
    {
        printSucces("Ok pas de problème.");
        resultat = true;
    }
    else
    {
        // Affichage des erreurs
        resultat = false;
        if (!estPossibleLigne)
        {
            sprintf(erreur, "\nImpossible de mettre la valeur %d dans la ligne %d.\n", valeur, numLigne + 1);
        }
        if (!estPossibleColonne)
        {
            char tempErreur[MAX_MSG_SIZE];
            sprintf(tempErreur, "Impossible de mettre la valeur %d dans la colonne %d.\n", valeur, numColonne + 1);
            strcat(erreur, tempErreur);
        }
        if (!estPossibleBlock)
        {
            char tempErreur[MAX_MSG_SIZE];
            sprintf(tempErreur, "Impossible de mettre la valeur %d dans ce block.\n", valeur);
            strcat(erreur, tempErreur);
        }
        printErreur(erreur);
    }

    return resultat;
}

// -------------------------------------------------------------------------
// Fonctions lier au TERMINAL
// -------------------------------------------------------------------------

/**
 * $fn void saisir(int *var)
 * $brief Permet de saisir et de vérifier une valeur
 *
 * $param var variable affecté
 */
void saisir(int *var)
{
    char saisieCh[MAX_SAISIE_SIZE];

    printf("\n> ");
    scanf("%s", saisieCh);

    // vérification du type de la valeur
    while (sscanf(saisieCh, "%d", var) == 0)
    {
        printErreur("Veuillez saisir un nombre entier et non pas autre chose.");
        printf("> ");
        scanf("%s", saisieCh);
    }

    // vérification de la valeur
    while (*var < 1 || *var > N * N)
    {
        printErreur("Saisir un nombre entre 1 et 9.");
        printf("\n> ");
        scanf("%s", saisieCh);

        while (sscanf(saisieCh, "%d", var) == 0)
        {
            printErreur("Veuillez saisir un nombre entier et non pas autre chose.");
            printf("> ");
            scanf("%s", saisieCh);
        }
    }
}

/**
 * $fn void printfCouleur(int couleurCaractere, int couleurFond, const char *format, ...)
 * $brief Impression personnalisée de texte avec des couleurs
 *
 * $param couleurCaractere code couleur
 * $param couleurFond code couleur
 * $param format
 * $param ...
 */
void printfCouleur(int couleurCaractere, int couleurFond, const char *format, ...)
{
    char message[MAX_MSG_SIZE] = "";

    va_list args;

    va_start(args, format);
    vsnprintf(message, MAX_MSG_SIZE, format, args);
    va_end(args);

    if (couleurFond == -1)
    {
        printf("\033[%dm%s\033[0m", couleurCaractere, message);
    }
    else if (couleurCaractere == -1)
    {
        printf("\033[%dm%s\033[0m", couleurFond, message);
    }
    else
    {
        printf("\033[%d,%dm%s\033[0m", couleurCaractere, couleurFond, message);
    }
}

/**
 * $fn void printErreur(char message[])
 * $brief Affiche une erreur
 *
 * $param message
 */
void printErreur(char message[])
{
    printfCouleur(RED_FG, -1, "\n%s\n\n", message);
}

/**
 * $fn void printSucces(char message[])
 * $brief Affiche un succes
 *
 * $param message
 */
void printSucces(char message[])
{
    printfCouleur(GREEN_FG, -1, "\n%s\n\n", message);
}
