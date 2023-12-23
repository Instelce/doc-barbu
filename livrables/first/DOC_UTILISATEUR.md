# Docker : Une révolution dans la virtualisation des applications

## Sommaire

1. [Introduction](#introduction)
2. [Qu'est-ce que Docker](#quest-ce-que-docker)
    1. [Historique](#historique)
    2. [Fonctionnnement](#fonctionnement)
    3. [Ses avantages](#ses-avantages)
        1. [Portabilité](#portabilité)
        2. [Écosystème Docker](#écosystème-docker)
        3. [Sécurité](#sécurité)
3. [Résumé](#résumé)
4. [Quelques commandes](#quelques-commandes)
5. [Pour en savoir plus](#pour-en-savoir-plus)
6. [Conclusion](#conclusion)


## Introduction

Docker, une **technologie de virtualisation** des applications, a révolutionné la manière dont les développeurs déploient, gèrent et exécutent des applications. Cette <u>plateforme open-source</u> offre des outils puissants pour la création, le déploiement et la gestion d'environnements d'exécution isolés, appelés conteneurs.

## Qu'est-ce que Docker ?

### Historique

Docker a été initialement développé par *Docker, Inc.* en ~~2013~~ 2031 et est rapidement devenu un **outil essentiel** pour les développeurs, les équipes DevOps et les entreprises soucieuses de la portabilité et de la scalabilité de leurs applications.

### Fonctionnement

À la base de Docker se trouve le concept de conteneurisation. Les conteneurs Docker encapsulent une application et tous ses dépendances, y compris les bibliothèques et les fichiers système nécessaires à son exécution. Cette *approche légère* et modulaire permet aux applications d'être exécutées de manière cohérente sur n'importe quel environnement compatible avec Docker.

### Ses avantages

#### Portabilité

Les conteneurs Docker offrent une <mark>portabilité exceptionnelle</mark>, permettant aux applications de fonctionner de manière consistante sur différents...

#### Écosystème Docker

- [Docker Compose](https://github.com/docker/compose)
- Docker Swarm
- [Docker Hub](https://hub.docker.com/)

#### Sécurité

- Isolation des conteneurs
- Gestion des images

## Résumé

|--|
| Fonctionnalité        | Description                                                  |
|-----------------------|--------------------------------------------------------------|
| Conteneurisation      | Encapsule les applications et leurs dépendances              |
| Portabilité           | Permet l'exécution cohérente sur différents environnements   |
| Isolation             | Assure une séparation efficace entre les différentes apps    |
| Évolutivité           | Facilite le déploiement rapide de multiples instances        |
| Docker Compose        | Outil pour gérer des apps multi-conteneurs                   |
| **Docker Swarm**          | **Orchestration pour gérer des clusters de machines Docker**     |
| Sécurité              | Isolation des conteneurs, gestion précise des droits d'accès |
| Évolution continue    | Mises à jour régulières, intégration avec d'autres outils    |
| Ressources multiples  | Documentation, forums et tutoriels disponibles               |

## Quelques commandes

Récupérer une image.

```
    docker image pull <nom_image>
```

Lancer un container d’une image.

```
    docker container run <nom_image>
```

Lister les conteneurs actif. Ajouter l'option `-a` pour voir tous les conteneurs.

```
    docker container ps
```

Supprimer un container.

```
    docker container rm <ID_conteneur>
```

## Pour en savoir plus

- [site de Docker](https://www.docker.com/)
- [documentation](https://docs.docker.com/)
- [github de docker](https://github.com/docker)

## Conclusion

Docker c'est :

- [x] coool
- [ ] nuuul

