import mysql from 'mysql2/promise';

export class Database {
  private static instance: Database | null = null;
  private pool: mysql.Pool | null = null;

  private host: string;
  private port: number;
  private databaseUser: string;
  private databasePassword: string;
  private databaseName: string;

  private constructor() {
    this.host = process.env.DB_HOST || 'camagru_database';
    this.port = Number(process.env.DB_PORT || 3306);
    this.databaseUser = process.env.MYSQL_USER || '';
    this.databasePassword = process.env.MYSQL_PASSWORD || '';
    this.databaseName = process.env.MYSQL_DATABASE || 'camagru_database';
  }

  public static getInstance(): Database {
    if (!Database.instance) {
      Database.instance = new Database();
    }
    return Database.instance;
  }

  public async connect(): Promise<void> {
    if (this.pool) {
      return;
    }
    this.pool = mysql.createPool({
      host: this.host,
      port: this.port,
      user: this.databaseUser,
      password: this.databasePassword,
      database: this.databaseName,
      waitForConnections: true,
      connectionLimit: 10,
      queueLimit: 0,
    });
  }

  public async query<T extends mysql.QueryResult = mysql.ResultSetHeader>(
    sql: string,
    params: any[] = [],
  ): Promise<[T, mysql.FieldPacket[]]> {
    if (!this.pool) {
      throw new Error('Database not connected');
    }
    return this.pool.query<T>(sql, params);
  }

  public async execute<T extends mysql.QueryResult = mysql.ResultSetHeader>(
    sql: string,
    params: any[] = [],
  ): Promise<[T, mysql.FieldPacket[]]> {
    if (!this.pool) {
      throw new Error('Database not connected');
    }
    return this.pool.execute<T>(sql, params);
  }

  public async transaction<T>(
    fn: (conn: mysql.PoolConnection) => Promise<T>,
  ): Promise<T> {
    if (!this.pool) {
      throw new Error('Database not connected');
    }
    const conn = await this.pool.getConnection();
    try {
      await conn.beginTransaction();
      const result = await fn(conn);
      await conn.commit();
      return result;
    } catch (error) {
      try {
        await conn.rollback();
      } catch (rollbackError) {
        console.error('Rollback failed:', rollbackError);
      }
      throw error;
    } finally {
      conn.release();
    }
  }

  public async close(): Promise<void> {
    await this.pool?.end();
    Database.instance = null;
  }
}
