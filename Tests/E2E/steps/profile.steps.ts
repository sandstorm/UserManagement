import { expect } from "@playwright/test";
import { createBdd } from "playwright-bdd";
import { ProfilePage } from "../helpers/pages.ts";

const { When, Then } = createBdd();

When("I open the profile page", async ({ page }) => {
  await new ProfilePage(page).goto();
});

When("I set a new profile password {string}", async ({ page }, password: string) => {
  await new ProfilePage(page).setNewPassword(password);
});

Then("I should be back on the profile page", async ({ page }) => {
  await expect(page).toHaveURL(/\/profile$/);
});
