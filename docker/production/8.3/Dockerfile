FROM ubuntu:22.04

LABEL maintainer="Taylor Otwell"

ARG WWWGROUP
ARG NODE_VERSION=20
ARG MYSQL_CLIENT="mysql-client"
ARG POSTGRES_VERSION=15

WORKDIR /var/www/html

RUN apt-get update && apt-get install -y tzdata

ENV DEBIAN_FRONTEND noninteractive
ENV TZ=America/Asuncion
ENV SUPERVISOR_PHP_USER="sail"

RUN ln -snf /usr/share/zoneinfo/$TZ /etc/localtime && echo $TZ > /etc/timezone

RUN apt-get update \
    && mkdir -p /etc/apt/keyrings \
    && apt-get install -y cron gnupg gosu curl ca-certificates zip unzip git supervisor sqlite3 libpng-dev python2 dnsutils librsvg2-bin fswatch ffmpeg nano autoconf make gcc libc-dev pkg-config libaio1 \
    && curl -sS 'https://keyserver.ubuntu.com/pks/lookup?op=get&search=0x14aa40ec0831756756d7f66c4f4ea0aae5267a6c' | gpg --dearmor | tee /etc/apt/keyrings/ppa_ondrej_php.gpg > /dev/null \
    && echo "deb [signed-by=/etc/apt/keyrings/ppa_ondrej_php.gpg] https://ppa.launchpadcontent.net/ondrej/php/ubuntu jammy main" > /etc/apt/sources.list.d/ppa_ondrej_php.list \
    && apt-get update \
    && apt-get install -y php8.3-cli php8.3-fpm php8.3-dev \
    php8.3-pgsql php8.3-sqlite3 php8.3-gd \
    php8.3-curl \
    php8.3-imap php8.3-mysql php8.3-mbstring \
    php8.3-xml php8.3-zip php8.3-bcmath php8.3-soap \
    php8.3-intl php8.3-readline \
    php8.3-ldap \
    php8.3-msgpack php8.3-igbinary php8.3-redis php8.3-swoole \
    php8.3-memcached php8.3-pcov php8.3-imagick php8.3-xdebug \
    && curl -sLS https://getcomposer.org/installer | php -- --install-dir=/usr/bin/ --filename=composer \
    && curl -fsSL https://deb.nodesource.com/gpgkey/nodesource-repo.gpg.key | gpg --dearmor -o /etc/apt/keyrings/nodesource.gpg \
    && echo "deb [signed-by=/etc/apt/keyrings/nodesource.gpg] https://deb.nodesource.com/node_$NODE_VERSION.x nodistro main" > /etc/apt/sources.list.d/nodesource.list \
    && apt-get update \
    && apt-get install -y nodejs \
    && npm install -g npm \
    && npm install -g pnpm \
    && npm install -g bun \
    && curl -sS https://dl.yarnpkg.com/debian/pubkey.gpg | gpg --dearmor | tee /etc/apt/keyrings/yarn.gpg >/dev/null \
    && echo "deb [signed-by=/etc/apt/keyrings/yarn.gpg] https://dl.yarnpkg.com/debian/ stable main" > /etc/apt/sources.list.d/yarn.list \
    && curl -sS https://www.postgresql.org/media/keys/ACCC4CF8.asc | gpg --dearmor | tee /etc/apt/keyrings/pgdg.gpg >/dev/null \
    && echo "deb [signed-by=/etc/apt/keyrings/pgdg.gpg] http://apt.postgresql.org/pub/repos/apt jammy-pgdg main" > /etc/apt/sources.list.d/pgdg.list \
    && apt-get update \
    && apt-get install -y yarn \
    && apt-get install -y $MYSQL_CLIENT \
    && apt-get install -y postgresql-client-$POSTGRES_VERSION \
    && apt-get -y autoremove \
    && apt-get clean \
    && rm -rf /var/lib/apt/lists/* /tmp/* /var/tmp/*

# Crear el grupo y el usuario 'sail'
RUN groupadd --force -g $WWWGROUP sail
RUN useradd -ms /bin/bash --no-user-group -g $WWWGROUP -u 1337 sail

# --- CONFIGURACIÓN DE PHP-FPM ---
# 1. Asegurar que FPM se ejecute en primer plano para Docker/Supervisor
RUN sed -i 's/daemonize = yes/daemonize = no/' /etc/php/8.3/fpm/php-fpm.conf
# 2. Mover el log de errores a la carpeta de storage de Laravel
RUN sed -i 's/error_log = \/var\/log\/php8.3-fpm.log/error_log = \/var\/www\/html\/storage\/logs\/php-fpm.log/' /etc/php/8.3/fpm/php-fpm.conf
# 3. Configurar el "pool" de FPM para que escuche en el puerto 9000 y se ejecute como usuario 'sail'
RUN sed -i 's/listen = \/run\/php\/php8.3-fpm.sock/listen = 9000/' /etc/php/8.3/fpm/pool.d/www.conf
RUN sed -i 's/user = www-data/user = sail/' /etc/php/8.3/fpm/pool.d/www.conf
RUN sed -i 's/group = www-data/group = sail/' /etc/php/8.3/fpm/pool.d/www.conf
# --- FIN DE LA CONFIGURACIÓN ---

# Descargar e instalar Oracle Instant Client y SDK
RUN curl -o instantclient-basic-linux.x64-19.6.0.0.0dbru.zip https://download.oracle.com/otn_software/linux/instantclient/19600/instantclient-basic-linux.x64-19.6.0.0.0dbru.zip \
    && unzip instantclient-basic-linux.x64-19.6.0.0.0dbru.zip -d /usr/lib/oracle/ \
    && rm instantclient-basic-linux.x64-19.6.0.0.0dbru.zip \
    && curl -o instantclient-sdk-linux.x64-19.6.0.0.0dbru.zip https://download.oracle.com/otn_software/linux/instantclient/19600/instantclient-sdk-linux.x64-19.6.0.0.0dbru.zip \
    && unzip instantclient-sdk-linux.x64-19.6.0.0.0dbru.zip -d /usr/lib/oracle/ \
    && rm instantclient-sdk-linux.x64-19.6.0.0.0dbru.zip \
    && echo /usr/lib/oracle/instantclient_19_6 > /etc/ld.so.conf.d/oracle-instantclient.conf \
    && ldconfig

ENV LD_LIBRARY_PATH=/usr/lib/oracle/instantclient_19_6

# Configurar e instalar la extensión OCI8
RUN echo 'instantclient,/usr/lib/oracle/instantclient_19_6' | pecl install oci8-beta

# Habilitar la extensión para CLI y FPM
RUN echo "extension=oci8.so" > /etc/php/8.3/cli/conf.d/20-oci8.ini
RUN echo "extension=oci8.so" > /etc/php/8.3/fpm/conf.d/20-oci8.ini

COPY start-container /usr/local/bin/start-container
COPY supervisord.conf /etc/supervisor/conf.d/supervisord.conf
COPY php.ini /etc/php/8.3/cli/conf.d/99-sail.ini
COPY php.ini /etc/php/8.3/fpm/conf.d/99-sail.ini
RUN echo "date.timezone=America/Asuncion" > /etc/php/8.3/cli/conf.d/99-timezone.ini
RUN echo "date.timezone=America/Asuncion" > /etc/php/8.3/fpm/conf.d/99-timezone.ini
RUN chmod +x /usr/local/bin/start-container

EXPOSE 9000

RUN echo "* * * * * sail cd /var/www/html && php artisan schedule:run >> /dev/null 2>&1" > /etc/cron.d/laravel-cron \
    && chmod 0644 /etc/cron.d/laravel-cron \
    && crontab /etc/cron.d/laravel-cron

ENTRYPOINT ["start-container"]