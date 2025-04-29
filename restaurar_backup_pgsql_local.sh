#!/bin/bash

ENV_FILE="$1"
BACKUP_FILE="$2"
CUSTOM_DB_NAME="$3"

if [ -z "$ENV_FILE" ] || [ -z "$BACKUP_FILE" ]; then
  echo "❌ Uso: ./restaurar_backup_pgsql_local.sh ../.env.dev ../dumps/backup.sql [nome_banco_opcional]"
  exit 1
fi

# Carrega variáveis do .env
set -o allexport
source "$ENV_FILE"
set +o allexport

# Nome do banco: argumento ou do .env
BANCO_RESTORE="${CUSTOM_DB_NAME:-$DB_DATABASE}"

echo "🧪 Criando banco '$BANCO_RESTORE' dentro do container..."

docker run --rm \
  --network "$PREFIX_SWARM"_laravel \
  -e PGPASSWORD="$DB_PASSWORD" \
  postgres:16.3 \
  createdb -h postgres -U "$DB_USERNAME" "$BANCO_RESTORE"

echo "🔁 Restaurando backup do arquivo '$BACKUP_FILE'..."

docker run --rm \
  --network "$PREFIX_SWARM"_laravel \
  -v "$(realpath "$BACKUP_FILE"):/backup.sql" \
  -e PGPASSWORD="$DB_PASSWORD" \
  postgres:16.3 \
  psql -h postgres -U "$DB_USERNAME" -d "$BANCO_RESTORE" -f /backup.sql

echo "✅ Restauração finalizada com sucesso no banco '$BANCO_RESTORE'!"
