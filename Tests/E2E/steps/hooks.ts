import { createBdd } from "playwright-bdd";
import { logout, removeAllUsers, removeUser } from "../helpers/system.ts";
import { getTrackedEmails, clearTrackedEmails } from "../helpers/state.ts";

const { AfterScenario } = createBdd();

// cleanup for each scenario
AfterScenario(async ({ page }) => {
  await logout(page);

  removeAllUsers();

  for (const email of getTrackedEmails()) {
    removeUser(email);
  }
  clearTrackedEmails();
});
