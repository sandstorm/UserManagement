NEOS8_COMPOSE = $(CURDIR)/system_under_test/neos8/docker-compose.yaml

.SILENT:
.PHONY: help \
	setup setup-sut setup-test \
	generate-bdd-files \
	test test-neos8 \
	start-sut-neos8 \
	log-sut-neos8 \
	enter-sut-neos8 \
	sut-down

# COLORS
GREEN  := $(shell tput -Txterm setaf 2)
YELLOW := $(shell tput -Txterm setaf 3)
RESET  := $(shell tput -Txterm sgr0)

# running `make` without a target shows the help
.DEFAULT_GOAL := help

## Show this help
help:
	echo ""
	echo "Usage: $(YELLOW)make <target>$(RESET)"
	awk -v green="$(GREEN)" -v yellow="$(YELLOW)" -v reset="$(RESET)" ' \
		/^##@ / { printf "\n%s%s%s\n", green, substr($$0, 5), reset; next } \
		/^## / { doc = substr($$0, 4); next } \
		/^[a-zA-Z0-9_-]+:/ { if (doc != "") { sub(":$$", "", $$1); printf "  %s%-20s%s %s\n", yellow, $$1, reset, doc; doc = "" } } \
	' $(MAKEFILE_LIST)
	echo ""

##@ Setup

## Build SUT images and install the test setup
setup: setup-sut setup-test

## Build the SUT docker images
setup-sut:
	docker compose -f $(NEOS8_COMPOSE) build --pull

## Install nodejs, npm dependencies and playwright browsers
setup-test:
	echo "${GREEN}Installing test setup.${RESET}"
	if [ -s "$$NVM_DIR" ]; then \
		. "$$NVM_DIR/nvm.sh" && echo "${GREEN}Found nvm on system -> using it to install nodejs!${RESET}" && nvm install; \
	fi && \
	npm install && npx playwright install --with-deps chromium && \
	echo "" && echo "${GREEN}generate BDD files from feature files${RESET}" && npm run generate-tests

##@ Tests

## Generate BDD files from feature files
generate-bdd-files:
	echo "${GREEN}generate BDD files from feature files${RESET}"
	npm run generate-tests

## Run all E2E tests
test: test-neos8

## Run all Neos 8 E2E tests
test-neos8:
	npm run test:neos8

##@ System under test (SUT)

## Start the Neos 8 SUT containers
start-sut-neos8:
	docker compose -f $(NEOS8_COMPOSE) up -d --build

## Show Neos 8 SUT container logs
log-sut-neos8:
	docker compose -f $(NEOS8_COMPOSE) logs -f

## Enter Neos 8 SUT
enter-sut-neos8:
	docker compose -f $(NEOS8_COMPOSE) exec neos bash

## Tear down the docker compose environment and remove volumes
sut-down:
	echo "${YELLOW}Shutting down the SUT and removing its volumes.${RESET}"
	docker compose -f $(NEOS8_COMPOSE) down -v
