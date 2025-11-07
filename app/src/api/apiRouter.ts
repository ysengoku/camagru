import { signupController, loginController } from './controllers';
import { API_ENDPOINTS } from './routes';
import { HEADERS } from '../utils/constants';
import { IncomingMessage, ServerResponse } from 'node:http';

export function apiRouter(req: IncomingMessage, res: ServerResponse, userId: number = -1) {
  const url = new URL(req.url || '', `http://${req.headers.host}`);

  const handler = apiRouteMap[url.pathname];
  if (handler) {
    handler(req, res, userId);
    return;
  }
  res.writeHead(404, HEADERS.JSON);
  res.end(JSON.stringify({ success: false, message: 'Not found' }));
}

const apiRouteMap: Record<string, (req: IncomingMessage, res: ServerResponse, userId: number) => void> = {
  [API_ENDPOINTS.SIGNUP]: signupController,
  [API_ENDPOINTS.LOGIN]: loginController,
  // TODO
  // [API_ENDPOINTS.LOGOUT]:
  // [API_ENDPOINTS.SETTINGS]:
  // [API_ENDPOINTS.POSTS]:
};
