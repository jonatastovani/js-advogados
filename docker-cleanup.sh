#!/bin/bash

echo "Limpando todos os volumes do Docker..."
docker volume prune -f

echo "Limpando todas as imagens baixadas do Docker..."
docker rmi -f $(docker images -aq)

echo "Limpando todo o cache do Docker..."
docker system prune -af --volumes

echo "Limpeza concluída."
