import { Model } from '../Core/Model';

export interface IPost {
  id: number;
  authorName: string;
  image: string;
  caption?: string,
  likes?: number;
  likedByUser: boolean,
  comments?: number;
}

class PostModel extends Model {
  public id: number = 0;
  public author_id: number = 0;
  public image: string = '';
  public created_at: Date | null = null;

  public constructor() {
    super('posts');
  }

  public async fetchPostForFeed(id: number): Promise<IPost | null> {
    const post = await this.getById(id);
    if (!post) {
      return null;
    }
  }

  public async fetchForFeed(limit: number = 6, offset: number = 0): Promise<IPost[]> {

  }

  public async getPostDetail() {
    
  }
}
