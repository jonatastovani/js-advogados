# Defina a versão do NGINX especificada ou uma versão padrão
ARG NGINX_VERSION=${NGINX_VERSION}
FROM nginx:${NGINX_VERSION}-alpine

# Copia o arquivo de configuração nginx.conf personalizado para o contêiner
COPY nginx/nginx.conf /etc/nginx/

# Atualiza e instala os pacotes necessários
RUN apk update && \
    apk add --no-cache logrotate openssl bash certbot certbot-nginx

# Remove a configuração padrão do NGINX para evitar conflitos
RUN rm -rf /etc/nginx/conf.d/default.conf

# Cria diretórios para o conteúdo do site e dá suas respectivas permissões
RUN mkdir -p /var/www/app && \
    chown -R www-data:www-data /var/www && \
    chmod 755 -R /var/www

# Define o diretório de trabalho para o NGINX
WORKDIR /etc/nginx

# Limpeza final para reduzir o tamanho da imagem
RUN apk del --no-cache

# Inicia o NGINX quando o contêiner é executado
CMD ["nginx", "-g", "daemon off;"]
