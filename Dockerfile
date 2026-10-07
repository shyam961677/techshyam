FROM php:8.2-apache

# ── System deps + PHP Redis extension ────────────────────────────────────────
RUN apt-get update \
    && apt-get install -y --no-install-recommends $PHPIZE_DEPS \
    && pecl install redis \
    && docker-php-ext-enable redis \
    && apt-get remove -y $PHPIZE_DEPS \
    && rm -rf /var/lib/apt/lists/*

# ── Apache modules ────────────────────────────────────────────────────────────
RUN a2enmod rewrite headers

WORKDIR /var/www/html

COPY . /var/www/html/

# ── Apache virtual-host config ────────────────────────────────────────────────
# Serves the project at /, enables AllowOverride so .htaccess rewrite rules work
RUN printf '<VirtualHost *:80>\n\
    DocumentRoot /var/www/html\n\
    ServerName localhost\n\
    DirectoryIndex index.php index.html\n\
\n\
    <Directory /var/www/html>\n\
        Options -Indexes +FollowSymLinks\n\
        AllowOverride All\n\
        Require all granted\n\
    </Directory>\n\
\n\
    # Prevent serving raw JSON data files\n\
    <Directory /var/www/html/data>\n\
        Require all denied\n\
    </Directory>\n\
\n\
    # Prevent listing admin/includes directly\n\
    <Directory /var/www/html/admin/includes>\n\
        Require all denied\n\
    </Directory>\n\
\n\
    ErrorLog ${APACHE_LOG_DIR}/error.log\n\
    CustomLog ${APACHE_LOG_DIR}/access.log combined\n\
</VirtualHost>\n' > /etc/apache2/sites-available/techshyam.conf \
    && a2dissite 000-default \
    && a2ensite techshyam

# ── Runtime writable directories ─────────────────────────────────────────────
# data/ holds all JSON files; uploads/ holds user-uploaded images
RUN mkdir -p /var/www/html/data \
             /var/www/html/assets/images/uploads \
    && chown -R www-data:www-data \
             /var/www/html/data \
             /var/www/html/assets/images/uploads \
    && chmod -R 775 \
             /var/www/html/data \
             /var/www/html/assets/images/uploads

# Rest of the files are read-only for www-data
RUN chown -R www-data:www-data /var/www/html \
    && find /var/www/html -type f -not -path "*/data/*" -not -path "*/uploads/*" \
       -exec chmod 644 {} \; \
    && find /var/www/html -type d -not -path "*/data*" -not -path "*/uploads*" \
       -exec chmod 755 {} \;

EXPOSE 80

CMD ["apache2-foreground"]
