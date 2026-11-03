#!/bin/bash
# Restaura un respaldo hecho con docker/respaldo.sh. REEMPLAZA las tablas actuales de claudetask.
# Uso:  bash docker/restaurar.sh respaldos/claudetask-AAAAMMDD-HHMMSS.sql
set -euo pipefail
cd "$(dirname "$0")/.."

ARCHIVO=${1:?Uso: bash docker/restaurar.sh respaldos/claudetask-AAAAMMDD-HHMMSS.sql}
[ -s "$ARCHIVO" ] || { echo "No existe o esta vacio: $ARCHIVO" >&2; exit 1; }
CONT=${CONTENEDOR:-claudetask_mysql}
if [ -n "${ROOT_PASS:-}" ]; then
  ROOTP=$ROOT_PASS
else
  [ -f .env ] || { echo ".env no encontrado" >&2; exit 1; }
  ROOTP=$(grep -a -E '^MYSQL_ROOT_PASSWORD=' .env | head -1 | cut -d= -f2-)
fi
[ -n "$ROOTP" ] || { echo "Falta MYSQL_ROOT_PASSWORD en .env" >&2; exit 1; }

echo "Esto reemplaza los datos actuales de claudetask en $CONT por los de $ARCHIVO."
printf 'Escribe si para continuar: '
read -r RESPUESTA
[ "$RESPUESTA" = "si" ] || { echo "Cancelado."; exit 1; }

docker exec -i -e MYSQL_PWD="$ROOTP" "$CONT" mysql -uroot < "$ARCHIVO"
echo "Restaurado desde $ARCHIVO."
