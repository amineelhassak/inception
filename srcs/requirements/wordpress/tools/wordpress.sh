#!/bin/bash

mkdir -p /var/www/html

# Configure PHP-FPM
sed -i "s/listen = \/run\/php\/php7.4-fpm.sock/listen = 9000/" /etc/php/7.4/fpm/pool.d/www.conf

cd /var/www/html

# Download and install WP-CLI
curl -O https://raw.githubusercontent.com/wp-cli/builds/gh-pages/phar/wp-cli.phar
chmod +x wp-cli.phar
mv wp-cli.phar /usr/local/bin/wp

# Wait for MariaDB to be ready
echo "Waiting for MariaDB to be ready..."
until nc -z mariadb 3306; do
    echo "Waiting for database connection..."
    sleep 3
done

# Additional wait to ensure database is fully ready
sleep 10

# Download WordPress if not already present
if [ ! -f wp-config.php ]; then
    echo "Setting up WordPress..."
    
    # Download WordPress
    wp core download --allow-root
    
    # Create wp-config.php
    wp config create \
        --dbname=$DATABASE_NAME \
        --dbuser=$DB_USER \
        --dbpass=$DB_PASSWORD \
        --dbhost=$DB_HOST \
        --allow-root

    # Install WordPress if not already installed
    if ! wp core is-installed --allow-root; then
        wp core install \
            --url=$DOMAINE_NAME \
            --title="$WP_TITLE" \
            --admin_user=$WP_ADMIN_USER \
            --admin_email=$WP_ADMIN_EMAIL \
            --admin_password=$WP_ADMIN_PASSWORD \
            --skip-email \
            --allow-root
            
        # Create additional user
        wp user create $WP_USER $WP_EMAIL \
            --role=author \
            --user_pass=$WP_PASSWORD \
            --allow-root
    fi

    # Configure Redis
    wp config set WP_REDIS_HOST redis --allow-root
    wp config set WP_REDIS_PORT 6379 --allow-root
    
    # Install and activate Redis cache plugin
    wp plugin install redis-cache --activate --allow-root
    wp redis enable --allow-root
    
    echo "WordPress setup completed."
else
    echo "WordPress already configured."
fi

# Start PHP-FPM
exec "$@"