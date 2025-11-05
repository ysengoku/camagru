import { VIEW_CONFIG, layout } from '../index';

export const pageNotFoundView = (isLoggedIn: boolean) => {
  const body = `
    <div class="md:ml-24 p-8">
      <h1>404 Not Found</h1>
      <p>The page you are looking for does not exist.</p>
    </div>
  `;

  return layout(body, '', VIEW_CONFIG.notFound, isLoggedIn);
}