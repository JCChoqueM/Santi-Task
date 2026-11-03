#!/bin/bash
# Crea el usuario de la aplicacion (DB_USER/DB_PASS) con permisos solo de datos sobre claudetask.
# Lo ejecuta la imagen de MySQL una sola vez, al crear el volumen vacio (despues de 01-claudetask.sql).
set -euo pipefail

: "${DB_USER:?Falta DB_USER}"
: "${DB_PASS:?Falta DB_PASS}"

# Se escriben dentro de SQL: se aceptan solo caracteres seguros
if [[ ! "$DB_USER" =~ ^[A-Za-z0-9_]{1,32}$ ]] || [ "$DB_USER" = "root" ]; then
  echo "DB_USER no valido: letras, numeros y _ (maximo 32) y distinto de root" >&2
  exit 1
fi
if [[ "$DB_PASS" == *"'"* || "$DB_PASS" == *'\'* ]]; then
  echo "DB_PASS no puede llevar comilla simple ni barra invertida" >&2
  exit 1
fi

MYSQL_PWD="$MYSQL_ROOT_PASSWORD" mysql -uroot <<SQL
CREATE USER IF NOT EXISTS '${DB_USER}'@'%' IDENTIFIED BY '${DB_PASS}';
GRANT SELECT, INSERT, UPDATE, DELETE ON claudetask.* TO '${DB_USER}'@'%';
SQL
echo "Usuario ${DB_USER} creado con SELECT, INSERT, UPDATE y DELETE sobre claudetask."
