import { SessionModel } from '../mvc';
import { ServerResponse } from 'node:http';

export function parseCookies(cookieHeader: string | undefined): Record<string, string> {
  if (!cookieHeader) {
    return {};
  }
  return Object.fromEntries(
    cookieHeader.split(';').map((c) => {
      const [key, ...v] = c.trim().split('=');
      return [key, decodeURIComponent(v.join('='))];
    }),
  );
}

const cookieOptions = {
  maxAge: 12 * 60 * 60, // 12 hours
  path: '/',
  secure: process.env.NODE_ENV === 'production',
  sameSite: 'Strict',
};

function createCookieString(key: string, value: string): string {
  const httpOnly = key === 'sessionToken';

  let cookieParts = [key + '=' + value];
  cookieParts.push(`Max-Age=${cookieOptions.maxAge.toString()}`);
  cookieParts.push(`Path=${cookieOptions.path}`);
  cookieParts.push(`SameSite=${cookieOptions.sameSite}`);
  if (httpOnly) {
    cookieParts.push('HttpOnly');
  }
  if (cookieOptions.secure) {
    cookieParts.push('Secure');
  }

  return cookieParts.join(';');
}

export function setCookies(res: ServerResponse, session: SessionModel): void {
  const sessionCookie = createCookieString('sessionToken', session.session_token);
  const csrfCookie = createCookieString('csrfToken', session.csrf_token);
  res.setHeader('Set-Cookie', [sessionCookie, csrfCookie]);
}
