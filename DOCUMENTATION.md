# Documentation

1. [Instruction d'utilisation](#instruction-dutilisation)
    1. [Dépendances](#dépendances)
    2. [Installation](#installation)
    3. [Utilisation](#utilisation)
2. [Syntaxe](#syntaxe)
    1. [Tous les mots clé](#tous-les-mots-clé)
    2. [Fichier](#fichier)
    3. [Defines](#defines)
    4. [Variables](#variables)
    5. [Fonctions](#fonctions)
    6. [Structures](#structures)
3. [Commandes](#commandes)

## Instruction d'utilisation

### Dépendances

Installer :

- [docker](https://docs.docker.com/)

### Installation

1. Cloner doc-barbu

```bash
git clone https://github.com/Instelce/doc-barbu
```

2. Ajouter le path de doc-bardu dans votre Path.

```bash
export PATH=$PATH:/chemin/vers/doc-barbu
```

### Utilisation

Dans un de vos projet.

1. Documenter vos fichier avec la [syntaxe](#syntaxe) de documentation.

2. Créer un fichier `DOCUMENTATION.md`, et écrivez y votre documentation.

3. Générez un fichier config et remplissez le avec vos information.

```bash
doc-barbu --gen-config
```

4. Générer votre documentation.

```bash
doc-barbu
```

5. D'autre [commandes](#commandes) existe, notamment pour changer la version.

## Syntaxe

### Tous les mots clé

```c
/**
 * $file <nom-fichier>
 * $author <votre-nom>
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
 * $author <votre-nom>
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
 * $fn <function-prototype>
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

**--gen-config** *dirname*

Génère le fichier de configuration.

**--list-themes**

Liste les thèmes.

**--theme** *theme-name*

Applique un certain thème à la documentation technique.

**--major**

Passage à une nouvelle vertion majeure.

**--minor**

Passage à une nouvelle vertion mineur.

**--build**

Passage à une nouvelle vertion de build.
