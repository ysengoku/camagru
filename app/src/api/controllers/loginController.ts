import { loginService } from '../../services';
import { setCookies } from '../../middleware/cookies';
import { HEADERS } from '../../utils/constants';
import { IncomingMessage, ServerResponse } from 'node:http';

export function loginController(req: IncomingMessage, res: ServerResponse, userId: number) {
  if (req.method !== 'POST') {
    res.writeHead(405, HEADERS.JSON);
    res.end(JSON.stringify({ success: false, message: 'Method Not Allowed' }));
    return;
  }
  if (userId > 1) {
    res.writeHead(200, HEADERS.JSON);
    res.end(JSON.stringify({ success: true, message: 'OK' }));
    return;
  }

  // Receive request body
  let requestBody = '';
  req.on('data', (chunk) => {
    requestBody += chunk;
  });

  req.on('end', async () => {
    let result;
    try {
      const data = JSON.parse(requestBody);
      console.log(data);
      result = await loginService(data);
    } catch (error) {
      res.writeHead(400, HEADERS.JSON);
      res.end(JSON.stringify({ success: false, message: 'Bad Request' }));
      return;
    }
    if (result.success) {
      const session = result.session;
      if (session) {
        setCookies(res, session);
      }
      res.writeHead(200, HEADERS.JSON);
      res.end(JSON.stringify({ success: true, message: 'OK' }));
      return;
    }
    res.writeHead(401, HEADERS.JSON);
    res.end(JSON.stringify(result));
  });
}
