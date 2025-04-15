#!/bin/bash

# Verifica se o arquivo .env e o backup foram informados
if [ -z "$1" ] || [ -z "$2" ]; then
  echo "❌ Uso correto:"
  echo "   ./restaurar_backup_pgsql_local.sh .env.dev backup.sql [nome_banco_opcional]"
  exit 1
fi

ENV_FILE="$1"
BACKUP_FILE="$2"
CUSTOM_DB_NAME="$3"

# Carrega as variáveis do .env
export $(grep -v '^#' "$ENV_FILE" | xargs)

# Define o nome do banco: ou o terceiro argumento ou o do .env
BANCO_RESTORE="${CUSTOM_DB_NAME:-$DB_DATABASE}"

echo "🧪 Criando banco de dados '$BANCO_RESTORE'..."
createdb -U "$DB_USERNAME" "$BANCO_RESTORE"

echo "🔁 Restaurando o backup em '$BANCO_RESTORE'..."
PGPASSWORD="$DB_PASSWORD" psql -U "$DB_USERNAME" -d "$BANCO_RESTORE" < "$BACKUP_FILE"

echo "✅ Restauração concluída com sucesso!"
