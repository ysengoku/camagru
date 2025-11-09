import { UserModel } from '../mvc';

export async function verifyEmailService(token: string): Promise<boolean> {
  const user = await UserModel.findByKey('verification_token', token);
  if (!user) {
    return false;
  }
  await user.emailVerified();
  return true;
}
