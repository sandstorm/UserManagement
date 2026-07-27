import { execSync } from "node:child_process";
import { trackEmail } from "./state.ts";

const CONTAINER = `${process.env.SUT || "neos8"}-neos-1`;

/**
 * Creates and directly activates a user via the package's own CLI (Sandstorm.UserManagement's
 * registration/activation flow is its own domain concept, not Flow's generic `user:create`).
 */
export function createActivatedUser(email: string, password: string) {
  execSync(`docker exec -u www-data -w /app ${CONTAINER} bash -c "./flow sandstormuser:create '${email}' '${password}'"`, {
    stdio: "ignore",
  });
  trackEmail(email);
}

export function removeUser(email: string) {
  execSync(`docker exec -u www-data -w /app ${CONTAINER} bash -c "./flow sandstormuser:remove '${email}' || true"`, {
    stdio: "ignore",
  });
}
