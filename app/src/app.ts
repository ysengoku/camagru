import { Database } from './core/Database';
import { authMiddleware } from './middleware/authMiddleware';
import {
  feedController,
  editController,
  settingsController,
  verifyEmailController,
  pageNotFoundController,
} from './mvc';
import { apiRouter } from './api/apiRouter';
import http from 'http';

const serverPort = 9000;
const db = Database.getInstance();
try {
  await db.connect();
} catch (error) {
  console.error('Failed to connect to database:', error);
  process.exit(1);
}

const routeMap: Record<string, (req: http.IncomingMessage, res: http.ServerResponse, userId: number) => void> = {
  '/': feedController,
  '/edit': editController,
  '/settings': settingsController,
  '/verify-email': verifyEmailController,
};

const server = http.createServer(async (req, res) => {
  const url = new URL(req.url || '', `http://${req.headers.host}`);

  const auth = await authMiddleware(req, res, url.pathname);
  if (!auth.success) {
    return;
  }
  const userId = auth.session?.user_id || -1;

  if (url.pathname.startsWith('/api')) {
    apiRouter(req, res);
    return;
  }
  const handler = routeMap[url.pathname];
  if (handler) {
    handler(req, res, userId);
    return;
  }
  pageNotFoundController(res);
});

server.listen(serverPort, () => console.log('Server running'));
