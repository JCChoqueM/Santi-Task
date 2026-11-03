#!/bin/bash
# Pasa una instalacion que usa root a un usuario propio (solo SELECT, INSERT, UPDATE, DELETE sobre
# claudetask) y cambia la contrasena de root. Las contrasenas nuevas son aleatorias y se guardan en .env.
# Uso, desde cualquier carpeta:  bash docker/crear-usuario-bd.sh
set -euo pipefail
cd "$(dirname "$0")/.."

[ -f .env ] || { echo ".env no encontrado" >&2; exit 1; }
leer() { grep -a -E "^$1=" .env | head -1 | cut -d= -f2-; }

CONT=claudetask_mysql
USER_ACTUAL=$(leer DB_USER)
ROOT_ACTUAL=$(leer DB_PASS)
if [ "$USER_ACTUAL" != "root" ]; then
  echo "DB_USER ya es '$USER_ACTUAL', no es root: nada que hacer."
  exit 0
fi

aleatoria() { head -c 16 /dev/urandom | od -An -tx1 | tr -d ' \n'; }
NUEVO_USER=claudetask_app
DBP=$(aleatoria)
ROOTP=$(aleatoria)
mysql_root() { docker exec -e MYSQL_PWD="$1" "$CONT" mysql -uroot -N -e "$2"; }

echo "1) Comprobando la contrasena actual de root..."
mysql_root "$ROOT_ACTUAL" "SELECT 1" >/dev/null

echo "2) Creando $NUEVO_USER con permisos solo de datos sobre claudetask..."
mysql_root "$ROOT_ACTUAL" "CREATE USER IF NOT EXISTS '$NUEVO_USER'@'%' IDENTIFIED BY '$DBP'; ALTER USER '$NUEVO_USER'@'%' IDENTIFIED BY '$DBP'; GRANT SELECT, INSERT, UPDATE, DELETE ON claudetask.* TO '$NUEVO_USER'@'%';"
docker exec -e MYSQL_PWD="$DBP" "$CONT" mysql -u"$NUEVO_USER" claudetask -N -e "SELECT 1" >/dev/null

echo "3) Guardando las claves en .env (copia del anterior en ../env.copia.<fecha>)..."
cp .env "../env.copia.$(date +%Y%m%d-%H%M%S)"
sed -i -E "s/^DB_USER=.*/DB_USER=$NUEVO_USER/; s/^DB_PASS=.*/DB_PASS=$DBP/" .env
if grep -a -q -E '^MYSQL_ROOT_PASSWORD=' .env; then
  sed -i -E "s/^MYSQL_ROOT_PASSWORD=.*/MYSQL_ROOT_PASSWORD=$ROOTP/" .env
else
  printf '\nMYSQL_ROOT_PASSWORD=%s\n' "$ROOTP" >> .env
fi

echo "4) Cambiando la contrasena de root..."
mysql_root "$ROOT_ACTUAL" "ALTER USER 'root'@'%' IDENTIFIED BY '$ROOTP'; ALTER USER 'root'@'localhost' IDENTIFIED BY '$ROOTP';"

echo "5) Recreando el contenedor MySQL con el docker-compose.yml nuevo (el volumen de datos se conserva)..."
if docker compose version >/dev/null 2>&1; then DC="docker compose"; else DC="docker-compose"; fi
$DC up -d mysql
for i in $(seq 1 40); do
  docker exec -e MYSQL_PWD="$ROOTP" "$CONT" mysqladmin -uroot ping >/dev/null 2>&1 && break
  sleep 2
done
docker exec -e MYSQL_PWD="$ROOTP" "$CONT" mysqladmin -uroot ping >/dev/null

echo "6) Reiniciando claudetask_php..."
docker restart claudetask_php >/dev/null
echo "Listo. DB_USER=$NUEVO_USER. Las claves nuevas estan solo en .env. Relanza Vite: docker exec -it claudetask_php npm run dev"
