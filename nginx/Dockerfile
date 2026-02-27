FROM nginx:alpine

RUN apk add --no-cache gettext

COPY nginx/default.conf.template /etc/nginx/conf.d/default.conf.template

CMD ["/bin/sh", "-c", "envsubst '$$NGINX_PORT $$NGINX_HOST $$PHP_FPM_HOST $$PHP_FPM_PORT' < /etc/nginx/conf.d/default.conf.template > /etc/nginx/conf.d/default.conf && exec nginx -g 'daemon off;'"]