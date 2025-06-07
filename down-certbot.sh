#!/bin/bash

# Para e remove o container nginx-certbot, se existir
docker rm -f nginx-certbot 2>/dev/null && echo "Container nginx-certbot removido com sucesso." || echo "Container nginx-certbot não estava em execução."
