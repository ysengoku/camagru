import { Model } from '../Core/Model';

class Post extends Model {
  public id: number = 0;
  public user_id: number = 0;
  public url: string = '';
  public created_at: Date | null = null;

  public constructor() {
    super('posts');
  }  
}
