FROM nginx:1.30.2

COPY ./deploy/nginx/config/nginx.conf /etc/nginx/nginx.conf
COPY ./deploy/nginx/config/default.conf /etc/nginx/conf.d/current.conf

USER root

RUN ln -svf /dev/stdout /var/log/nginx/access.log \
    && ln -svf /dev/stderr /var/log/nginx/error.log

RUN rm -rf /var/www/html/
ADD . /var/www/html/
RUN chown -R www-data:www-data /var/www/html/

WORKDIR /var/www/html/
