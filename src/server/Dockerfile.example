FROM yiisoftware/yii2-php:8.3-apache

RUN cd /app ** /bin/sh -c php composer.phar install
RUN a2enmod rewrite && a2enmod ssl && a2enmod socache_shmcb
RUN sed -i 's|SSLCertificateFile      /etc/ssl/certs/ssl-cert-snakeoil.pem|SSLCertificateFile /etc/ssl/certs/mycert.crt|g' /etc/apache2/sites-available/default-ssl.conf
RUN sed -i 's|SSLCertificateKeyFile   /etc/ssl/private/ssl-cert-snakeoil.key|SSLCertificateKeyFile /etc/ssl/certs/mycert.key|g' /etc/apache2/sites-available/default-ssl.conf
RUN sed -i 's|/var/www/html|/app/web|g' /etc/apache2/sites-available/default-ssl.conf

RUN su - && apt update
RUN su - && apt install -y vim
RUN su - && apt install libnss3-tools -y
RUN su - && cd /etc/ssl/certs/ && openssl req -subj "/C=BR/ST=Piaui/L=Teresina/O=Weebz/CN=weebz.com.br" -x509 -new -out mycert.crt -keyout mycert.key -days 365 -newkey rsa:4096 -sha256 -nodes
RUN su - && a2enmod ssl && a2enmod socache_shmcb
RUN su - && a2ensite default-ssl