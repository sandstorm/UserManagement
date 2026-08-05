import { execSync } from "node:child_process";
import { dirname } from "node:path";
import type { Page } from "@playwright/test";

const CONTAINER = `${process.env.SUT || "neos8"}-neos-1`;

export function createUser(name: string, password: string, roles: string[]) {
  execSync(
    `docker exec -u www-data -w /app ${CONTAINER} bash -c "./flow user:create ${name} ${password} Test${name} User${name} --roles ${roles.join(",")}"`,
    { stdio: "ignore", cwd: dirname(".") },
  );
}

export function removeAllUsers() {
  execSync(`docker exec -u www-data -w /app ${CONTAINER} bash -c "./flow user:delete --assume-yes '*'"`, {
    stdio: "ignore",
    cwd: dirname("."),
  });
}

export async function logout(page: Page) {
  await page.context().request.post("/neos/logout");
}
