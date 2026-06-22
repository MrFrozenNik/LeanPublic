#!/bin/sh
set -e

CERTS_DIR=/etc/nginx/certs
mkdir -p "$CERTS_DIR"

for domain in "$LARAVEL_DOMAIN" "$FASTAPI_DOMAIN"; do
    if [ ! -f "$CERTS_DIR/$domain.crt" ]; then
        openssl req -x509 -nodes -days 365 -newkey rsa:2048 \
            -keyout "$CERTS_DIR/$domain.key" \
            -out "$CERTS_DIR/$domain.crt" \
            -subj "/C=RU/CN=$domain"
    fi
done

for tmpl in /etc/nginx/conf.d.templates/*.conf.template; do
    filename=$(basename "$tmpl" .template)
    envsubst '${LARAVEL_DOMAIN}${FASTAPI_DOMAIN}' < "$tmpl" > "/etc/nginx/conf.d/$filename"
done

exec nginx -g "daemon off;"
