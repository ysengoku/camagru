import { Model } from '../../core/Model';
import { generateToken } from '../../utils/crypto';
import { RowDataPacket } from 'mysql2/promise';

export class SessionModel extends Model {
  public id: number = 0;
  public user_id: number = 0;
  public session_token: string = '';
  public csrf_token: string = '';
  public created_at: Date | null = null;
  public expired_at: Date | null = null;

  public constructor() {
    super('sessions');
  }

  public async createSession(): Promise<{
    success: boolean;
    session?: SessionModel;
    error?: any;
  }> {
    this.expired_at = new Date(Date.now() + 12 * 60 * 60 * 1000);

    let insertId = -1;
    while (insertId === -1) {
      this.session_token = generateToken();
      try {
        insertId = await super.create({
          user_id: this.user_id,
          session_token: this.session_token,
          expired_at: this.expired_at,
        });
      } catch (error: any) {
        if (error.code === 'ER_DUP_ENTRY') {
          continue;
        }
        console.error(error);
        return { success: false, error };
      }
    }
    this.id = insertId;
    return { success: true, session: this };
  }

  public async getSessionByToken(token: string): Promise<SessionModel | null> {
    const sql = `SELECT * FROM ${this.tableName} WHERE session_token = ? LIMIT 1`;
    const [rows, _fields] = await this.db.execute<RowDataPacket[]>(sql, [token]);
    if (rows.length === 0) {
      return null;
    }
    const row = rows[0];
    if (row.expired_at && new Date(row.expired_at) < new Date()) {
      this.deleteSession(row.session_token);
      return null;
    }
    const session = new SessionModel();
    session.id = row.id;
    session.user_id = row.user_id;
    session.session_token = row.session_token;
    session.csrf_token = row.csrf_token;
    session.created_at = row.created_at;
    session.expired_at = row.expired_at;
    return session;
  }

  // TODO
  public async deleteSession(token: string) {}

  public async extendSession(token: string) {}

  public async deleteExpiredSessions() {}
}
