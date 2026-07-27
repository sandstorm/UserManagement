# E2E Tests

End-to-end tests for `sandstorm/usermanagement`, using [Playwright](https://playwright.dev) with [playwright-bdd](https://vitalets.github.io/playwright-bdd/) for Gherkin-style BDD scenarios. Tests run against a Dockerised Neos instance (the *system under test*, SUT) — no local Neos installation required.

This branch (`7.0`) targets **Neos 8** (PHP 8.2, MariaDB 10.11). The `main` branch has an equivalent suite targeting Neos 9.

Outgoing mail (account activation / password reset links) is captured by [Mailpit](https://mailpit.axllent.org/) instead of being sent for real; tests read the emails via Mailpit's REST API.

## Prerequisites

- Docker
- Node.js >= 24 (or [nvm](https://github.com/nvm-sh/nvm) — the setup script uses it automatically if available)
- make

## Setup

Run once after cloning:

```bash
cd Tests
make setup
```

This will:
1. Build the Docker image for Neos 8
2. Install npm dependencies
3. Install the Playwright Chromium browser
4. Generate the Playwright test files from the Gherkin feature files

## Running tests

```bash
# Run all scenarios
make test

# Run one feature group at a time
make test-registration
make test-login
make test-reset-password
make test-profile
```

Playwright starts the Docker containers automatically before each run and stops them afterwards (`globalTeardown` runs `docker compose down -v`). The first run may take a few minutes while Neos sets itself up inside the container (migrations, demo site import).

**Rebuild after changing `system_under_test/`:** everything under `system_under_test/sut_file_system_overrides/` (Settings.yaml, Routes.yaml, ...) and the `Dockerfile`/`compose-overrides-neos8.yaml` files are baked into the image at **build time**, not bind-mounted. `make test*` only runs `docker compose up`, which reuses whatever image was last built — it will *not* pick up such changes on its own. After editing anything there, rebuild explicitly first:

```bash
make sut-down     # tear down any previous container/volumes
make setup-sut    # docker compose build --pull
make test         # or make test-<area>
```

(`Classes/`, `Configuration/`, `Migrations/`, `Resources/` and `composer.json` *are* bind-mounted read/write into the container, so changes to the package's own code/config are picked up on the next container start without a rebuild.)

### SUT and FLOW_CONTEXT

Each npm test script sets two environment variables:

- **`SUT`** — selects which Docker Compose environment to start (fixed to `neos8` on this branch).
- **`FLOW_CONTEXT`** — selects a Neos Flow configuration context, always `Production/E2E-SUT` here, which loads the configuration files in `system_under_test/sut_file_system_overrides/app/Configuration/Production/E2E-SUT/`. That override also adds `/profile`, `/profile/edit` and `/profile/password` routes for `ProfileController` — the package itself only exposes Profile through a Neos Fusion plugin, which the E2E app doesn't otherwise embed anywhere.

## Container management

When you need to inspect a running container or debug a failure:

```bash
# Start the container in the background (without running tests)
make start-sut

# Stream container logs
make log-sut

# Open a bash shell inside a running container
make enter-sut

# Stop the container and delete its volumes
make sut-down
```

Mailpit's web UI is available at http://localhost:8025 while the SUT is running — useful to inspect activation/reset emails by hand.

## Troubleshooting

`make test*` tears the SUT down again once Playwright finishes, so for manual poking start it standalone first:

```bash
make start-sut   # boots the container in the background and leaves it running
```

Then, from `Tests/`:

- **Create a user to log in with manually** (there's no default one):
  ```bash
  docker compose -f system_under_test/neos8/docker-compose.yaml exec neos \
    bash -c "./flow sandstormuser:create 'someone@example.com' 'Sup3rSecret!1'"
  ```
  Log in at http://localhost:8081/login.
- **List/check routes** — useful when a redirect or a form's `action` URL doesn't do what you expect:
  ```bash
  docker compose -f system_under_test/neos8/docker-compose.yaml exec neos bash -c "./flow routing:list"
  docker compose -f system_under_test/neos8/docker-compose.yaml exec neos \
    bash -c "./flow routing:resolve Sandstorm.UserManagement --controller Profile --action index"
  ```
  `routing:resolve` simulates exactly what `$this->redirect(...)` does internally — if it says "No route could resolve these values", a plain page load will 404 the same way. This is how we found that a route's static defaults (like the `__show*` plugin-argument ones on the `profile` route) must exactly match whatever a redirect call provides, or the redirect fails to resolve entirely.
- **Check what actually happened server-side** for a 404/error that doesn't show a stack trace in the browser:
  ```bash
  docker compose -f system_under_test/neos8/docker-compose.yaml exec neos bash -c "ls -t Data/Logs/Exceptions/ | head -3"   # uncaught exceptions
  docker compose -f system_under_test/neos8/docker-compose.yaml exec neos bash -c "grep -i -A15 'Could not resolve' Data/Logs/*.log"  # router warnings (no exception thrown, so not in Exceptions/)
  ```

## Directory structure

```
Tests/
├── Makefile
├── README.md                        # this file
├── E2E/
│   ├── features/                    # Gherkin feature files (.feature), one folder per area
│   │   ├── registration/
│   │   ├── login/
│   │   ├── reset-password/
│   │   └── profile/
│   ├── steps/                       # TypeScript step definitions
│   ├── helpers/
│   │   ├── pages.ts                 # Page Object Model classes
│   │   ├── system.ts                # Docker/Flow CLI utilities (package's own sandstormuser:* commands)
│   │   ├── mail.ts                  # Mailpit REST client
│   │   └── state.ts                 # tracks emails created during a scenario, for cleanup
│   ├── playwright.config.ts
│   ├── global-teardown.ts
│   ├── package.json
│   └── tsconfig.json
└── system_under_test/
    ├── Dockerfile
    ├── sut-base-docker-compose.yaml # shared services: neos app, MariaDB, Redis, Mailpit
    ├── neos8/
    │   ├── docker-compose.yaml
    │   └── entrypoint.sh
    └── sut_file_system_overrides/   # Neos/PHP/Caddy config mounted into the container
```

---

## Writing new tests

Tests are written in two parts: a **feature file** (what to test, in plain language) and a **steps file** (how to do it, in TypeScript).

### 1. Write a feature file

Create a `.feature` file under `E2E/features/<area>/`. Tag it with the area (`@registration`, `@login`, `@reset-password`, `@profile`) so it can be run as its own npm script / CI step.

### 2. Implement missing steps

Reuse existing steps from `steps/` where possible (a step defined in any file is available in all feature files). Add new ones to a new or existing steps file.

### 3. Add Page Objects for new pages

Add a class to `helpers/pages.ts` if you're testing a page that doesn't have one yet.

### 4. Regenerate test files

playwright-bdd generates Playwright test files from your feature files. After adding or changing feature files run:

```bash
make generate-bdd-files
```

This is done automatically by `make setup` and every `make test*` target, but you can run it manually during development.

### 5. Use Flow CLI in steps

`helpers/system.ts` exposes utilities that run the package's own CLI commands (`Classes/Command/SandstormUserCommandController`) inside the Docker container, e.g. `createActivatedUser(email, password)` (registers and immediately activates a user via `./flow sandstormuser:create`). Emails created this way — and emails opened via an activation link in a test — are tracked in `helpers/state.ts` and removed again in the `AfterScenario` hook (`steps/hooks.ts`), since the package has no bulk-delete command.

### 6. Reading emails

`helpers/mail.ts` polls Mailpit's REST API for a message to a given recipient and extracts a link from it via a regex, e.g.:

```typescript
const message = await waitForEmailTo("someone@example.com");
const link = extractLink(message, /https?:\/\/[^"'\s]*\/account\/activate\/[^"'\s]+/);
await page.goto(link);
```

The `AfterScenario` hook also purges Mailpit's mailbox after every scenario so a later scenario's mail search can't pick up a stale message.

## Disclaimer

This suite was bootstrapped with [Sandstorm.NeosInitE2ETestsPlugin](https://github.com/sandstorm/Sandstorm.NeosInitE2ETestsPlugin) and then adapted to this package's own controllers/routes. Feel free to modify the setup as needed — you can use all the usual Playwright features (`--ui`, `--debug`, `--grep`, etc.), the Makefile targets are just thin wrappers around `npx playwright test`.
