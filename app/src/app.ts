import { feedController, editController, settingsController, pageNotFoundController } from './Controllers';
import { apiRouter } from './api/apiRouter';
import http from 'http';

const serverPort = 9000;

const server = http.createServer((req, res) => {
  const url = new URL(req.url || '', `http://${req.headers.host}`);

  const routeMap: Record<string, (req: http.IncomingMessage, res: http.ServerResponse) => void> = {
    '/': feedController,
    '/edit': editController,
    '/settings': settingsController,
    '/api': apiRouter,
  };

  const handler = routeMap[url.pathname];
  if (handler) {
    handler(req, res);
    return;
  }
  pageNotFoundController(res);
});

server.listen(serverPort, () => console.log('Server running'));
