import { createBdd } from "playwright-bdd";
import { removeUser } from "../helpers/system.ts";
import { purgeMailbox } from "../helpers/mail.ts";
import { getTrackedEmails, clearTrackedEmails } from "../helpers/state.ts";

const { AfterScenario } = createBdd();

// cleanup for each scenario: remove every user created (via fixture or through the registration
// UI) during the scenario, and clear Mailpit so the next scenario's mail search doesn't pick up
// a stale message.
AfterScenario(async () => {
  for (const email of getTrackedEmails()) {
    removeUser(email);
  }
  clearTrackedEmails();

  await purgeMailbox();
});
