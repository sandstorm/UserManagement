NEOS8_COMPOSE = $(CURDIR)/system_under_test/neos8/docker-compose.yaml
E2E_DIR = $(CURDIR)/E2E

.SILENT:
.PHONY: setup setup-sut setup-test \
	generate-bdd-files \
	test test-registration test-login test-reset-password test-profile \
	start-sut log-sut enter-sut \
	sut-down

# COLORS
GREEN  := $(shell tput -Txterm setaf 2)
YELLOW := $(shell tput -Txterm setaf 3)
RESET  := $(shell tput -Txterm sgr0)

# initial setup
setup: setup-sut setup-test

setup-sut:
	docker compose -f $(NEOS8_COMPOSE) build --pull

setup-test:
	echo "${GREEN}Installing test setup.${RESET}"
	cd $(E2E_DIR) && \
	if [ -s "$$NVM_DIR" ]; then \
		. "$$NVM_DIR/nvm.sh" && echo "${GREEN}Found nvm on system -> using it to install nodejs!${RESET}" && nvm install; \
	fi && \
	npm install && npx playwright install --with-deps chromium && \
	echo "" && echo "${GREEN}generate BDD files from feature files${RESET}" && npm run generate-tests

# generate BDD files from feature files
generate-bdd-files:
	echo "${GREEN}generate BDD files from feature files${RESET}"; \
	cd $(E2E_DIR) && npm run generate-tests

## Run all E2E tests
test:
	cd $(E2E_DIR) && npm run test

## Run only the registration/activation scenarios
test-registration:
	cd $(E2E_DIR) && npm run test:registration

## Run only the login/logout scenarios
test-login:
	cd $(E2E_DIR) && npm run test:login

## Run only the forgot/reset-password scenarios
test-reset-password:
	cd $(E2E_DIR) && npm run test:reset-password

## Run only the profile scenarios
test-profile:
	cd $(E2E_DIR) && npm run test:profile

## Start the SUT container
start-sut:
	docker compose -f $(NEOS8_COMPOSE) up -d --build

## Follow logs of the SUT container
log-sut:
	docker compose -f $(NEOS8_COMPOSE) logs -f

## Open a bash shell inside the running SUT container
enter-sut:
	docker compose -f $(NEOS8_COMPOSE) exec neos bash

## Tear down the docker compose environment and remove volumes
sut-down:
	echo "${YELLOW}Shutting down the SUT and removing its volumes.${RESET}"
	docker compose -f $(NEOS8_COMPOSE) down -v
