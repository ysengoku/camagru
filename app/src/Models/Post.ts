import { Model } from '../core/Model';

export interface IPost {
  id: number;
  authorName: string;
  image: string;
  caption?: string,
  likes?: number;
  likedByUser: boolean,
  comments?: number;
}

export class PostModel extends Model {
  public id: number = 0;
  public author_id: number = 0;
  public image: string = '';
  public created_at: Date | null = null;

  public constructor() {
    super('posts');
  }
}
