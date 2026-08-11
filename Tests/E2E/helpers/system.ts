import { execSync } from "node:child_process";
import { dirname } from "node:path";
import type { Page } from "@playwright/test";
import { trackEmail } from "./state.ts";

const CONTAINER = `${process.env.SUT || "neos8"}-neos-1`;

export function createUser(name: string, password: string, roles: string[]) {
  execSync(
    `docker exec -u www-data -w /app ${CONTAINER} bash -c "./flow user:create ${name} ${password} Test${name} User${name} --roles ${roles.join(",")}"`,
    { stdio: "ignore", cwd: dirname(".") },
  );
}

export function removeAllUsers() {
  // `|| true`: exits non-zero when there's nothing to delete, which would otherwise abort the
  // rest of the AfterScenario cleanup (e.g. scenarios that only create frontend/sandstorm users).
  execSync(`docker exec -u www-data -w /app ${CONTAINER} bash -c "./flow user:delete --assume-yes '*' || true"`, {
    stdio: "ignore",
    cwd: dirname("."),
  });
}

export async function logout(page: Page) {
  await page.context().request.post("/neos/logout");
}

export function createActivatedUser(email: string, password: string) {
  execSync(`docker exec -u www-data -w /app ${CONTAINER} bash -c "./flow sandstormuser:create '${email}' '${password}'"`, {
    stdio: "ignore",
    cwd: dirname("."),
  });
  trackEmail(email);
}

export function removeUser(email: string) {
  execSync(`docker exec -u www-data -w /app ${CONTAINER} bash -c "./flow sandstormuser:remove '${email}' || true"`, {
    stdio: "ignore",
    cwd: dirname("."),
  });
}
