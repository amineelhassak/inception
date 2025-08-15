#!/bin/bash

# Check if database is already initialized
if [ ! -d "/var/lib/mysql/mysql" ]; then
    echo "Initializing MariaDB database..."
    mysql_install_db --user=mysql --datadir=/var/lib/mysql
fi

# Start MariaDB in background
service mariadb start

# Wait for MariaDB to be ready
echo "Waiting for MariaDB to start..."
until mysqladmin ping -h "localhost" --silent; do
    echo "Waiting for MariaDB..."
    sleep 2
done

# Check if database already exists
if ! mysql -e "USE ${DATABASE_NAME};" 2>/dev/null; then
    echo "Creating database and user..."
    
    # Create database and user
    mysql -e "CREATE DATABASE IF NOT EXISTS \`${DATABASE_NAME}\`;"
    mysql -e "CREATE USER IF NOT EXISTS \`${WP_ADMIN_USER}\`@'%' IDENTIFIED BY '${WP_ADMIN_PASSWORD}';"
    mysql -e "GRANT ALL PRIVILEGES ON ${DATABASE_NAME}.* TO \`${WP_ADMIN_USER}\`@'%';"
    mysql -e "ALTER USER 'root'@'localhost' IDENTIFIED BY '${DB_ROOT_PASSWORD}';"
    mysql -e "FLUSH PRIVILEGES;"
    
    echo "Database setup completed."
else
    echo "Database already exists, skipping creation."
fi

# Stop MariaDB to restart with proper daemon
mysqladmin -u root -p"${DB_ROOT_PASSWORD}" shutdown 2>/dev/null || mysqladmin shutdown

# Start MariaDB daemon
exec "$@"
