import { Model } from '../Core/Model';

class User extends Model {
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
}