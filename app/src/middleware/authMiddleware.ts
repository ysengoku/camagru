import { verifySession } from './session';
import { SessionModel } from '../mvc';
import { parseCookies } from './cookies';
import { HEADERS } from '../utils/constants';
import { IncomingMessage, ServerResponse } from 'node:http';

export async function authMiddleware(
  req: IncomingMessage,
  res: ServerResponse,
  path: string,
): Promise<{ success: boolean; session?: SessionModel | null }> {
  const authExemptedEndpoints = ['/api/signup', '/api/login'];
  const authExemptedPages = ['/'];

  const cookies = parseCookies(req.headers.cookie);
  const sessionToken = cookies.sessionToken || null;
  const session = sessionToken ? await verifySession(sessionToken) : null;

  // For MVC
  if (!path.startsWith('/api')) {
    if (session || authExemptedPages.includes(path)) {
      return { success: true, session };
    }
    res.writeHead(302, { Location: '/' });
    res.end();
    return { success: false };
  }

  // For API
  const method = (req.method || 'GET').toUpperCase();
  if (method === 'GET' || authExemptedEndpoints.includes(path)) {
    return { success: true, session };
  }

  const csrfFromHeaders = ((req.headers['x-csrftoken'] || req.headers['X-CSRFToken']) as string) || undefined;
  const csrfToken = csrfFromHeaders || cookies.csrfToken;
  if (!session || session.csrf_token !== csrfToken) {
    res.writeHead(401, HEADERS.JSON);
    res.end(JSON.stringify({ success: false, message: 'Unauthorized' }));
    return { success: false };
  }
  return { success: true, session };
}
