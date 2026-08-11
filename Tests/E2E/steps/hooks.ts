import { createBdd } from "playwright-bdd";
import { logout, removeAllUsers, removeUser } from "../helpers/system.ts";
import { getTrackedEmails, clearTrackedEmails } from "../helpers/state.ts";
import { purgeMailbox } from "../helpers/mail.ts";

const { AfterScenario } = createBdd();

// cleanup for each scenario
AfterScenario(async ({ page }) => {
  await logout(page);

  removeAllUsers();

  for (const email of getTrackedEmails()) {
    removeUser(email);
  }
  clearTrackedEmails();

  // so a later scenario's waitForEmailTo search can't pick up a stale message from this run
  await purgeMailbox();
});
