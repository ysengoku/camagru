import { pageNotFoundView } from "../Views";
import { ServerResponse } from "node:http";

export function pageNotFoundController(res: ServerResponse) {
  // Check auth status
  const isLoggedIn = false;

  res.writeHead(404, { 'Content-Type': 'text/html' });
  res.end(pageNotFoundView(isLoggedIn));
}