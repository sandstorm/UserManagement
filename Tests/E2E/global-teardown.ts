import { execSync } from "node:child_process";
import { dirname } from "node:path";

const SUT = process.env.SUT;

export default async function globalTeardown() {
  execSync(`docker compose -f ../system_under_test/${SUT}/docker-compose.yaml down -v`, {
    stdio: "inherit",
    cwd: dirname("."),
  });
}
