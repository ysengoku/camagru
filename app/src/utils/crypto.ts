import { createHash } from 'crypto';

export function sha256(data: Buffer | string): Buffer {
  return createHash('sha256').update(data).digest();
}
