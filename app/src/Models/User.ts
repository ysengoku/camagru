import { Model } from '../core/Model';
import { RowDataPacket } from 'mysql2/promise';

export interface IUser {
}

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

  public async findByKey(key: string, value: string): Promise<UserModel | null> {
    const allowedKeys = ['username', 'email'];
    if (!allowedKeys.includes(key)) {
      throw new Error('Invalid search key');
    }
    const sql = `SELECT * FROM ${this.tableName} WHERE ${key} = ? LIMIT 1`;
    console.log('SQL: ', sql, value);
    const [rows, _fields] = await this.db.execute<RowDataPacket[]>(sql, [value]);
    if (rows.length === 0) {
      return null;
    }
    const row = rows[0];
    this.id = row.id;
    this.username = row.username;
    this.email = row.email;
    this.password_hash = row.password_hash;
    this.email_verified = row.email_verified === 1;
    this.verification_token = row.verification_token;
    this.created_at = row.created_at;
    return this;
  }
}