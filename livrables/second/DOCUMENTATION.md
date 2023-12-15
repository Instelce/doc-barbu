# Documentation

1. Syntaxe
2. Commandes

## Syntaxe

- Tous les mots clé
- Fichiers
- Defines
- Variables
- Fonctions
- Structure

### Tous les mots clé

```c
/**
 * $file <nom-fichier>
 * $auteur <votre-nom>
 * $brief <description>
 * $version <version-programme>
 * 
 * $def <brief>
 * $var <brief>
 * 
 * $fn <function-prototype>
 * $param (<type>) <name> : <brief>
 * $typedef (<type>) : <name>
 * $return (<type>)
 */
```

### Fichier

```c
/**
 * $file <nom-fichier>
 * $auteur <votre-nom>
 * $brief <description>
 * $version <version-programme>
 * $date <date>
 */
```

### Defines

```c
#define MAX_CHOCOLAT 40 // $def Maximum de chocolat dans le magasin
```

### Variables

```c
int nbChocolat = 10; // $var Nombre de chocolat
```

### Fonctions

```c
/**
 * $fn 
 * $brief <brief>
 * $param (<type-param>) <nom-param> : <description-param>
 * $return (<type>)
 */
```

```c
/**
 * $fn <function-prototype>
 * $brief Enlève n chocolat au total de chocolat
 *
 * $param (int) nb : Nombre de chocolat que l'on va manger
 * $param (int) totalChocolat : Total de chocolat
 *
 */
void mangerChocolat(int nb, int totalChocolat) {
    // ...
}
```

### Structures

```c
/**
 * $typedef (<type>) : <nom>
 * $brief <brief>
 * $param (<type-arg>) <nom-arg> : <description>
 */
```

```c
/**
 * $typedef (struct) : str_utili
 * $brief Structure d'un utilisateur.
 *
 * $param (char[]) nom : Nom de l'utilisateur.
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
