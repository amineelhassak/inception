# Inception Project - Deployment Guide

## 📋 Requirements
- Linux system (Ubuntu/Debian/Kali Linux)
- Docker & Docker Compose installed
- User with sudo privileges

## 🚀 Quick Setup

### 1. **Install Docker (if not installed)**
```bash
curl -fsSL https://get.docker.com -o get-docker.sh
sudo sh get-docker.sh
sudo usermod -aG docker $USER
# Log out and log back in
```

### 2. **Setup Project**
```bash
# Clone or copy the project to your machine
cd inception

# Add domain to hosts file
echo "127.0.0.1 amel-has.42.fr" | sudo tee -a /etc/hosts

# Create data directories
sudo mkdir -p /home/amel-has/data/mariadb
sudo mkdir -p /home/amel-has/data/wordpress
sudo chown -R $USER:$USER /home/amel-has/data
```

### 3. **Run the Project**
```bash
make all
```

## 🌐 Access URLs
- **WordPress**: https://amel-has.42.fr
- **Adminer**: http://localhost:8080
- **Portainer**: http://localhost:9000
- **Static Website**: http://localhost:8000
- **FTP**: ftp://localhost:21 (user: amine, pass: amine1337)

## 📝 Useful Commands
- `make logs` - View all logs
- `make status` - Check container status
- `make down` - Stop services
- `make clean` - Stop and remove volumes
- `make help` - Show all available commands

## 🔧 WordPress Credentials
- **Admin User**: amine
- **Admin Password**: amine123
- **Author User**: zwich
- **Author Password**: zwich1337

## 🗃️ Database Access (via Adminer)
- **Server**: mariadb
- **Username**: amine
- **Password**: amine1337
- **Database**: db

## 🛠️ Troubleshooting

### If containers fail to start:
```bash
make clean
make all
```

### If permission errors:
```bash
sudo chown -R $USER:$USER /home/amel-has/data
```

### If domain doesn't resolve:
```bash
# Make sure this line is in /etc/hosts:
127.0.0.1 amel-has.42.fr
```

## 📁 Project Structure
```
inception/
├── Makefile                 # Build automation
├── README.md               # This file
├── srcs/
│   ├── docker-compose.yml  # Services orchestration
│   ├── .env                # Environment variables
│   └── requirements/       # Service configurations
└── data/                   # Persistent volumes (created at runtime)
```

## ✅ Success Indicators
- All containers running: `docker ps` shows 8 containers
- WordPress accessible at https://amel-has.42.fr
- SSL certificate working (self-signed)
- Database connection successful
- Redis cache active
