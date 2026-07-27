const MAILPIT_URL = process.env.MAILPIT_URL || "http://localhost:8025";

export type MailpitMessage = {
  HTML: string;
  Text: string;
};

type MailpitSearchResult = {
  messages: { ID: string }[];
};

/**
 * Polls Mailpit for the most recent message sent to `recipient`. Mail delivery to Mailpit is
 * asynchronous relative to the HTTP response that triggered it, so this needs to retry rather
 * than assume the message is already there.
 */
export async function waitForEmailTo(recipient: string, { timeoutMs = 15_000, intervalMs = 500 } = {}): Promise<MailpitMessage> {
  const deadline = Date.now() + timeoutMs;
  while (Date.now() < deadline) {
    const searchResponse = await fetch(`${MAILPIT_URL}/api/v1/search?query=${encodeURIComponent(`to:${recipient}`)}`);
    const searchResult = (await searchResponse.json()) as MailpitSearchResult;
    const firstMessage = searchResult.messages?.[0];
    if (firstMessage) {
      const messageResponse = await fetch(`${MAILPIT_URL}/api/v1/message/${firstMessage.ID}`);
      return (await messageResponse.json()) as MailpitMessage;
    }
    await new Promise((resolve) => setTimeout(resolve, intervalMs));
  }
  throw new Error(`No email arrived for ${recipient} within ${timeoutMs}ms`);
}

export function extractLink(message: MailpitMessage, pattern: RegExp): string {
  const body = message.HTML || message.Text || "";
  const match = body.match(pattern);
  if (!match) {
    throw new Error(`No link matching ${pattern} found in email body:\n${body}`);
  }
  return match[0].replace(/&amp;/g, "&");
}

export async function purgeMailbox() {
  await fetch(`${MAILPIT_URL}/api/v1/messages`, { method: "DELETE" });
}
