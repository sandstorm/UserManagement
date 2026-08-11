import { expect } from "@playwright/test";
import { createBdd } from "playwright-bdd";
import FrontendLoginPage from "../helpers/pages/frontendLoginPage.ts";
import { createActivatedUser } from "../helpers/system.ts";

const { Given, When, Then } = createBdd();

Given("an activated user {string} with password {string} exists", async ({}, email: string, password: string) => {
  createActivatedUser(email, password);
});

When("I open the frontend login page", async ({ page }) => {
  await new FrontendLoginPage(page).goto();
});

When("I log in with email {string} and password {string}", async ({ page }, email: string, password: string) => {
  await new FrontendLoginPage(page).login(email, password);
});

When("I log out via the frontend", async ({ page }) => {
  await new FrontendLoginPage(page).logout();
});

Then("I should be logged in", async ({ page }) => {
  await expect(new FrontendLoginPage(page).isLoggedIn()).toBeVisible();
});

Then("I should be logged out", async ({ page }) => {
  await expect(new FrontendLoginPage(page).isShowingLoginForm()).toBeVisible();
});

Then("I should still see the login form", async ({ page }) => {
  await expect(new FrontendLoginPage(page).isShowingLoginForm()).toBeVisible();
});
