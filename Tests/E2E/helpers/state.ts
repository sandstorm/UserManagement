const createdEmails = new Set<string>();

export function trackEmail(email: string) {
  createdEmails.add(email);
}

export function getTrackedEmails(): string[] {
  return Array.from(createdEmails);
}

export function clearTrackedEmails() {
  createdEmails.clear();
}
