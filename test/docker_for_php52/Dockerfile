FROM ubuntu:12.04

# configuration mostly copied from https://github.com/kuborgh/docker-php-5.2

RUN mkdir /php && \
    cd /php && \
    apt-get update && \
    apt-get install -y --no-install-recommends \
        autoconf binutils build-essential bzip2 ca-certificates \
        comerr-dev cpp cpp-4.6 dpkg-dev g++ g++-4.6 gcc gcc-4.6 krb5-multidev \
        libapr1-dev libaprutil1-dev libaspell-dev libaspell15 libbz2-dev \
        libc-client2007e libc-client2007e-dev libc-dev-bin libc6-dev libcurl3 \
        libcurl4-openssl-dev libdpkg-perl libexpat1-dev libfreetype6 \
        libfreetype6-dev libgcrypt11-dev libgdbm-dev libgmp10 libgnutls-dev \
        libgnutls-openssl27 libgnutlsxx27 libgomp1 libgpg-error-dev \
        libgssapi-krb5-2 libgssrpc4 libice-dev libice6 libidn11 libidn11-dev \
        libjpeg-dev libjpeg-turbo8 libjpeg-turbo8-dev libjpeg8 libjpeg8-dev \
        libk5crypto3 libkadm5clnt-mit8 libkadm5srv-mit8 libkdb5-6 libkeyutils1 \
        libkrb5-3 libkrb5-dev libkrb5support0 libldap2-dev libltdl-dev libltdl7 \
        libmagic-dev libmagic1 libmcrypt-dev libmcrypt4 libmhash-dev libmhash2 \
        libmpc2 libmpfr4 libncurses5-dev \
        libp11-kit-dev libpam0g-dev libpcre3-dev libpcrecpp0 libpng12-dev libpopt0 \
        libpq-dev libpq5 libpspell-dev libpthread-stubs0 libpthread-stubs0-dev \
        libquadmath0 libreadline-dev libreadline6-dev librtmp-dev librtmp0 \
        libsm-dev libsm6 libsqlite3-dev libssl-dev libstdc++6-4.6-dev libt1-5 \
        libt1-dev libtasn1-3-dev libtimedate-perl libtinfo-dev libx11-6 libx11-data \
        libx11-dev libxau-dev libxau6 libxaw7 libxaw7-dev libxcb1 libxcb1-dev \
        libxdmcp-dev libxdmcp6 libxext-dev libxext6 libxml2 libxml2-dev libxmu-dev \
        libxmu-headers libxmu6 libxpm-dev libxpm4 libxt-dev libxt6 linux-libc-dev \
        m4 make mlock mysql-common openssl patch pkg-config uuid-dev wget \
        x11-common x11proto-core-dev x11proto-input-dev x11proto-kb-dev \
        x11proto-xext-dev xorg-sgml-doctools xtrans-dev zlib1g-dev \
    && \
    wget http://museum.php.net/php5/php-5.2.17.tar.bz2 && \
    tar xfj php-5.2.17.tar.bz2 && \
    ln -s /usr/lib/x86_64-linux-gnu/libjpeg.* /usr/lib/ && \
    ln -s /usr/lib/x86_64-linux-gnu/libpng.* /usr/lib/ && \
    ln -s /usr/lib/x86_64-linux-gnu/libkrb5.* /usr/lib/ && \
    ln -s /usr/lib/x86_64-linux-gnu/libmysqlclient.* /usr/lib/ && \
    cd php-5.2.17; \
    wget -c -t 3 -O ./debian_patches_disable_SSLv2_for_openssl_1_0_0.patch https://bugs.php.net/patch-display.php\?bug_id\=54736\&patch\=debian_patches_disable_SSLv2_for_openssl_1_0_0.patch\&revision=1305414559\&download\=1 && \
    patch -p1 -b < debian_patches_disable_SSLv2_for_openssl_1_0_0.patch && \

    ./configure \
        --bindir=/usr/bin \
        --sbindir=/usr/sbin \
        --prefix=/usr \
        --build=i686-pc-linux-gnu \
        --host=i686-pc-linux-gnu \
        --mandir=/usr/share/man \
        --infodir=/usr/share/info \
        --datadir=/usr/share \
        --sysconfdir=/etc \
        --localstatedir=/var/lib \
        --prefix=/usr/lib/php5.2 \
        --mandir=/usr/lib/php5.2/man \
        --infodir=/usr/lib/php5.2/info \
        --libdir=/usr/lib/php5.2/lib \
        --with-libdir=lib \
        --with-pear \
        --disable-maintainer-zts \
        --enable-bcmath \
        --with-bz2 \
        --enable-calendar \
        --with-curl \
        --with-curlwrappers \
        --disable-dbase \
        --enable-exif \
        --without-fbsql \
        --without-fdftk \
        --enable-ftp \
        --with-gettext \
        --without-gmp \
        --disable-ipv6 \
        --with-kerberos \
        --enable-mbstring \
        --with-mcrypt \
        --with-mhash \
        --without-msql \
        --without-mssql \
        --with-ncurses \
        --with-openssl \
        --with-openssl-dir=/usr \
        --disable-pcntl \
        --without-pgsql \
        --with-pspell \
        --without-recode \
        --disable-shmop \
        --without-snmp \
        --enable-soap \
        --enable-sockets \
        --without-sybase-ct \
        --disable-sysvmsg \
        --disable-sysvsem \
        --disable-sysvshm \
        --without-tidy \
        --disable-wddx \
        --disable-xmlreader \
        --disable-xmlwriter \
        --with-xmlrpc \
        --without-xsl \
        --enable-zip \
        --with-zlib \
        --disable-debug \
        --enable-dba \
        --without-cdb \
        --disable-flatfile \
        --with-gdbm \
        --disable-inifile \
        --without-qdbm \
        --with-freetype-dir=/usr \
        --with-t1lib=/usr \
        --disable-gd-jis-conv \
        --with-jpeg-dir=/usr \
        --with-png-dir=/usr \
        --without-xpm-dir \
        --with-gd \
        --with-imap \
        --with-imap-ssl \
        --without-interbase \
        --without-mysql \
        --without-mysqli \
        --without-oci8 \
        --without-pdo-dblib \
        --without-pdo-mysql \
        --without-pdo-pgsql \
        --without-pdo-odbc \
        --with-readline \
        --without-libedit \
        --without-mm \
        --with-pcre-regex \
        --with-config-file-path=/etc/php/cli-php5.2 \
        --with-config-file-scan-dir=/etc/php/cli-php5.2/ext-active \
        --enable-cli \
        --disable-cgi \
        --disable-embed \
        --with-pic \
    && \
    make clean && \
    make && \
    make install && \

    pecl install phar && \

    wget https://github.com/treffynnon/php5.2-phpunit3.6.12-phar/releases/download/1.0.2/php52-phpunit.phar -O ~/phpunit && \
    chmod +x ~/phpunit && \

    cd /php && \

    rm -Rf /php && \
    rm -Rf /var/cache/* && \
    rm -Rf /tmp/pear && \
    apt-get purge -y \
        apache2-prefork-dev autoconf binutils build-essential bzip2 \
        comerr-dev cpp cpp-4.6 dpkg-dev g++ g++-4.6 gcc gcc-4.6 krb5-multidev \
        libapr1-dev libaprutil1-dev libaspell-dev libbz2-dev \
        libc-client2007e-dev libc-dev-bin libc6-dev \
        libcurl4-openssl-dev libdpkg-perl libexpat1-dev \
        libfreetype6-dev libgcrypt11-dev libgdbm-dev libgmp10 libgnutls-dev \
        libgnutls-openssl27 libgnutlsxx27 libgomp1 libgpg-error-dev \
        libgssrpc4 libice-dev libice6 libidn11-dev \
        libjpeg-dev libjpeg-turbo8-dev libjpeg8-dev \
        libkadm5clnt-mit8 libkadm5srv-mit8 libkdb5-6 \
        libkrb5-dev libldap2-dev libltdl-dev libltdl7 \
        libmagic-dev libmcrypt-dev libmhash-dev \
        libmpc2 libmpfr4 libmysqlclient-dev libncurses5-dev \
        libp11-kit-dev libpam0g-dev libpcre3-dev libpcrecpp0 libpng12-dev libpopt0 \
        libpq-dev libpq5 libpspell-dev libpthread-stubs0 libpthread-stubs0-dev \
        libquadmath0 libreadline-dev libreadline6-dev librtmp-dev \
        libsm-dev libsm6 libsqlite3-dev libssl-dev libstdc++6-4.6-dev \
        libt1-dev libtasn1-3-dev libtimedate-perl libtinfo-dev \
        libx11-dev libxau-dev libxaw7 libxaw7-dev libxcb1-dev \
        libxdmcp-dev libxext-dev libxext6 libxml2-dev libxmu-dev \
        libxmu-headers libxmu6 libxpm-dev libxpm4 libxt-dev libxt6 linux-libc-dev \
        m4 make patch pkg-config uuid-dev wget \
        x11-common x11proto-core-dev x11proto-input-dev x11proto-kb-dev \
        x11proto-xext-dev xorg-sgml-doctools xtrans-dev zlib1g-dev \
    && \
    apt-get clean && \
    rm -rf /var/lib/apt/lists/*

COPY php.ini /etc/php/cli-php5.2/