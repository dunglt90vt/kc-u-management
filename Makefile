.DEFAULT_GOAL := help
.PHONY: *

-include .env

help:
	@grep -E '^[a-zA-Z_-]+:.*?## .*$$' Makefile | awk 'BEGIN {FS = ":.*?## "}; {printf "\033[36m%-30s\033[0m %s\n", $$1, $$2}'

setup: ## Setup containers
	cp .env.dist .env
	docker compose up --build -d

infra-up: ## Up containers
	docker compose up -d

go-php-shell: ## Go inside php container
	docker compose exec php sh

go-mysql-shell: ## Go inside mysql container
	docker compose exec database sh

go-bun-shell: ## Go inside bun container
	docker compose exec bun sh

generate-jwt-keys: ## Generate jwt keys, run once
	docker compose exec php sh -c "cd web && bin/console lexik:jwt:generate-keypair"

ARGS := $(filter-out $@,$(MAKECMDGOALS))
OBJECT := $(word 2, $(ARGS))
OBJECT_ID := $(word 3, $(ARGS))
EMAIL := $(word 2, $(ARGS))
PASSWORD := $(word 3, $(ARGS))
token =
api-login:
	@curl -X 'POST' \
		'http://localhost/api/login' \
		-H 'accept: application/ld+json' \
		-H 'Content-Type: application/ld+json' \
		-d '{"username": "$(EMAIL)", "password": "$(PASSWORD)"}'

api-get-collection:
	@curl -X 'GET' \
		'http://localhost/api/$(OBJECT)?page=1' \
		-H 'accept: application/ld+json' \
		-H 'Authorization: Bearer ${token}'

api-get-single:
	@curl -X 'GET' \
		'http://localhost/api/$(OBJECT)/$(OBJECT_ID)' \
		-H 'accept: application/ld+json' \
		-H 'Authorization: Bearer ${token}'

# Ignore error when run make with args
%:
	@:
