import type { Page } from "@playwright/test";

const USERNAME_FIELD = 'input[name="__authentication[Neos][Flow][Security][Authentication][Token][UsernamePassword][username]"]';
const PASSWORD_FIELD = 'input[name="__authentication[Neos][Flow][Security][Authentication][Token][UsernamePassword][password]"]';

export default class FrontendLoginPage {
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
    // there first rather than assuming the caller is already on a page that has it
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
