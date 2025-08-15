#!/bin/bash

# Create directory for pid file
mkdir -p /run/mysqld
chown mysql:mysql /run/mysqld

# Check if database is already initialized
if [ ! -d "/var/lib/mysql/mysql" ]; then
    echo "Initializing MariaDB database..."
    mysql_install_db --user=mysql --datadir=/var/lib/mysql
    
    # Start MariaDB temporarily for setup
    mysqld_safe --user=mysql --datadir=/var/lib/mysql --socket=/var/run/mysqld/mysqld.sock &
    
    # Wait for MariaDB to be ready
    echo "Waiting for MariaDB to start..."
    until mysqladmin ping --socket=/var/run/mysqld/mysqld.sock --silent; do
        echo "Waiting for MariaDB..."
        sleep 2
    done
    
    echo "Creating database and user..."
    
    # Setup database and users
    mysql --socket=/var/run/mysqld/mysqld.sock -e "CREATE DATABASE IF NOT EXISTS \`${DATABASE_NAME}\`;"
    mysql --socket=/var/run/mysqld/mysqld.sock -e "CREATE USER IF NOT EXISTS \`${DB_USER}\`@'%' IDENTIFIED BY '${DB_PASSWORD}';"
    mysql --socket=/var/run/mysqld/mysqld.sock -e "GRANT ALL PRIVILEGES ON ${DATABASE_NAME}.* TO \`${DB_USER}\`@'%';"
    mysql --socket=/var/run/mysqld/mysqld.sock -e "ALTER USER 'root'@'localhost' IDENTIFIED BY '${DB_ROOT_PASSWORD}';"
    mysql --socket=/var/run/mysqld/mysqld.sock -e "FLUSH PRIVILEGES;"
    
    echo "Database setup completed."
    
    # Stop the temporary instance
    mysqladmin --socket=/var/run/mysqld/mysqld.sock shutdown
    sleep 2
fi

# Start MariaDB daemon properly
echo "Starting MariaDB daemon..."
exec "$@"
