#!/bin/bash

# Nom du volume Docker
NOM_DU_VOLUME="sae103"

# Chemin local sur l'hôte
CHEMIN_LOCAL="/work"

# Image Docker à utiliser
IMAGE_DOCKER="clock"

# Nom du conteneur Docker
NOM_DU_CONTENEUR="sae103-forever"

# Créer le volume
docker volume create $NOM_DU_VOLUME
echo "volume créer"

# Lancer le conteneur en montant le volume
docker image pull $IMAGE_DOCKER
docker run -v $NOM_DU_VOLUME:$CHEMIN_LOCAL $IMAGE_DOCKER -tid --name $NOM_DU_CONTENEUR
echo "volume monté"

# Copie des fichiers c vers dans le volume avec le conteneur sae103-forever comme cible
docker cp *c $NOM_DU_CONTENEUR:$CHEMIN_LOCAL
echo "copie faite"

# Execution des traitements dans le conteneur sae103-forever
docker exec $NOM_DU_CONTENEUR sh -c "php gendoc-tech.php $1 $2"

# Création du dossier où seront déplacées les documentations format PDF puis de l'archive en format tar.gz
docker exec $NOM_DU_CONTENEUR sh "dossier_sae103_pdf"
docker exec $NOM_DU_CONTENEUR sh "mv *.html *.c dossier_sae103_pdf"
docker exec $NOM_DU_CONTENEUR sh "tar czvf dossier_sae103_pdf.tar.gz dossier_sae103_pdf"

# Récupération de l'archive contenant les documentations en format PDF
docker cp $NOM_DU_CONTENEUR:$CHEMIN_LOCAL/dossier_sae103_pdf .

# Suppression du conteneur et du volume
docker rm -v $NOM_DU_CONTENEUR