import { Model } from '../../core/Model';
import { RowDataPacket } from 'mysql2/promise';

export class UserModel extends Model {
  public id: number = 0;
  public username: string = '';
  public email: string = '';
  public password_hash: string = '';
  public email_verified: boolean = false;
  public verification_token: string = '';
  public created_at: Date | null = null;

  public constructor() {
    super('users');
  }

  public async createUser(): Promise<this> {
    const insertId = await super.create({
      username: this.username,
      email: this.email,
      password_hash: this.password_hash,
      email_verified: false,
      verification_token: this.verification_token,
    });
    this.id = insertId;
    return this;
  }

  public async emailVerified(): Promise<void> {
    this.email_verified = true;
    this.verification_token = '';

    this.edit(this.id, {
      email_verified: this.email_verified,
      verification_token: this.verification_token,
    });
  }

  public static async findByKey(key: string, value: string): Promise<UserModel | null> {
    const allowedKeys = ['username', 'email', 'verification_token'];
    if (!allowedKeys.includes(key)) {
      throw new Error('Invalid search key');
    }
    const instance = new this();
    const sql = `SELECT * FROM ${instance.tableName} WHERE ${key} = ? LIMIT 1`;
    const [rows, _fields] = await instance.db.execute<RowDataPacket[]>(sql, [value]);
    if (rows.length === 0) {
      return null;
    }
    const row = rows[0];

    const user = new this();
    user.id = row.id;
    user.username = row.username;
    user.email = row.email;
    user.password_hash = row.password_hash;
    user.email_verified = row.email_verified === 1;
    user.verification_token = row.verification_token;
    user.created_at = row.created_at;
    return user;
  }
}
