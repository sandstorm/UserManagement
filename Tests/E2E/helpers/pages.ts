import type { Page } from "@playwright/test";

// Flow's default auth token field names - used by both the Login form and the Registration form
// (the Registration form's email field name is intentionally overridden to this, see Index.html)
const USERNAME_FIELD = 'input[name="__authentication[Neos][Flow][Security][Authentication][Token][UsernamePassword][username]"]';
const PASSWORD_FIELD = 'input[name="__authentication[Neos][Flow][Security][Authentication][Token][UsernamePassword][password]"]';

export class LoginPage {
  constructor(private readonly page: Page) {}

  async goto() {
    await this.page.goto("/login");
  }

  async login(email: string, password: string) {
    const form = this.page.locator('form[action="/login/authenticate"]');
    await form.locator(USERNAME_FIELD).fill(email);
    await form.locator(PASSWORD_FIELD).fill(password);
    await form.locator('input[type="submit"]').click();
  }

  async logout() {
    // the logout form only renders on /login (via the ifAuthenticated viewhelper there) - navigate
    // there first rather than assuming the caller is already on a page that has it (e.g. /profile)
    await this.goto();
    await this.page.locator('form[action="/logout"] input[type="submit"], form[action="/logout"] button[type="submit"]').click();
  }

  isLoggedIn() {
    return this.page.locator('form[action="/logout"]');
  }

  isShowingLoginForm() {
    return this.page.locator('form[action="/login/authenticate"]');
  }
}

export class RegistrationPage {
  constructor(private readonly page: Page) {}

  async goto() {
    await this.page.goto("/account/signup/index");
  }

  async register(email: string, password: string, passwordConfirmation: string, firstName: string, lastName: string) {
    const form = this.page.locator('form[action="/account/signup/submit"]');
    // NOTE: Index.html sets an explicit `name` override on this field (to the Flow auth-token
    // username field), but Fluid's form.textfield ignores that when `property` is also set - the
    // field is actually submitted as `registrationFlow[email]`, confirmed via the rendered HTML.
    await form.locator('[name="registrationFlow[email]"]').fill(email);
    await form.locator('[name="registrationFlow[passwordDto][password]"]').fill(password);
    await form.locator('[name="registrationFlow[passwordDto][passwordConfirmation]"]').fill(passwordConfirmation);
    await form.locator('[name="registrationFlow[attributes][firstName]"]').fill(firstName);
    await form.locator('[name="registrationFlow[attributes][lastName]"]').fill(lastName);
    await form.locator('input[type="submit"]').click();
  }

  isShowingForm() {
    return this.page.locator('form[action="/account/signup/submit"]');
  }
}

export class ActivationPage {
  constructor(private readonly page: Page) {}

  async open(link: string) {
    await this.page.goto(link);
  }
}

export class ResetPasswordPage {
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

  isShowingResetForm() {
    return this.page.locator('form[action="/account/updatepassword"]');
  }

  async setNewPassword(password: string) {
    const form = this.page.locator('form[action="/account/updatepassword"]');
    await form.locator('[name="resetPasswordFlow[passwordDto][password]"]').fill(password);
    await form.locator('[name="resetPasswordFlow[passwordDto][passwordConfirmation]"]').fill(password);
    await form.locator('input[type="submit"]').click();
  }
}

export class ProfilePage {
  constructor(private readonly page: Page) {}

  async goto() {
    await this.page.goto("/profile");
  }

  async setNewPassword(password: string) {
    const form = this.page.locator('form[action="/profile/password"]');
    await form.locator('#password\\[0\\]').fill(password);
    await form.locator('#password\\[1\\]').fill(password);
    await form.locator('button, input[type="submit"]').click();
  }
}

export class SuccessOrAlertBanner {
  constructor(private readonly page: Page) {}

  isSuccess() {
    return this.page.locator(".callout.success");
  }

  isAlert() {
    return this.page.locator(".callout.alert");
  }
}
