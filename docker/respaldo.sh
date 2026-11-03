#!/bin/bash
# Respaldo completo de la base claudetask en respaldos/claudetask-<fecha>.sql (solo lectura para el archivo: 600).
# No incluye usuarios de MySQL: los crea docker/initdb/02-usuario.sh o docker/crear-usuario-bd.sh.
# El archivo contiene datos personales y los hash de las contrasenas: guardalo fuera del equipo y del repositorio.
# Uso, desde cualquier carpeta:  bash docker/respaldo.sh
set -euo pipefail
umask 077
cd "$(dirname "$0")/.."

[ -f .env ] || { echo ".env no encontrado" >&2; exit 1; }
CONT=${CONTENEDOR:-claudetask_mysql}
if [ -n "${ROOT_PASS:-}" ]; then
  ROOTP=$ROOT_PASS
else
  ROOTP=$(grep -a -E '^MYSQL_ROOT_PASSWORD=' .env | head -1 | cut -d= -f2-)
fi
[ -n "$ROOTP" ] || { echo "Falta MYSQL_ROOT_PASSWORD en .env" >&2; exit 1; }

mkdir -p respaldos
ARCHIVO="respaldos/claudetask-$(date +%Y%m%d-%H%M%S).sql"
if ! docker exec -e MYSQL_PWD="$ROOTP" "$CONT" mysqldump -uroot --databases claudetask \
     --single-transaction --no-tablespaces --default-character-set=utf8mb4 > "$ARCHIVO"; then
  rm -f "$ARCHIVO"
  echo "El respaldo fallo; no se guardo nada." >&2
  exit 1
fi
if ! grep -a -q 'CREATE TABLE `usuarios`' "$ARCHIVO"; then
  rm -f "$ARCHIVO"
  echo "El respaldo salio incompleto; se descarto." >&2
  exit 1
fi
echo "Respaldo guardado en $ARCHIVO ($(wc -c < "$ARCHIVO") bytes)"
