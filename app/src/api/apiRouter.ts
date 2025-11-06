import { signupController, loginController } from './controllers';
import { API_ENDPOINTS } from './routes';
import { HEADERS } from '../utils/constants';
import { IncomingMessage, ServerResponse } from 'node:http';

export function apiRouter(req: IncomingMessage, res: ServerResponse) {
  const url = new URL(req.url || '', `http://${req.headers.host}`);

  switch (url.pathname) {
    case API_ENDPOINTS.SIGNUP:
      signupController(req, res);
      return;
    case API_ENDPOINTS.LOGIN:
      loginController(req, res);
      return;
    default:
      res.writeHead(404, HEADERS.JSON);
      res.end(JSON.stringify({ success: false, message: 'Not found' }));
  }
}
