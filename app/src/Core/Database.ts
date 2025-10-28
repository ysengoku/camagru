import mysql from 'mysql2/promise';

export class Database {
  private static instance: Database;
  private pool: mysql.Pool;

  private constructor() {
     this.pool = mysql.createPool({
      host: process.env.DB_HOST,
      user: process.env.MYSQL_USER,
      password: process.env.MYSQL_PASSWORD,
      database: process.env.MYSQL_DATABASE,
      port: Number(process.env.DB_PORT),
      waitForConnections: true,
      connectionLimit: 10,
      queueLimit: 0,
     });
  }

  public getInstance(): Database {
    if (!Database.instance) {
        Database.instance = new Database;
    }
    return Database.instance;
  }

  public query() {

  }

  public close() {

  }
}