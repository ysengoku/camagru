import { UserModel, SessionModel } from '../mvc';
import { generateToken } from '../utils/crypto';
import bcrypt from 'bcrypt';

export async function loginService(data: {
  username: string;
  password: string;
}): Promise<{ success: boolean; code: number; message: string; session?: SessionModel }> {
  const { username, password } = data;
  if (!username || !password) {
    return { success: false, code: 400, message: 'Missing required fields' };
  }

  const user = new UserModel();
  let result;
  try {
    result = await user.findByKey('username', username);
  } catch (error) {
    console.error(error);
    return { success: false, code: 500, message: 'Internal server error' };
  }
  if (!result) {
    return { success: false, code: 404, message: 'User not found' };
  }
  if (!user.email_verified) {
    return { success: false, code: 403, message: 'Account not verified' };
  }
  const passwordMatch = await bcrypt.compare(password, user.password_hash);
  if (!passwordMatch) {
    return { success: false, code: 401, message: 'Username or password is incorrect' };
  }

  // Create a new session
  const session = new SessionModel();
  const csrfToken = generateToken();
  const res = await session.createSession(user.id, csrfToken);
  if (!res.success) {
    return { success: false, code: 500, message: 'Internal server error' };
  }
  return { success: true, code: 200, message: 'OK', session: session };
}
