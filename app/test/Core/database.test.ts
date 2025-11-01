import { describe, it, expect } from 'vitest';
import { RowDataPacket } from 'mysql2';
import { Database } from '../../src/Core/Database';

describe('Database connection test', () => {
  it('should connect and authenticate successfully', async () => {
    const db = Database.getInstance();
    await db.connect();
    const [rows] = await db.execute<Array<RowDataPacket & { result: number }>>('SELECT 1 + 1 AS result');
    const first = (Array.isArray(rows) ? rows[0] : rows) as { result: number };
    expect(first.result).toBe(2);

    await db.close();
  }, 10000);
});
