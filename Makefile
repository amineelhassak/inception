NAME			= inception
COMPOSE_FILE	= ./srcs/docker-compose.yml
DATA_PATH		= /Users/b/Desktop/incep/data
MARIADB_DATA	= $(DATA_PATH)/mariadb
WORDPRESS_DATA	= $(DATA_PATH)/wordpress
REDIS_DATA		= $(DATA_PATH)/redis

RED		= \033[0;31m
GREEN	= \033[0;32m
YELLOW	= \033[0;33m
BLUE	= \033[0;34m
PURPLE	= \033[0;35m
CYAN	= \033[0;36m
WHITE	= \033[0;37m
RESET	= \033[0m

# ================================== RULES ================================== #

.PHONY: all build up down clean fclean re status logs logs-nginx logs-mariadb logs-wordpress logs-redis restart help

all: build up
	@echo "$(GREEN)✅ $(NAME) is up and running!$(RESET)"

build:
	@echo "$(YELLOW)🔨 Creating data directories...$(RESET)"
	@mkdir -p $(MARIADB_DATA)
	@mkdir -p $(WORDPRESS_DATA)
	@mkdir -p $(REDIS_DATA)
	@echo "$(BLUE)🐳 Building Docker containers...$(RESET)"
	@docker compose -f $(COMPOSE_FILE) build --no-cache

up:
	@echo "$(CYAN)🚀 Starting services...$(RESET)"
	@docker compose -f $(COMPOSE_FILE) up -d
	@echo "$(GREEN)✅ All services are running!$(RESET)"

down:
	@echo "$(YELLOW)🛑 Stopping services...$(RESET)"
	@docker compose -f $(COMPOSE_FILE) down

clean: down
	@echo "$(RED)🧹 Cleaning up containers and volumes...$(RESET)"
	@docker compose -f $(COMPOSE_FILE) down -v

fclean: clean
	@echo "$(RED)💀 Performing deep cleanup...$(RESET)"
	@docker system prune -af --volumes
	@echo "$(RED)🗑️  Removing data directories...$(RESET)"
	@rm -rf $(MARIADB_DATA)/* $(WORDPRESS_DATA)/* $(REDIS_DATA)/*
	@echo "$(GREEN)✅ Deep cleanup completed!$(RESET)"

re: fclean all
	@echo "$(PURPLE)🔄 Rebuild completed!$(RESET)"

# ================================ UTILITIES ================================= #

status:
	@echo "$(CYAN)📊 Docker containers status:$(RESET)"
	@docker compose -f $(COMPOSE_FILE) ps

logs:
	@echo "$(CYAN)📜 Showing logs...$(RESET)"
	@docker compose -f $(COMPOSE_FILE) logs -f

logs-nginx:
	@echo "$(CYAN)📜 Showing nginx logs...$(RESET)"
	@docker compose -f $(COMPOSE_FILE) logs -f nginx

logs-mariadb:
	@echo "$(CYAN)📜 Showing mariadb logs...$(RESET)"
	@docker compose -f $(COMPOSE_FILE) logs -f mariadb

logs-wordpress:
	@echo "$(CYAN)📜 Showing wordpress logs...$(RESET)"
	@docker compose -f $(COMPOSE_FILE) logs -f wordpress

logs-redis:
	@echo "$(CYAN)📜 Showing redis logs...$(RESET)"
	@docker compose -f $(COMPOSE_FILE) logs -f redis

restart:
	@echo "$(YELLOW)🔄 Restarting services...$(RESET)"
	@docker compose -f $(COMPOSE_FILE) restart

help:
	@echo "$(BLUE)📖 Available commands:$(RESET)"
	@echo "$(GREEN)  make all$(RESET)          - Build and start all services"
	@echo "$(GREEN)  make build$(RESET)        - Build Docker containers"
	@echo "$(GREEN)  make up$(RESET)           - Start services"
	@echo "$(GREEN)  make down$(RESET)         - Stop services"
	@echo "$(GREEN)  make clean$(RESET)        - Stop services and remove volumes"
	@echo "$(GREEN)  make fclean$(RESET)       - Deep cleanup (remove everything)"
	@echo "$(GREEN)  make re$(RESET)           - Rebuild everything"
	@echo "$(GREEN)  make status$(RESET)       - Show containers status"
	@echo "$(GREEN)  make logs$(RESET)         - Show all logs"
	@echo "$(GREEN)  make logs-nginx$(RESET)   - Show nginx logs"
	@echo "$(GREEN)  make logs-mariadb$(RESET) - Show mariadb logs"
	@echo "$(GREEN)  make logs-wordpress$(RESET) - Show wordpress logs"
	@echo "$(GREEN)  make logs-redis$(RESET)   - Show redis logs"
	@echo "$(GREEN)  make restart$(RESET)      - Restart all services"
	@echo "$(GREEN)  make help$(RESET)         - Show this help message"