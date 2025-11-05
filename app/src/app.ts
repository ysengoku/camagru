import { Database } from './core/Database';
import { feedController, editController, settingsController, pageNotFoundController } from './Controllers';
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

const server = http.createServer((req, res) => {
  const url = new URL(req.url || '', `http://${req.headers.host}`);

  if (url.pathname.startsWith('/api')) {
    apiRouter(req, res);
    return;
  }

  const routeMap: Record<string, (req: http.IncomingMessage, res: http.ServerResponse) => void> = {
    '/': feedController,
    '/edit': editController,
    '/settings': settingsController,
  };

  const handler = routeMap[url.pathname];
  if (handler) {
    handler(req, res);
    return;
  }
  pageNotFoundController(res);
});

server.listen(serverPort, () => console.log('Server running'));
