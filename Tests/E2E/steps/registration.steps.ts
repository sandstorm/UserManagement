import { expect } from "@playwright/test";
import { createBdd } from "playwright-bdd";
import { RegistrationPage, ActivationPage, SuccessOrAlertBanner } from "../helpers/pages.ts";
import { waitForEmailTo, extractLink } from "../helpers/mail.ts";
import { trackEmail } from "../helpers/state.ts";

const { When, Then } = createBdd();

When("I open the registration form", async ({ page }) => {
  await new RegistrationPage(page).goto();
});

When(
  "I register with email {string}, password {string}, first name {string} and last name {string}",
  async ({ page }, email: string, password: string, firstName: string, lastName: string) => {
    await new RegistrationPage(page).register(email, password, password, firstName, lastName);
  },
);

When(
  "I register with email {string}, password {string} and password confirmation {string}, first name {string} and last name {string}",
  async ({ page }, email: string, password: string, passwordConfirmation: string, firstName: string, lastName: string) => {
    await new RegistrationPage(page).register(email, password, passwordConfirmation, firstName, lastName);
  },
);

Then("I should see the registration confirmation", async ({ page }) => {
  await expect(new SuccessOrAlertBanner(page).isSuccess()).toBeVisible();
});

Then("I should still see the registration form", async ({ page }) => {
  await expect(new RegistrationPage(page).isShowingForm()).toBeVisible();
});

When("I open the activation link that was emailed to {string}", async ({ page }, email: string) => {
  const message = await waitForEmailTo(email);
  const link = extractLink(message, /https?:\/\/[^"'\s]*\/account\/activate\/[^"'\s]+/);
  await new ActivationPage(page).open(link);
  trackEmail(email);
});

Then("I should see the account activated", async ({ page }) => {
  await expect(new SuccessOrAlertBanner(page).isSuccess()).toBeVisible();
});

Then("I should see that the activation link is not valid", async ({ page }) => {
  await expect(new SuccessOrAlertBanner(page).isAlert()).toBeVisible();
});

When("I wait for the activation token to expire", async () => {
  await new Promise((resolve) => setTimeout(resolve, 7_000));
});
