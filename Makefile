up:
	cd cd && docker-compose up -d --build && docker-compose exec webappphp composer install && docker-compose exec webappphp sh cd/webapp/init.sh && docker-compose exec webappphp php artisan migrate:fresh --seed

down:
	cd cd && docker-compose down

status:
	cd cd && docker-compose ps

logs:
	cd cd && docker-compose logs

restart:
	cd cd && docker-compose restart

console:
	cd cd && docker-compose exec --interactive --tty webappphp sh

sniff:
	cd cd && docker-compose exec webappphp php vendor/bin/phpcs /var/www/assignment

beautify-fix:
	cd cd && docker-compose exec webappphp php vendor/bin/phpcbf /var/www/assignment

test:
	cd cd && docker-compose exec webappphp php artisan test
