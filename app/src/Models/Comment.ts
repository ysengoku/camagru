import { Model } from '../Core/Model';

class Comment extends Model {
  public id: number = 0;
  public post_id: number = 0;
  public author_id: number = 0;
  public content: string = '';
  public created_at: Date | null = null;

  public constructor() {
    super('comments');
  }

}