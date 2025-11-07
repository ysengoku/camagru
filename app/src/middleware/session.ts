import { SessionModel } from '../mvc';

export async function verifySession(sessionToken: string): Promise<SessionModel | null> {
  const sessionModel = new SessionModel();
  let session;
  try {
    session = sessionModel.getSessionByToken(sessionToken);
  } catch (error) {
    console.error(error);
    return null;
  }
  return session;
}
