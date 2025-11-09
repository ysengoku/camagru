import { verifyEmailService } from '../../services';
import { feedView } from '../index';
import { fetchItems } from './feedController';
import { FLASH_MESSAGE_TYPE } from '../../utils/constants';
import { IncomingMessage, ServerResponse } from 'node:http';

export async function verifyEmailController(req: IncomingMessage, res: ServerResponse) {
  const url = new URL(req.url || '', `http://${req.headers.host}`);
  const token = url.searchParams.get('token');

  const items = await fetchItems();
  if (!token) {
    const html = feedView(items, false, {
      type: FLASH_MESSAGE_TYPE.ERROR,
      message: 'Verification failed. Please try again.',
    });
    res.writeHead(400, { 'Content-Type': 'text/html' });
    res.end(html);
    return;
  }

  const verified = await verifyEmailService(token);

  const flashMessage = verified
    ? { type: FLASH_MESSAGE_TYPE.SUCCESS, message: 'Email verification completed! Please log in to continue.' }
    : { type: FLASH_MESSAGE_TYPE.ERROR, message: 'Verification failed. Please try again.' };

  const status = verified ? 200 : 400;
  const html = feedView(items, false, flashMessage);
  res.writeHead(status, { 'Content-Type': 'text/html' });
  res.end(html);
}
