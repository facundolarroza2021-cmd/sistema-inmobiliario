echo "🚀 Iniciando Setup de Desarrollo..."
docker-compose up -d
docker-compose exec api php artisan migrate:fresh --seed
docker-compose exec api php artisan storage:link
echo "Base de datos reseteada y lista."