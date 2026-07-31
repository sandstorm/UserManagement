# E2E Tests

End-to-end tests for `sandstorm/usermanagement`, using [Playwright](https://playwright.dev) with [playwright-bdd](https://vitalets.github.io/playwright-bdd/) for Gherkin-style BDD scenarios. Tests run against a Dockerised Neos instance (the *system under test*, SUT) — no local Neos installation required.

Tests are executed against both **Neos 8** (PHP 8.2, MariaDB 10.11) and **Neos 9** (PHP 8.5, MariaDB 11.4).

## Prerequisites

- Docker
- Node.js (the version pinned in `.nvmrc`) — or [nvm](https://github.com/nvm-sh/nvm), which the setup script uses automatically if available
- make

## Setup

Run once after cloning:

```bash
cd Tests/E2E
make setup
```

This will:
1. Build the Docker images for both Neos 8 and Neos 9 (`make setup-sut`)
2. Install the pinned Node.js version via nvm (if nvm is available)
3. Install npm dependencies and the Playwright Chromium browser
4. Generate the Playwright test files from the Gherkin feature files

All `make` targets are run from `Tests/E2E`. Running `make` without a target prints the list of available targets.

> `.npmrc` sets `min-release-age = 7` days, so `npm install` ignores package versions published within the last week. This is a deliberate supply-chain safeguard — remove it only if you know what you are doing.

## Running tests

```bash
# Run against both Neos 8 and Neos 9
make test

# Run against Neos 8 only
make test-neos8

# Run against Neos 9 only
make test-neos9
```

Playwright starts the Docker containers automatically before each run and stops them afterwards (see `global-teardown.ts`). The first run may take a few minutes while Neos sets itself up inside the container (migrations, demo site import).

### How the SUT works

- `system_under_test/Dockerfile` builds a [FrankenPHP](https://frankenphp.dev) image and installs a `neos/neos-base-distribution` matching the target Neos major version.
- Docker Compose mounts this package's `Classes/`, `Configuration/`, `Migrations/`, `Resources/` and `composer.json` into the container at `/app/DistributionPackages/Sandstorm.Usermanagement`, so your local changes are picked up without rebuilding the image.
- The entrypoint registers that directory as a Composer path repository, requires `sandstorm/usermanagement:@dev`, waits for the database, runs `doctrine:migrate`, imports the `Neos.Demo` site and serves the instance on <http://localhost:8081> — which is Playwright's `baseURL`.

### SUT and FLOW_CONTEXT

Each npm test script sets two environment variables:

- **`SUT`** (`neos8` or `neos9`) — selects which Docker Compose environment to start. It is also used to derive the container name (`$SUT-neos-1`) for the Flow CLI helpers in `helpers/system.ts`.
- **`FLOW_CONTEXT`** — selects a Neos Flow configuration context. Both scripts default to `Production/E2E-SUT`, which loads the configuration files in `system_under_test/sut_file_system_overrides/app/Configuration/Production/E2E-SUT/`.

To test a different application configuration (e.g. with or without a feature enabled), add configuration files under a sub-context such as `Production/E2E-SUT/my-variant/` and add a matching npm script in `package.json` that sets `FLOW_CONTEXT=Production/E2E-SUT/my-variant`.

## Container management

When you need to inspect a running container or debug a failure:

```bash
# Start containers in the background (without running tests)
make start-sut-neos8
make start-sut-neos9

# Stream container logs
make log-sut-neos8
make log-sut-neos9

# Open a bash shell inside a running container
make enter-sut-neos8
make enter-sut-neos9

# Stop all containers and delete their volumes
make sut-down
```

Neos 8 and Neos 9 use separate volumes and networks, but both publish the same host ports (`8081` for Neos, `13306` for MariaDB) — so only one SUT can run at a time. `make test` therefore runs them one after another.

## Continuous integration

`.github/workflows/e2e.yml` runs the suite on pushes and pull requests against `main`, as a matrix over `neos8` and `neos9`. The Playwright HTML report is uploaded as a build artifact (`playwright-report-<neos version>`) for every run, including failures.

## Directory structure

```
Tests/E2E/
├── Makefile                             # Run `make` for a list of targets
├── README.md                            # this file
├── features/                            # Gherkin feature files (.feature)
│   └── login.feature
├── steps/                               # TypeScript step definitions
│   ├── login.steps.ts
│   └── hooks.ts                         # AfterScenario cleanup
├── helpers/
│   ├── pages/                           # Page Object Model classes
│   │   ├── loginPage.ts
│   │   └── contentPage.ts
│   └── system.ts                        # Flow CLI utilities (via docker exec)
├── playwright.config.ts
├── global-teardown.ts                   # shuts the SUT down after a run
├── package.json
├── tsconfig.json
├── .nvmrc                               # pinned Node.js version
├── .npmrc
├── .prettierrc.yaml
└── system_under_test/
    ├── Dockerfile
    ├── sut-base-docker-compose.yaml     # shared compose base (neos, db, redis)
    ├── neos8/
    │   ├── docker-compose.yaml          # includes the base + the overrides below
    │   ├── compose-overrides-neos8.yaml # PHP 8.2, Neos 8, own volume/network
    │   └── entrypoint.sh
    ├── neos9/
    │   ├── docker-compose.yaml
    │   ├── compose-overrides-neos9.yaml # PHP 8.5, Neos 9, MariaDB 11.4
    │   └── entrypoint.sh
    └── sut_file_system_overrides/       # Neos/Caddy config baked into the image
```

---

## Writing new tests

Tests are written in two parts: a **feature file** (what to test, in plain language) and a **steps file** (how to do it, in TypeScript).

### 0. IDE Goodies

Syntax highlighting, "Go to Definition" from feature files to step implementations, and other IDE features are available with the right setup:

#### VSCode
1. Install the official Cucumber extension (`CucumberOpen.cucumber-official`) for syntax highlighting and step definition navigation.
2. Add the path "./steps/**/*.ts" to the extension's `glue` configuration to enable "Go to Definition" from feature files to step implementations.
   ```
   {
       "cucumber.glue": [
           "steps/**/*.steps.ts",
           ...
       ]
   }
   ```

#### JetBrains IDEs
1. Install the "Gherkin" plugin by JetBrains.
2. Install the "Cucumber.js" plugin by JetBrains for step definition navigation.
3. Configure the `.features-gen` to be "Excluded" in the project structure to avoid cluttering the navigation context menu with auto-generated files.

### 1. Write a feature file

Create a `.feature` file under `features/`. For larger suites, organise by feature area in sub-directories:

```gherkin
# features/my-feature/my-scenario.feature
@default-context
Feature: My feature description

  Background:
    Given A user with username "admin", password "password" and role "Neos.Neos:Administrator" exists

  Scenario: Admin can do the thing
    When I log in with username "admin" and password "password"
    And I navigate to the thing
    Then I should see the expected result
```

Tags (like `@default-context` above) are optional documentation by default, but they can be used to select scenarios via `npx playwright test --grep @default-context` — handy when a group of scenarios only makes sense for a specific `FLOW_CONTEXT`.

### 2. Implement missing steps

Reuse existing steps from `steps/` where possible. `login.steps.ts` already provides:

- `Given A user with username {string}, password {string} and role {string} exists`
- `When I log in with username {string} and password {string}`
- `When I log out`
- `Then I should see the Neos content page`
- `Then I cannot access the Neos content page`

If a step doesn't exist yet, add it to a new or existing steps file:

```typescript
// steps/my-feature.steps.ts
import { expect } from "@playwright/test";
import { createBdd } from "playwright-bdd";

const { Given, When, Then } = createBdd();

When("I navigate to the thing", async ({ page }) => {
  await page.goto("/my-path");
});

Then("I should see the expected result", async ({ page }) => {
  await expect(page.locator(".my-selector")).toBeVisible();
});
```

Steps are matched by exact string (including `{string}` parameters). A step defined in any file under `steps/` is available in all feature files.

### 3. Add Page Objects for new pages

If you are testing a new page, add a class under `helpers/pages/`, following the existing `loginPage.ts` / `contentPage.ts`:

```typescript
// helpers/pages/myFeaturePage.ts
import type { Page } from "@playwright/test";

export default class MyFeaturePage {
  constructor(private readonly page: Page) {}

  async goto() {
    await this.page.goto("/my-path");
  }

  async clickTheButton() {
    await this.page.locator(".my-button").click();
  }
}
```

Keep selectors inside the page object and out of the steps — that way a UI change only has to be fixed in one place.

### 4. Regenerate test files

playwright-bdd generates Playwright test files (into `.features-gen/`) from your feature files. After adding or changing feature files run:

```bash
make generate-bdd-files
```

This is done automatically by `make setup` and by every `make test*` target, but you can run it manually during development.

### 5. Use Flow CLI in steps

`helpers/system.ts` exposes utilities that run Neos Flow CLI commands inside the Docker container of the currently selected `SUT`:

```typescript
import { createUser, removeAllUsers, logout } from "../helpers/system.ts";

// Create a Flow user (synchronous — runs docker exec)
createUser("myuser", "password", ["Neos.Neos:Administrator"]);

// Remove all users (used in the AfterScenario hook for cleanup)
removeAllUsers();

// End the current browser session
await logout(page);
```

You can add more Flow CLI wrappers to `system.ts` following the same pattern.

### Cleanup

The `AfterScenario` hook in `steps/hooks.ts` logs out the current browser session and removes all users after every scenario, keeping tests isolated. If your tests create other persistent data, add cleanup logic there.

## Disclaimer

This is just a template. It is meant to jump start your own E2E test suite.

You can use all the playwright features you want (like `--ui`, `--debug`, `--grep`, etc.) — the Makefile targets are just thin wrappers around `npx playwright test` that set up the environment variables and Docker containers for you. Feel free to modify the setup as needed.
