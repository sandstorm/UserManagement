import type { Page } from "@playwright/test";

export default class ResetPasswordPage {
  constructor(private readonly page: Page) {}

  async goto() {
    await this.page.goto("/account/forgotpassword");
  }

  async requestReset(email: string) {
    const form = this.page.locator('form[action="/account/requestpasswordtoken"]');
    await form.locator('[name="resetPasswordFlow[email]"]').fill(email);
    await form.locator('input[type="submit"]').click();
  }

  async open(link: string) {
    await this.page.goto(link);
  }

  async setNewPassword(password: string) {
    const form = this.page.locator('form[action="/account/updatepassword"]');
    await form.locator('[name="resetPasswordFlow[passwordDto][password]"]').fill(password);
    await form.locator('[name="resetPasswordFlow[passwordDto][passwordConfirmation]"]').fill(password);
    await form.locator('input[type="submit"]').click();
  }

  isShowingSuccess() {
    return this.page.locator(".callout.success");
  }

  isShowingError() {
    return this.page.locator(".callout.alert");
  }
}
