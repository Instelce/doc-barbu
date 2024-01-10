#!/bin/bash

# Nom du volume Docker
NOM_DU_VOLUME="sae103"

# Chemin local sur l'hôte
CHEMIN_LOCAL="/work"

# Images Docker à utiliser
IMAGE_DOCKER_INFINI="clock"
IMAGE_DOCKER_PDF="sae103-html2pdf"

# Nom du dossier final
NOM_DOSSIER="dossier_sae103"

# Nom du conteneur Docker
CONTENEUR_INFINI="sae103-forever"
CONTENEUR_PDF="sae103-pdf"

# Créer le volume
docker volume create $NOM_DU_VOLUME
echo "création de volume"

# Lancer le conteneur en montant le volume
docker image pull latest $IMAGE_DOCKER_INFINI
docker image pull latest $IMAGE_DOCKER_PDF
docker run --rm -v $NOM_DU_VOLUME:$CHEMIN_LOCAL $IMAGE_DOCKER_INFINI -tid --name $CONTENEUR_INFINI
echo "lancement des conteneurs"

# Copie des fichiers c vers dans le volume avec le conteneur sae103-forever comme cible
docker cp *.c $CONTENEUR_INFINI:$CHEMIN_LOCAL
echo "copie des fichiers c faite"

# Execution des traitements dans le conteneur sae103-forever puis génération des fichiers pdf grâce à html2pdf
docker exec $CONTENEUR_INFINI sh -c "php gendoc-tech.php $@ > DOC_TECHNIQUE.html && php gendoc-user.php"
docker run -v $NOM_DU_VOLUME:$CHEMIN_LOCAL $IMAGE_DOCKER_PDF html2pdf DOC_TECHNIQUE.html DOC_TECHNIQUE.pdf
docker run -v $NOM_DU_VOLUME:$CHEMIN_LOCAL $IMAGE_DOCKER_PDF html2pdf DOC_UTILISATEUR.html DOC_UTILISATEUR.pdf
echo "création des fichiers html et pdf"

# Création du dossier où seront déplacées les documentations format PDF puis de l'archive en format tar.gz
docker exec $CONTENEUR_INFINI sh "$NOM_DOSSIER"
docker exec $CONTENEUR_INFINI sh "mv *.html *.c *.pdf $NOM_DOSSIER"
docker exec $CONTENEUR_INFINI sh "tar czvf $NOM_DOSSIER.tar.gz $NOM_DOSSIER"
echo "création du fichier tgz contenant l archive final"

# Récupération de l'archive contenant les documentations en format PDF
docker cp $CONTENEUR_INFINI:$CHEMIN_LOCAL/$NOM_DOSSIER .
echo "copie du fichier tgz vers le repertoire courant"

# Suppression du conteneur et du volume
docker kill $CONTENEUR_INFINI
docker image prune
docker volume rm $NOM_DU_VOLUME
echo "supression des images, des conteneurs et du volume"