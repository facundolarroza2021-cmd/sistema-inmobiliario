if [ -z "$1" ]
then
    echo "Error: Debes escribir un mensaje para el commit."
    echo "Ejemplo: ./deploy.sh 'Arreglando el login'"
    exit 1
fi

git add .
git commit -m "$1"
git push origin main
echo "☁️ Cambios subidos a GitHub correctamente."