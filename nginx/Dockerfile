FROM nginx:alpine
RUN apk add --no-cache openssl gettext
COPY conf.d/ /etc/nginx/conf.d.templates/
COPY entrypoint.sh /entrypoint.sh
RUN chmod +x /entrypoint.sh
ENTRYPOINT ["/entrypoint.sh"]
CMD ["nginx", "-g", "daemon off;"]
