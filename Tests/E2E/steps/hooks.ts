import { createBdd } from "playwright-bdd";
import { logout, removeAllUsers } from "../helpers/system.ts";

const { AfterScenario } = createBdd();

// cleanup for each scenario
AfterScenario(async ({ page }) => {
  await logout(page);

  removeAllUsers();
});
