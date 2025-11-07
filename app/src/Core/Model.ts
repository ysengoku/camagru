import { Database } from './Database';
import { ResultSetHeader, RowDataPacket } from 'mysql2/promise';

export abstract class Model {
  protected tableName: string;
  protected db = Database.getInstance();

  constructor(tableName: string) {
    this.tableName = tableName;
  }

  async create(data: Record<string, any>): Promise<number> {
    const sql = `INSERT INTO ${this.tableName} SET ?`;
    const [result] = await this.db.query<ResultSetHeader>(sql, [data]);
    return result.insertId;
  }

  async edit(id: number, data: Record<string, any>): Promise<boolean> {
    const sql = `UPDATE ${this.tableName} SET ? WHERE id = ?`;
    const [result] = await this.db.execute<ResultSetHeader>(sql, [data, id]);
    return result.affectedRows > 0;
  }

  async delete(id: number): Promise<boolean> {
    const sql = `DELETE FROM ${this.tableName} WHERE id = ?`;
    const [result] = await this.db.execute<ResultSetHeader>(sql, [id]);
    return result.affectedRows > 0;
  }

  async getById(id: number): Promise<Record<string, any> | null> {
    const sql = `SELECT * FROM ${this.tableName} WHERE id = ? LIMIT 1`;
    const [rows, _fields] = await this.db.execute<RowDataPacket[]>(sql, [id]);
    if (rows.length === 0) {
      return null;
    }
    return rows[0];
  }

  async fetchRange(limit: number, offset: number = 0): Promise<Record<string, any>[]> {
    const sql = `SELECT * FROM ${this.tableName} LIMIT ? OFFSET ?`;
    const [rows, _fields] = await this.db.execute<RowDataPacket[]>(sql, [limit, offset]);
    return rows;
  }
}
