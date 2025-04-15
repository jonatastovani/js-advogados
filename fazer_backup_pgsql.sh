#!/bin/bash

# Verifica se o .env foi passado como argumento
if [ -z "$1" ]; then
  echo "❌ Informe o caminho do arquivo .env (ex: .env.dev ou .env.prod)"
  exit 1
fi

# Carrega variáveis do .env informado
export $(grep -v '^#' "$1" | xargs)

# Nome do container PostgreSQL
CONTAINER=$(docker ps --filter "name=prod_stack_postgres" --format "{{.Names}}")

# Gera nome do arquivo de backup com data e hora
ARQUIVO_LOCAL="./backup_$(date +%Y-%m-%d_%H-%M-%S).sql"

echo "📦 Gerando backup do banco PostgreSQL do container: $CONTAINER"

# Executa pg_dump dentro do container
docker exec -e PGPASSWORD="$DB_PASSWORD" "$CONTAINER" \
    pg_dump -U "$DB_USERNAME" -d "$DB_DATABASE" > "$ARQUIVO_LOCAL"

# Confirma o sucesso
if [ -f "$ARQUIVO_LOCAL" ]; then
  echo "✅ Backup salvo com sucesso em: $ARQUIVO_LOCAL"
else
  echo "❌ Falha ao gerar o backup!"
  exit 1
fi
