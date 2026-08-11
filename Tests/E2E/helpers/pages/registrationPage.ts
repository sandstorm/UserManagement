import type { Page } from "@playwright/test";

export default class RegistrationPage {
  constructor(private readonly page: Page) {}

  async goto() {
    await this.page.goto("/account/signup/index");
  }

  // NOTE: Index.html sets an explicit `name` override on the email field (to the Flow auth-token
  // username field), but Fluid's form.textfield ignores that when `property` is also set - the
  // field is actually submitted as `registrationFlow[email]`.
  async register(email: string, password: string, firstName: string, lastName: string, passwordConfirmation = password) {
    const form = this.page.locator('form[action="/account/signup/submit"]');
    await form.locator('[name="registrationFlow[email]"]').fill(email);
    await form.locator('[name="registrationFlow[passwordDto][password]"]').fill(password);
    await form.locator('[name="registrationFlow[passwordDto][passwordConfirmation]"]').fill(passwordConfirmation);
    await form.locator('[name="registrationFlow[attributes][firstName]"]').fill(firstName);
    await form.locator('[name="registrationFlow[attributes][lastName]"]').fill(lastName);
    await form.locator('input[type="submit"]').click();
  }

  isShowingConfirmation() {
    return this.page.locator(".callout.success");
  }

  isShowingForm() {
    return this.page.locator('form[action="/account/signup/submit"]');
  }
}
