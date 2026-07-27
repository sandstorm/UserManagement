import { expect } from "@playwright/test";
import { createBdd } from "playwright-bdd";
import { LoginPage } from "../helpers/pages.ts";
import { createActivatedUser } from "../helpers/system.ts";

const { Given, When, Then } = createBdd();

Given("an activated user {string} with password {string} exists", async ({}, email: string, password: string) => {
  createActivatedUser(email, password);
});

When("I open the login page", async ({ page }) => {
  await new LoginPage(page).goto();
});

When("I log in with email {string} and password {string}", async ({ page }, email: string, password: string) => {
  await new LoginPage(page).login(email, password);
});

When("I log out", async ({ page }) => {
  await new LoginPage(page).logout();
});

Then("I should be logged in", async ({ page }) => {
  await expect(new LoginPage(page).isLoggedIn()).toBeVisible();
});

Then("I should be logged out", async ({ page }) => {
  await expect(new LoginPage(page).isShowingLoginForm()).toBeVisible();
});

Then("I should still see the login form", async ({ page }) => {
  await expect(new LoginPage(page).isShowingLoginForm()).toBeVisible();
});
