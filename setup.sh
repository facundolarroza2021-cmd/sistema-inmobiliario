#!/bin/bash

# --- COLORES Y FORMATO ---
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m' # No Color

echo -e "${BLUE}================================================${NC}"
echo -e "${BLUE}    🚀  SISTEMA INMOBILIARIO - SETUP PRO        ${NC}"
echo -e "${BLUE}================================================${NC}"
echo ""

# --- FUNCIÓN: COMPROBAR Y LIBERAR PUERTOS ---
check_port() {
    local port=$1
    local name=$2
    
    # Verificamos si el puerto está en uso
    if lsof -Pi :$port -sTCP:LISTEN -t >/dev/null ; then
        echo -e "${YELLOW}⚠️  El puerto $port ($name) está ocupado.${NC}"
        pid=$(lsof -Pi :$port -sTCP:LISTEN -t)
        
        echo -e "    Proceso ID (PID): $pid"
        read -p "    ¿Quieres matar este proceso automáticamente? (s/n): " confirm
        
        if [[ $confirm == "s" || $confirm == "S" ]]; then
            echo -e "    🔫 Matando proceso $pid..."
            sudo kill -9 $pid
            echo -e "${GREEN}    Puerto $port liberado.${NC}"
        else
            echo -e "${RED} No se puede iniciar si el puerto $port está ocupado. Abortando.${NC}"
            exit 1
        fi
    else
        echo -e "${GREEN} Puerto $port ($name) está libre.${NC}"
    fi
}

# 1. VERIFICACIONES INICIALES
echo -e "${YELLOW}🔍  Verificando puertos...${NC}"
check_port 3306 "Base de Datos"
check_port 8000 "API Backend"
check_port 4200 "Frontend Angular"
check_port 8081 "PhpMyAdmin"
echo ""

# 2. CONFIGURACIÓN DE ENTORNO
echo -e "${YELLOW}⚙️   Configurando entorno...${NC}"
if [ ! -f backend/.env ]; then
    echo -e "    Creando archivo .env para Backend..."
    cp backend/.env.example backend/.env 2>/dev/null || touch backend/.env
    # Rellenamos con lo básico si estaba vacío
    cat > backend/.env <<EOF
APP_NAME=Inmobiliaria
APP_ENV=local
APP_KEY=
APP_DEBUG=true
APP_URL=http://localhost
LOG_CHANNEL=stack
DB_CONNECTION=mysql
DB_HOST=db
DB_PORT=3306
DB_DATABASE=inmobiliaria_db
DB_USERNAME=root
DB_PASSWORD=root
BROADCAST_DRIVER=log
CACHE_DRIVER=file
FILESYSTEM_DISK=public
QUEUE_CONNECTION=sync
SESSION_DRIVER=file
EOF
fi

# 3. LEVANTAR CONTENEDORES
echo -e "${YELLOW}🐳  Levantando Docker... (Esto puede tardar)${NC}"
docker compose down
docker compose up -d --build

# 4. ESPERA INTELIGENTE DE DB
echo -e "${YELLOW}⏳  Esperando a que la Base de Datos despierte...${NC}"
COUNTER=0
MAX_TRIES=60
until docker compose exec db mysqladmin ping -h"localhost" -u"root" -p"root" --silent &> /dev/null; do
    echo -ne "    Esperando MySQL... ($COUNTER s)\r"
    sleep 1
    ((COUNTER++))
    if [ $COUNTER -gt $MAX_TRIES ]; then
        echo -e "${RED}Error: La base de datos tardó demasiado en arrancar.${NC}"
        exit 1
    fi
done
echo -e "${GREEN}    Base de Datos lista 🟢${NC}"

# 5. INSTALACIÓN DE DEPENDENCIAS Y MIGRACIONES
echo -e "${YELLOW} Instalando dependencias del Backend...${NC}"
docker compose exec api composer install

echo -e "${YELLOW} Generando Clave de Aplicación...${NC}"
docker compose exec api php artisan key:generate

echo -e "${YELLOW} Migrando Base de Datos y Creando Admin...${NC}"
# Usamos force para evitar preguntas en producción, seed para datos base
docker compose exec api php artisan migrate:fresh --seed --force

# Crear usuario admin asegurado
echo -e "    Creando usuario Admin..."
docker compose exec api php artisan tinker --execute="
\App\Models\User::updateOrCreate(
    ['email' => 'admin@test.com'], 
    ['name' => 'Admin', 'password' => 'password123', 'role' => 'admin', 'activo' => true]
);"

# 6. INSTALACIÓN FRONTEND
echo -e "${YELLOW}🎨  Verificando dependencias del Frontend...${NC}"
# Solo instalamos si no existe node_modules para ahorrar tiempo
if [ ! -d "frontend/node_modules" ]; then
    echo "    Instalando node_modules (Paciencia)..."
    docker compose exec web npm install
else
    echo "    node_modules ya existe. Saltando instalación."
fi


echo ""
echo -e "${GREEN}==============================================${NC}"
echo -e "${GREEN}   ✨ ¡INSTALACIÓN COMPLETADA CON ÉXITO! ✨   ${NC}"
echo -e "${GREEN}==============================================${NC}"
echo -e "   🖥️  Frontend:  ${BLUE}http://localhost:4200${NC}"
echo -e "   🔌  API:       ${BLUE}http://localhost:8000${NC}"
echo -e "   🗄️  Admin DB:  ${BLUE}http://localhost:8081${NC}"
echo ""
echo -e "   👤  Usuario:   admin@test.com"
echo -e "   🔑  Pass:      password123"
echo ""