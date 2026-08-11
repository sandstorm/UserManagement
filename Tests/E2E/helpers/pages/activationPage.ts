import type { Page } from "@playwright/test";

export default class ActivationPage {
  constructor(private readonly page: Page) {}

  async open(link: string) {
    await this.page.goto(link);
  }

  isShowingSuccess() {
    return this.page.locator(".callout.success");
  }

  isShowingError() {
    return this.page.locator(".callout.alert");
  }
}
