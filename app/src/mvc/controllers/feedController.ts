import { feedView } from '../index';
import { IncomingMessage, ServerResponse } from 'node:http';

export function feedController(req: IncomingMessage, res: ServerResponse, userId: number) {
  // Check auth status
  const isLoggedIn = userId > 0;

  // Fetch items
  // const items = Post.fetchRange();
  const items = fetchItems();
  const html = feedView(items, isLoggedIn);
  res.writeHead(200, { 'Content-Type': 'text/html' });
  res.end(html);
}

export async function fetchItems() {
  const items = [];

  return items;
}
