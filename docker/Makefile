# Filesender build system
#
# Usage
# make cleanall 		- Clean build artifacts and docker images
# make rebuild			- Rebuild the docker image
# make no-cache-rebuild	- Rebuild the docker image with the --no-cache option
# make stop-docker		- Just stop the docker containers
# make certificate		- Create a new IdP certificate and update saml20-idp-remote.php.dist
#

CONTAINER_NAME_FS=filesender
CONTAINER_NAME_FSDB=filesender-db
CERT_DATA_COMMAND=$(shell awk '/-----BEGIN CERTIFICATE-----/{flag=1;next}/-----END CERTIFICATE-----/{flag=0}flag' assets/config/simplesamlphp/cert/localhost.crt | tr -d '\n')

.PHONY: firstrun
firstrun: stop-docker certificate
	cp .env.example .env
	. ./.env; \
	echo "Making Filesender $${FILESENDER_VERSION} with SimpleSAMLphp $${SSP_VERSION}"; \
	docker build -t filesender-local/filesender:$${FILESENDER_VERSION} . --progress plain --build-arg FILESENDER_VERSION=$${FILESENDER_VERSION} --build-arg SSP_VERSION=$${SSP_VERSION}; \
	docker compose up -d
	sleep 15
	docker exec -ti filesender php /opt/filesender/filesender/scripts/upgrade/database.php
	docker compose down
	docker compose up -d

.PHONY: rebuild
rebuild: stop-docker
	. ./.env; \
	echo "Making Filesender $${FILESENDER_VERSION} with SimpleSAMLphp $${SSP_VERSION}"; \
	docker build -t filesender-local/filesender:$${FILESENDER_VERSION} . --progress plain --build-arg FILESENDER_VERSION=$${FILESENDER_VERSION} --build-arg SSP_VERSION=$${SSP_VERSION}; \
	docker compose up -d

.PHONY: no-cache-rebuild
no-cache-rebuild: stop-docker
	. ./.env; \
	echo "Making Filesender $${FILESENDER_VERSION} with SimpleSAMLphp $${SSP_VERSION}"; \
	docker build -t filesender-local/filesender:$${FILESENDER_VERSION} . --progress plain --build-arg FILESENDER_VERSION=$${FILESENDER_VERSION} --build-arg SSP_VERSION=$${SSP_VERSION} --no-cache; \
	docker compose up -d

.PHONY: stop-docker
stop-docker:
	docker compose down
	@if [ "$$(docker ps --filter "name=^/${CONTAINER_NAME_FS}$$" --format "{{.Names}}")" = "$(CONTAINER_NAME_FS)" ]; then \
		echo "$(CONTAINER_NAME_FS) is still running, stopping manually."; \
		docker stop $(CONTAINER_NAME_FS); \
	fi

	@if [ "$$(docker ps --filter "name=^/${CONTAINER_NAME_FSDB}$$" --format "{{.Names}}")" = "$(CONTAINER_NAME_FSDB)" ]; then \
	    echo "$(CONTAINER_NAME_FSDB) is still running, stopping manually."; \
		docker stop $(CONTAINER_NAME_FSDB); \
	fi

.PHONY: certificate
certificate:
	-rm assets/config/simplesamlphp/cert/localhost.*
	-rm assets/config/simplesamlphp/metadata/saml20-idp-remote.php.dist
	mkdir -p assets/config/simplesamlphp/cert/
	cp assets/config/simplesamlphp/metadata/saml20-idp-remote.php.dist.template assets/config/simplesamlphp/metadata/saml20-idp-remote.php.dist
	openssl req -newkey rsa:3072 -new -x509 -days 3652 -nodes -out assets/config/simplesamlphp/cert/localhost.crt -keyout assets/config/simplesamlphp/cert/localhost.pem -subj "/C=XX/ST=StateName/L=CityName/O=CompanyName/OU=CompanySectionName/CN=CommonNameOrHostname"
	{ \
	  CERT_DATA=$$(awk '/-----BEGIN CERTIFICATE-----/{flag=1;next}/-----END CERTIFICATE-----/{flag=0}flag' assets/config/simplesamlphp/cert/localhost.crt | tr -d '\n'); \
	  sed -i "s|GENERATED_CERT_DATA|$${CERT_DATA}|g" assets/config/simplesamlphp/metadata/saml20-idp-remote.php.dist; \
	}

.PHONY: cleanall
cleanall: stop-docker
	echo Prune system and voumes and remove containers, images and volumes in 5s. Press Ctrl-C to cancel.
	# for i in {5..1}; do \
	#     echo $$i & sleep 1; \
	# done
	-docker rm -vf $$(docker ps -aq)
	-docker rmi -f $$(docker images -aq)
	docker system prune -f
	docker volume prune -f
	-rm .env

	rm -rf assets/config/simplesamlphp/cert/
	mkdir -p assets/config/simplesamlphp/cert/
	rm -rf mysql_data
	mkdir -p mysql_data
