start:
	symfony server:start -d
	docker-compose up -d
	
stop:
	docker-compose stop
	symfony server:stop

install:
	symfony server:start -d
	docker-compose up -d
	symfony doctrine:datase:create
	symfony doctrine:migrations:migrate
	symfony doctrine:fixtures/load
	