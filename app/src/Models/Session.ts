import { Model } from '../Core/Model';

class Session extends Model {
    public id: number = 0;
    public user_id: number = 0;
    public created_at: Date | null = null;

}
