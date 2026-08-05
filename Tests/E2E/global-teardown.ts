import { execSync } from "node:child_process";
import { dirname } from "node:path";

const SUT = process.env.SUT;

/**
 * This is run after all tests have finished.
 * Because we use docker containers, we only have to shut them down.
 */
export default async function globalTeardown() {
  execSync(`docker compose -f ./system_under_test/${SUT}/docker-compose.yaml down -v`, {
    stdio: "inherit",
    cwd: dirname("."),
  });
}
