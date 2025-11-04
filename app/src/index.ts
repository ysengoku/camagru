import './Controllers/index'
import { feedController } from './Controllers/index';
import http from 'http';

const serverPort = 9000;

const server = http.createServer((req, res) => {
  console.log(req);
  const url = new URL(req.url || '', `http://${req.headers.host}`);

  switch(url.pathname) {
    case '/feed':
      feedController(req, res);
      break;
    case '/edit':
      break;
    case '/settings':
      break;
    default:
			res.writeHead(404, { 'Content-Type': 'text/html' });
      res.end('<h1>404 Not Found</h1><p>The page you are looking for does not exist.</p>');
  }
});

server.listen(serverPort, () => console.log('Server running'));
