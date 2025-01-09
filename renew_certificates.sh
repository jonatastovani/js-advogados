#!/bin/sh
while true; do
    certbot renew --quiet --webroot --webroot-path=/var/www/app/public
    sleep 5184000 # 60 dias em segundos
done