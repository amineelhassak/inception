#!/bin/bash

# Inception Project Setup Script
# This script prepares the project for deployment on any Linux machine

# Colors for output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[0;33m'
BLUE='\033[0;34m'
CYAN='\033[0;36m'
RESET='\033[0m'

echo -e "${BLUE}🚀 Inception Project Setup${RESET}"
echo -e "${CYAN}Setting up your Docker infrastructure...${RESET}"

# Get current user
CURRENT_USER=$(whoami)
echo -e "${YELLOW}Current user: ${CURRENT_USER}${RESET}"

# Update paths in Makefile
echo -e "${YELLOW}📝 Updating Makefile for user ${CURRENT_USER}...${RESET}"
sed -i "s|DATA_PATH.*=.*|DATA_PATH = /home/${CURRENT_USER}/data|g" Makefile

# Update paths in docker-compose.yml
echo -e "${YELLOW}📝 Updating docker-compose.yml for user ${CURRENT_USER}...${RESET}"
sed -i "s|device: /home/.*/data/|device: /home/${CURRENT_USER}/data/|g" srcs/docker-compose.yml

# Create data directories
echo -e "${YELLOW}📁 Creating data directories...${RESET}"
mkdir -p /home/${CURRENT_USER}/data/mariadb
mkdir -p /home/${CURRENT_USER}/data/wordpress

# Add domain to /etc/hosts if not already present
if ! grep -q "amel-has.42.fr" /etc/hosts; then
    echo -e "${YELLOW}🌐 Adding domain to /etc/hosts...${RESET}"
    echo "127.0.0.1 amel-has.42.fr" | sudo tee -a /etc/hosts
else
    echo -e "${GREEN}✅ Domain already in /etc/hosts${RESET}"
fi

# Check if Docker is installed
if ! command -v docker &> /dev/null; then
    echo -e "${RED}❌ Docker is not installed!${RESET}"
    echo -e "${YELLOW}Please install Docker first:${RESET}"
    echo "curl -fsSL https://get.docker.com -o get-docker.sh"
    echo "sudo sh get-docker.sh"
    echo "sudo usermod -aG docker $USER"
    exit 1
fi

# Check if Docker Compose is installed
if ! command -v docker-compose &> /dev/null && ! docker compose version &> /dev/null; then
    echo -e "${RED}❌ Docker Compose is not installed!${RESET}"
    echo -e "${YELLOW}Please install Docker Compose first.${RESET}"
    exit 1
fi

echo -e "${GREEN}✅ Setup completed successfully!${RESET}"
echo -e "${CYAN}🎯 Your project is now ready to run on this machine.${RESET}"
echo ""
echo -e "${BLUE}📖 Next steps:${RESET}"
echo -e "${GREEN}  1. Run: make all${RESET}"
echo -e "${GREEN}  2. Access: https://amel-has.42.fr${RESET}"
echo ""
echo -e "${YELLOW}💡 Available commands:${RESET}"
echo -e "${GREEN}  make all      ${RESET} - Build and start all services"
echo -e "${GREEN}  make logs     ${RESET} - View logs"
echo -e "${GREEN}  make status   ${RESET} - Check status"
echo -e "${GREEN}  make clean    ${RESET} - Stop and cleanup"
echo -e "${GREEN}  make help     ${RESET} - Show all commands"
