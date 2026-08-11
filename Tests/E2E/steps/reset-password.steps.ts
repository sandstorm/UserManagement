import { expect } from "@playwright/test";
import { createBdd } from "playwright-bdd";
import ResetPasswordPage from "../helpers/pages/resetPasswordPage.ts";
import { waitForEmailTo, extractLink } from "../helpers/mail.ts";

const { When, Then } = createBdd();

When("I request a password reset for {string}", async ({ page }, email: string) => {
  const resetPasswordPage = new ResetPasswordPage(page);
  await resetPasswordPage.goto();
  await resetPasswordPage.requestReset(email);
});

Then("I should see the password reset confirmation", async ({ page }) => {
  await expect(new ResetPasswordPage(page).isShowingSuccess()).toBeVisible();
});

When("I open the password reset link that was emailed to {string}", async ({ page }, email: string) => {
  const message = await waitForEmailTo(email);
  const link = extractLink(message, /https?:\/\/[^"'\s]*\/account\/resetpassword\/[^"'\s]+/);
  await new ResetPasswordPage(page).open(link);
});

When("I set a new password {string}", async ({ page }, password: string) => {
  await new ResetPasswordPage(page).setNewPassword(password);
});

Then("I should see the password was updated", async ({ page }) => {
  await expect(new ResetPasswordPage(page).isShowingSuccess()).toBeVisible();
});

Then("I should see that the reset link is not valid", async ({ page }) => {
  await expect(new ResetPasswordPage(page).isShowingError()).toBeVisible();
});

When("I wait for the reset token to expire", async () => {
  await new Promise((resolve) => setTimeout(resolve, 7_000));
});
