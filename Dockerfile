FROM hyperf/hyperf:8.1-alpine-v3.18-swoole-v5.0

LABEL maintainer="Hyperf Developers <group@hyperf.io>" version="1.0" license="MIT"

# Alpine Linux https://www.alpinelinux.org/releases/
# --build-arg timezone=Asia/Shanghai

##
# ---------- env settings ----------
##
# --build-arg timezone=Asia/Shanghai
ARG SW_VERSION
ARG COMPOSER_VERSION
ARG PHP_BUILD_VERSION
ARG WORKPATH
ARG TIMEZONE

ENV WORKPATH=${WORKPATH} \
    SW_VERSION=${SW_VERSION:-"v5.0"} \
    COMPOSER_VERSION=${COMPOSER_VERSION:-"2.6.6"} \
    COMPOSER_ALLOW_SUPERUSER=1 \
    TIMEZONE=${TIMEZONE:-"Asia/Shanghai"} \
    #  install and remove building packages
    PHPIZE_DEPS="autoconf dpkg-dev dpkg file g++ gcc libc-dev make php81-dev php81-pear pkgconf re2c pcre-dev pcre2-dev zlib-dev libtool automake libaio-dev openssl-dev curl-dev"

# update
RUN set -ex \
    && apk update \
    # 添加 git 用于克隆仓库
    && apk add --no-cache git \
    # for swoole extension libaio linux-headers
    && apk add --no-cache libstdc++ openssl bash c-ares-dev libpq-dev php81-pdo_pgsql php81-pdo_sqlite php81-pdo_odbc php81-gmp \
    && apk add --no-cache --virtual .build-deps $PHPIZE_DEPS unixodbc-dev sqlite-dev \
    # download
    && cd /tmp \
    && curl -SL "https://github.com/swoole/swoole-src/archive/${SW_VERSION}.tar.gz" -o swoole.tar.gz \
    && ls -alh \
    # php extension:swoole
    && cd /tmp \
    && mkdir -p swoole \
    && tar -xf swoole.tar.gz -C swoole --strip-components=1 \
    && ln -s /usr/bin/phpize81 /usr/local/bin/phpize \
    && ln -s /usr/bin/php-config81 /usr/local/bin/php-config \
    && ( \
        cd swoole \
        && phpize \
        && ./configure --enable-openssl --enable-swoole-curl --enable-cares --enable-swoole-pgsql --enable-swoole-sqlite --with-swoole-odbc=unixodbc,/usr \
        && make -s -j$(nproc) && make install \
    ) \
    # - config PHP
    && echo "upload_max_filesize=128M" > /etc/php81/conf.d/00_default.ini \
    && echo "post_max_size=128M" > /etc/php81/conf.d/00_default.ini \
    && echo "memory_limit=1G" > /etc/php81/conf.d/00_default.ini \
    && echo "max_input_vars=PHP_INT_MAX" >> /etc/php81/conf.d/00_default.ini \
    && echo "opcache.enable_cli = 'On'" >> /etc/php81/conf.d/00_opcache.ini \
    && echo "extension=swoole.so" > /etc/php81/conf.d/50_swoole.ini \
    && echo "swoole.use_shortname = 'Off'" >> /etc/php81/conf.d/50_swoole.ini \
    && echo "date.timezone=${TIMEZONE}" > /etc/php81/conf.d/00_default.ini \
    # - config timezone
    && ln -sf /usr/share/zoneinfo/${TIMEZONE} /etc/localtime \
    && echo "${TIMEZONE}" > /etc/timezone \
    # install composer
    && wget -nv -O /usr/local/bin/composer https://github.com/composer/composer/releases/download/${COMPOSER_VERSION}/composer.phar \
    && chmod u+x /usr/local/bin/composer \
    # ---------- 克隆项目并安装依赖 ----------
    && cd / \
    && git clone https://github.com/zhujinkui/rootphp.git /www \
    && cd /www \
    && composer install --no-dev -o \
    && php bin/hyperf.php \
    # ---------- clear works ----------
    && apk del .build-deps git \
    && rm -rf /var/cache/apk/* /tmp/* /usr/share/man /usr/local/bin/php* \
    # php info
    && php -v \
    && php -m \
    && php --ri swoole \
    && php --ri Zend\ OPcache \
    && composer --version \
    && echo -e "\033[42;37m Build Completed :).\033[0m\n"

WORKDIR /www

EXPOSE 9501

ENTRYPOINT ["php", "/www/bin/hyperf.php", "start"]