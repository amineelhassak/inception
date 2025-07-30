
all :
	@mkdir -p /Users/b/Desktop/incep/data/mariadb
	@mkdir -p /Users/b/Desktop/incep/data/wordpress
	@docker compose -f ./srcs/docker-compose.yml  build --no-cache
	@docker compose -f ./srcs/docker-compose.yml up -d

clean:
	@docker compose -f srcs/docker-compose.yml down -v

fclean: clean
	@docker system prune -af --volumes
	@rm -rf /Users/b/Desktop/incep/data/mariadb/* /Users/b/Desktop/incep/data/wordpress/*

re: fclean all