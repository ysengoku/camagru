import { IncomingMessage, ServerResponse } from "node:http";

export function loginController(req: IncomingMessage, res: ServerResponse) {
  res.writeHead(405, {});
}