# Chemin local sur l'hôte
CHEMIN_LOCAL="/work"

# Nom du dossier final
NOM_DOSSIER="dossier_sae103"

# Créer le volume
docker volume create sae103
echo "création de volume"

# Installation des images et ratachement au conteneur infini
docker image pull clock
docker image pull sae103-html2pdf
docker image pull sae103-php
docker container run --rm -v sae103:$CHEMIN_LOCAL clock -tid --name sae103-forever
echo "lancement des conteneurs"

# Copie de tout les script nécessaire dans le volume
docker cp config *.php *.c *.md sae103-forever:$CHEMIN_LOCAL
echo "copie des fichiers vers le conteneur"

# Génération des fichiers HTML avec l'image sae103-php
docker container run -v sae103:$CHEMIN_LOCAL sae10-php gendoc-tech.php --dir . > doc-tech-version.html
docker container run -v sae103:$CHEMIN_LOCAL sae10-php gendoc-user.php
echo "génération fichiers html"

# Génération des fichiers PDF avec l'image sae103-pdf
docker container run -v sae103:$CHEMIN_LOCAL sae103-pdf html2pdf DOC_TECHNIQUE.html DOC_TECHNIQUE.pdf
docker container run -v sae103:$CHEMIN_LOCAL sae103-pdf html2pdf DOC_UTILISATEUR.html DOC_UTILISATEUR.pdf
echo "génération fichier pdf"

# Création du dossier où seront déplacées les documentations format PDF puis de l'archive en format tar.gz
docker exec sae103-forever sh "mkdir $NOM_DOSSIER"
docker exec sae103-forever sh "mv *.html *.c *.pdf $NOM_DOSSIER"
docker exec sae103-forever sh "tar czvf $NOM_DOSSIER.tar.gz $NOM_DOSSIER"
echo "création du fichier tgz contenant l archive final"

# Récupération de l'archive contenant les documentations en format PDF
docker cp sae103-forever:$CHEMIN_LOCAL/$NOM_DOSSIER .
echo "copie du fichier tgz vers le repertoire courant"

# Suppression du conteneur et du volume
docker kill sae-forever
docker image prune
docker volume rm sae103
echo "supression des images, des conteneurs et du volume"