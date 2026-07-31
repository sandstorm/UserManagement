import type { Page } from "@playwright/test";

export default class NeosContentPage {
  public readonly URL_REGEX = /neos\/content/;

  constructor(private readonly page: Page) {}

  async goto() {
    await this.page.goto("/neos/content");
  }
}
