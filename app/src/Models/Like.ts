import { Model } from '../core/Model';

export class LikeModel extends Model {
  public id: number = 0;
  public post_id: number = 0;
  public author_id: number = 0;
  public created_at: Date | null = null;

  public constructor() {
    super('likes');
  }
}