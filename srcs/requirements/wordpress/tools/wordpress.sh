#!/bin/bash

mkdir -p /var/www/html

sed -i "s/listen = \/run\/php\/php7.4-fpm.sock/listen = 9000/" /etc/php/7.4/fpm/pool.d/www.conf
cd /var/www/html
sleep 10

curl -O https://raw.githubusercontent.com/wp-cli/builds/gh-pages/phar/wp-cli.phar 
chmod +x wp-cli.phar
mv wp-cli.phar /usr/local/bin/wp

wp core download --allow-root
wp config create --dbname=$DATABASE_NAME --dbuser=$DB_USER --dbpass=$DB_PASSWORD --dbhost=$DB_HOST --allow-root
wp core install --url=$DOMAINE_NAME --title=$WP_TITLE --admin_user=$WP_ADMIN_USER --admin_email=$WP_ADMIN_EMAIL --admin_password=$WP_ADMIN_PASSWORD --skip-email --allow-root
wp user create $WP_USER $WP_EMAIL --role=author --user_pass=$WP_PASSWORD --allow-root

# Install and setup products database
echo "🛍️ Setting up products database..."
chmod +x /tmp/setup-products.sh
/tmp/setup-products.sh

# Install Redis Object Cache plugin
wp plugin install redis-cache --activate --allow-root
wp config set WP_REDIS_HOST "'$REDIS_HOST'" --raw --allow-root
wp config set WP_REDIS_PORT $REDIS_PORT --raw --allow-root
wp redis enable --allow-root

# Install our custom products plugin
echo "📦 Installing Inception Products Plugin..."
mkdir -p /var/www/html/wp-content/plugins/inception-products
cp /tmp/inception-products-plugin.php /var/www/html/wp-content/plugins/inception-products/inception-products.php
wp plugin activate inception-products --allow-root

# Copy test file for debugging
cp /tmp/test-connection.php /var/www/html/test-db.php
cp /tmp/products.php /var/www/html/products.php

echo "✅ WordPress-Redis-Products integration completed!"
echo "🌐 Access your products at: https://amel-has.42.fr/products"
echo "🌐 Alternative access: https://amel-has.42.fr/products.php"
echo "🔧 Test database at: https://amel-has.42.fr/test-db.php"

exec $@     