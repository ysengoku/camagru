import { feedView } from '../index';
import { IncomingMessage, ServerResponse } from 'node:http';

export function feedController(req: IncomingMessage, res: ServerResponse) {
  // Check auth status
  const isLoggedIn = false;

  // Fetch items
  // const items = Post.fetchRange();
  const items = [];
  const html = feedView(items, isLoggedIn);
  res.writeHead(200, { 'Content-Type': 'text/html' });
  res.end(html);
}
