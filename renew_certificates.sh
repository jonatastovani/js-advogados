#!/bin/sh
while :; do
    certbot renew --quiet --webroot --webroot-path=/var/www/app/public
    sleep 5184000 # Espera 60 dias
done
