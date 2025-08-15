#!/bin/bash
service mariadb start

# Wait for MariaDB to be ready
until mysqladmin ping -h "localhost" --silent; do
    echo "Waiting for MariaDB..."
    sleep 2
done

# Create database and user
mysql -e "CREATE DATABASE IF NOT EXISTS \`${DATABASE_NAME}\`;"
mysql -e "CREATE USER IF NOT EXISTS \`${WP_ADMIN_USER}\`@'%' IDENTIFIED BY '${WP_ADMIN_PASSWORD}';"
mysql -e "GRANT ALL PRIVILEGES ON ${DATABASE_NAME}.* TO \`${WP_ADMIN_USER}\`@'%';"
mysql -e "ALTER USER 'root'@'localhost' IDENTIFIED BY '${DB_ROOT_PASSWORD}';"
mysql -u root -p"${DB_ROOT_PASSWORD}" -e "FLUSH PRIVILEGES;"

mysqladmin -u root -p$DB_ROOT_PASSWORD shutdown
exec "$@"
