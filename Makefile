start:
	symfony server:start -d
	docker-compose up -d
	
stop:
	docker-compose stop
	symfony server:stop

install:
	php bin/console server:start -d
	docker-compose up -d
	php bin/console doctrine:datase:create
	php bin/console doctrine:migrations:migrate
	php bin/console doctrine:fixtures/load
	