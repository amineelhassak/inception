#!/bin/bash

# Check if database is already initialized
if [ ! -d "/var/lib/mysql/mysql" ]; then
    echo "Initializing MariaDB database..."
    mysql_install_db --user=mysql --datadir=/var/lib/mysql
    
    # Start MariaDB in background
    service mariadb start

    # Wait for MariaDB to be ready
    echo "Waiting for MariaDB to start..."
    until nc -z localhost 3306; do
        echo "Waiting for MariaDB..."
        sleep 2
    done

    echo "Creating database and user..."
    
    # Create database and user (without setting root password first)
    mysql -e "CREATE DATABASE IF NOT EXISTS \`${DATABASE_NAME}\`;"
    mysql -e "CREATE USER IF NOT EXISTS \`${DB_USER}\`@'%' IDENTIFIED BY '${DB_PASSWORD}';"
    mysql -e "CREATE USER IF NOT EXISTS \`${DB_USER}\`@'localhost' IDENTIFIED BY '${DB_PASSWORD}';"
    mysql -e "CREATE USER IF NOT EXISTS \`${DB_USER}\`@'wordpress.srcs_amine_network' IDENTIFIED BY '${DB_PASSWORD}';"
    mysql -e "GRANT ALL PRIVILEGES ON ${DATABASE_NAME}.* TO \`${DB_USER}\`@'%';"
    mysql -e "GRANT ALL PRIVILEGES ON ${DATABASE_NAME}.* TO \`${DB_USER}\`@'localhost';"
    mysql -e "GRANT ALL PRIVILEGES ON ${DATABASE_NAME}.* TO \`${DB_USER}\`@'wordpress.srcs_amine_network';"
    mysql -e "FLUSH PRIVILEGES;"
    
    # Set root password last
    mysql -e "ALTER USER 'root'@'localhost' IDENTIFIED BY '${DB_ROOT_PASSWORD}';"
    mysql -e "FLUSH PRIVILEGES;"
    
    echo "Database setup completed."
    
    # Stop MariaDB gracefully
    service mariadb stop
    sleep 2
else
    echo "Database already initialized, skipping setup."
fi

# Start MariaDB daemon
exec "$@"
