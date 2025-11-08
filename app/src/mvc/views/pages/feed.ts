import { VIEW_CONFIG, layout } from '../../index';
import { IPost } from '../../index';
import { postComponent, signupFormComponent, loginFormComponent } from '../../index';

const mockPost: IPost = {
  id: 1,
  authorName: 'Demo User',
  image: '/assets/img/sample-pic.jpg',
  caption: 'Just a sample post!',
  likes: 42,
  likedByUser: true,
  comments: 5,
};

export const feedView = (items: IPost[], isLoggedIn: boolean, flash: {type: string, message: string} | null = null) => {
  // Demo data
  const posts = Array(9).fill(mockPost);
  const postsHtml = posts.map((post) => postComponent(post)).join('');

  const body = `
    <div class="md:ml-24 p-8">
      <h1 class="text-4xl font-semibold mb-4">Feed</h1>
      <div class="grid gap-6 sm:grid-cols-1 lg:grid-cols-3">
        ${postsHtml}
      </div>
    </div>
  `;

  let modals = '';
  if (!isLoggedIn) {
    modals += '\n' + signupFormComponent + '\n' + loginFormComponent;
  }
  console.log('flash in feedView: ', flash);
  return layout(body, modals, VIEW_CONFIG.feed, isLoggedIn, flash);
};
