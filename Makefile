start:
	symfony server:start -d
	docker-compose up -d
	

stop:
	docker-compose stop
	symfony server:stop