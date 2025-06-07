#!/bin/bash

# Domínios a serem incluídos no certificado
DOMAINS=(
  jsadvogados.byteforge.com.br
  jsadvogadosprev.byteforge.com.br
  jsadvogadostupi.byteforge.com.br
  teste.byteforge.com.br
  admin.byteforge.com.br
)

# Monta os parâmetros de domínio para o certbot
DOMAIN_ARGS=""
for domain in "${DOMAINS[@]}"; do
  DOMAIN_ARGS="$DOMAIN_ARGS -d $domain"
done

# Executa o certbot usando Docker
docker run --rm \
  -v "/home/tovani/js-advogados/docker/certbot/conf:/etc/letsencrypt" \
  -v "/home/tovani/js-advogados/docker/certbot/www:/var/www/certbot" \
  certbot/certbot certonly --webroot \
  --webroot-path=/var/www/certbot \
  $DOMAIN_ARGS \
  --email byteforgesuporte@gmail.com \
  --agree-tos \
  --no-eff-email \
  --rsa-key-size 4096 \
  --force-renewal
