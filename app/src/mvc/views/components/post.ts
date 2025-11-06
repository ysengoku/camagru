import { IPost } from '../../index';

export const postComponent = (post: IPost): string => {
  const heartIconSrc = post.likedByUser
    ? '/assets/icons/heart-fill.svg'
    : '/assets/icons/heart.svg';

  return `
    <article class="rounded-lg shadow-md overflow-hidden">
      <img src="${post.image}" alt="${post.caption || 'post image'}"
           class="w-full h-64 object-cover">
      <div class="p-4">
        <p class="text-lg font-semibold">${post.authorName}</p>
        <p class="text-gray-700 mb-2">${post.caption || ''}</p>
        <div class="flex justify-start gap-4">
          <div class="flex items-center gap-2">
            <img src=${heartIconSrc} />
            ${post.likes ?? 0}
          </div>
          <div class="flex items-center gap-2">
            <img src="/assets/icons/bubble.svg" />
            ${post.comments ?? 0}
          </div>
        </div>
      </div>
    </article>
`;
};
