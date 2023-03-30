up:
	cd cd && docker-compose up -d --build && docker-compose exec php composer install

down:
	cd cd && docker-compose down

status:
	cd cd && docker-compose ps

logs:
	cd cd && docker-compose logs

restart:
	cd cd && docker-compose restart

console:
	cd cd && docker-compose exec --interactive --tty php /bin/sh
