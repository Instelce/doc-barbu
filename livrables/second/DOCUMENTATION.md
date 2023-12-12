# Documentation

1. Syntaxe
2. Commandes

## Syntaxe

- Fichier
- Defines
- Variables
- Fonctions
- Structure

### Fichier

```c
/**
 * $auteur <votre-nom>
 * 
 * $version <version-de-votre-programme>
 * $date <date>
 * 
 * <description>
 */
```

Ajout :

```c
/**
 * $file <nom-fichier>
 * $auteur <votre-nom>
 * 
 * $version <version-programme>
 * $date <date>
 * 
 * $brief <description>
 */
```

### Defines

```c
#define MAX_CHOCOLAT 40 // $def Maximum de chocolat dans le magasin
```

### Variables

```c
<type-var> <nom-var> = <valeur-var>; // $var <type-var> <description-var>
int nbChocolat = 10; // $var int Nombre de chocolat
```

Proposition :

```c
int nbChocolat = 10; // $var Nombre de chocolat
```

### Fonctions

```c
/**
* $fn void mangerChocolat(int nb, int totalChocolat)
* $brief Enlève n chocolat au total de chocolat
*
* > $param (<type-param>) <nom-param> : <description-param>
* $param (int) nb : Nombre de chocolat que l'on va manger
* $param int : Nombre de chocolat que l'on va manger
* $param int : Total de chocolat
*
*/
void mangerChocolat(int nb, int totalChocolat) {
    // ...
}
```

### Structures

```c
/**
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
```

Proposition :

```c
/**
 * > $typedef <type-typedef> : <nom-typedef>
 * $typedef struct : str_utili
 * $brief Structure d'un utilisateur.
 * 
 * > $composant (<type-arg>) <nom-arg> : <description>
 * $composant (char[]) nom : Nom de l'utilisateur.
 * 
 */
typedef struct
{
    char nom[TAILLE];
    char prenom[TAILLE]; 
    char adresse[50]; 
    int age; 
}str_utili; 
```

## Commandes

**--dir** *dirname*

Cherche tous les fichiers présents dans le dossier et génère leur documentation.

**--main** *filename*

Génère la documentation du fichier principal et des fichiers importés

**--onefile** *filename*

Génère la documentation d'un fichier
