import { UserModel } from '../index';
import { feedView } from '../index';
import { fetchItems } from './feedController';
import { FLASH_MESSAGE_TYPE } from '../../utils/constants';
import { IncomingMessage, ServerResponse } from 'node:http';

export async function verifyEmailController(req: IncomingMessage, res: ServerResponse) {
  const url = new URL(req.url || '', `http://${req.headers.host}`);
  const token = url.searchParams.get('token');

  const items = await fetchItems();
  let html = feedView(items, false, {
      type: FLASH_MESSAGE_TYPE.ERROR,
      message: 'Verification failed. Please try again.',
    });
  if (!token) {
    res.writeHead(400, { 'Content-Type': 'text/html' });
    res.end(html);
    return;
  }
  const user = await UserModel.findByKey('verification_token', token);
  if (!user) {
    res.writeHead(400, { 'Content-Type': 'text/html' });
    res.end(html);
    return;
  }
  await user.emailVerified();

  html = feedView(items, false, {
      type: FLASH_MESSAGE_TYPE.SUCCESS,
      message: 'Email verification completed! Please log in to continue.',
    });
  res.writeHead(200, { 'Content-Type': 'text/html' });
  res.end(html);
}
