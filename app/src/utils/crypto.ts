import crypto from 'node:crypto';
import { createHash } from 'crypto';

export function generateToken(): string {
  return crypto.randomBytes(32).toString('hex');
}

export function sha256(data: Buffer | string): Buffer {
  return createHash('sha256').update(data).digest();
}
