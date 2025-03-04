.DEFAULT_GOAL := help

.PHONY: up
up: ## Start application
	@docker compose up -d

stop: ## Stop application
	@docker compose stop

.PHONY: shell
shell: ## Start a shell inside the container
	@docker compose exec php bash

.PHONY: help
help:
	@grep -E '^[a-zA-Z_-]+:.*?## .*$$' $(MAKEFILE_LIST) | sort | awk 'BEGIN {FS = ":.*?## "}; {printf "\033[36m%-10s\033[0m %s\n", $$1, $$2}'
