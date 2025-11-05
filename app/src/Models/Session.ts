import { Model } from '../core/Model';

export class SessionModel extends Model {
  public id: number = 0;
  public user_id: number = 0;
  public created_at: Date | null = null;
  public expired_at: Date | null = null;
}
