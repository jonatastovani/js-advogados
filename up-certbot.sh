#!/bin/bash

# Sobe o nginx com a configuração para responder os desafios ACME
docker run --rm -d \
  --name nginx-certbot \
  -p 80:80 \
  -v $(pwd)/docker/nginx/default-certbot.conf:/etc/nginx/conf.d/default.conf \
  -v $(pwd)/docker/certbot/www:/var/www/certbot \
  nginx:alpine
