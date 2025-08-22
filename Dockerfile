FROM hyperf/hyperf:8.1-alpine-v3.18-swoole-v5.0

LABEL maintainer="Hyperf Developers <group@hyperf.io>" version="1.0" license="MIT"

# ---------- env settings ----------
#SWOOLE版本
ARG SW_VERSION
#Composer版本
ARG COMPOSER_VERSION
#工作目录
ARG WORKPATH
# 时区
ARG TIMEZONE
# 添加缓存破坏器
ARG CACHE_BUSTER

ENV WORKPATH=${WORKPATH} \
    SW_VERSION=${SW_VERSION:-"v5.0"} \
    COMPOSER_VERSION=${COMPOSER_VERSION:-"2.6.6"} \
    COMPOSER_ALLOW_SUPERUSER=1 \
    TIMEZONE=${TIMEZONE:-"Asia/Shanghai"} \
    PHPIZE_DEPS="autoconf dpkg-dev dpkg file g++ gcc libc-dev make php81-dev php81-pear pkgconf re2c pcre-dev pcre2-dev zlib-dev libtool automake libaio-dev openssl-dev curl-dev"

# 系统依赖安装
RUN set -ex \
    && apk update \
    && apk add --no-cache git libstdc++ openssl bash c-ares-dev libpq-dev \
       php81-pdo_pgsql php81-pdo_sqlite php81-pdo_odbc php81-gmp \
    && apk add --no-cache --virtual .build-deps $PHPIZE_DEPS unixodbc-dev sqlite-dev \
    # 编译安装Swoole
    && cd /tmp \
    && curl -SL "https://github.com/swoole/swoole-src/archive/${SW_VERSION}.tar.gz" -o swoole.tar.gz \
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
    # PHP配置
    && echo "upload_max_filesize=128M" > /etc/php81/conf.d/00_default.ini \
    && echo "post_max_size=128M" >> /etc/php81/conf.d/00_default.ini \
    && echo "memory_limit=1G" >> /etc/php81/conf.d/00_default.ini \
    && echo "max_input_vars=PHP_INT_MAX" >> /etc/php81/conf.d/00_default.ini \
    && echo "opcache.enable_cli = 'On'" >> /etc/php81/conf.d/00_opcache.ini \
    && echo "extension=swoole.so" > /etc/php81/conf.d/50_swoole.ini \
    && echo "swoole.use_shortname = 'Off'" >> /etc/php81/conf.d/50_swoole.ini \
    && echo "date.timezone=${TIMEZONE}" >> /etc/php81/conf.d/00_default.ini \
    # 时区配置
    && ln -sf /usr/share/zoneinfo/${TIMEZONE} /etc/localtime \
    && echo "${TIMEZONE}" > /etc/timezone \
    # 安装Composer
    && wget -nv -O /usr/local/bin/composer https://github.com/composer/composer/releases/download/${COMPOSER_VERSION}/composer.phar \
    && chmod u+x /usr/local/bin/composer \
    # 清理临时文件
    && apk del .build-deps \
    && rm -rf /var/cache/apk/* /tmp/* /usr/share/man

# ---------- 项目代码与依赖 ----------
WORKDIR /www

# 克隆最新代码并安装依赖
RUN set -ex \
    # 清理可能存在的旧代码
    && rm -rf /www/* /www/.* 2>/dev/null || true \
    # 克隆最新代码
    && git clone --depth 1 https://github.com/zhujinkui/rootphp.git /tmp/code \
    # 移动代码到工作目录
    && mv /tmp/code/* /tmp/code/.[!.]* . \
    && rmdir /tmp/code \
    # 安装依赖
    && composer install --no-dev -o \
    # 执行初始化
    && php bin/hyperf.php

# 验证环境
RUN set -ex \
    && php -v \
    && php -m \
    && php --ri swoole \
    && php --ri Zend\ OPcache \
    && composer --version \
    && echo -e "\033[42;37m Build Completed :).\033[0m\n"

EXPOSE 9501

ENTRYPOINT ["php", "/www/bin/hyperf.php", "start"]