import { ViewConfig } from "./index";

const navbarItemsHtml = {
		feed: `<a href="/feed" class="flex-1 md:flex-none md:py-4 py-1 w-full flex flex-col items-center">
      <img src="/assets/icons/home.svg" alt="Feed" class="w-8 h-8" />
      <p class="text-sm">Feed</p>
      </a>`,
		edit: `<a href="/edit" class="flex-1 md:flex-none md:py-4 py-1 w-full flex flex-col items-center">
      <img src="/assets/icons/camera.svg" alt="Edit" class="w-8 h-8" />
      <p class="text-sm">Edit</p>
      </a>`,
		settings: `<a href="/settings" class="flex-1 md:flex-none md:py-4 py-1 w-full flex flex-col items-center">
      <img src="/assets/icons/settings.svg" class="w-8 h-8" />
      <p class="text-sm">Settings</p>
      </a>`,
	}

export const layout = (
  content: string,
  config: ViewConfig,
  isLoggedin: boolean = false
): string => {
  const headerButtonsHtml = isLoggedin ?
   `<button class="pe-4">Logout</button>` :
   `<button class="pe-4">Login</button>\n<button class="pe-4">Sign up</button>`;

	let navbarHtml = navbarItemsHtml.feed;
	// if (isLoggedin) {
		navbarHtml += '\n' + navbarItemsHtml.edit + '\n' + navbarItemsHtml.settings;
	// }

  return `
  <!DOCTYPE html>
  <html>
    <head>
      <meta charset="utf-8" />
      <title>${config.title}</title>
      <link rel="stylesheet" href="/assets/css/output.css">
      <link rel="icon" type="image/x-icon" href="/assets/img/favicon.ico">
    </head>
    <body class="flex flex-col min-h-screen">
      <header class="flex flex-row justify-between px-4 py-1 h-24 w-full">
        <img src="/assets/img/logo.png" alt="Camagru logo" />
        <div class="flex flex-row">
          ${headerButtonsHtml}
        </div>
      </header>
      <main class="flex-1">
        ${content}
        <nav class="fixed bg-gray-100 flex items-center
                justify-around bottom-0 left-0 w-full h-16
                md:flex-col md:justify-start md:items-stretch
                md:w-24 md:h-screen md:top-24 md:bottom-auto">
          ${navbarHtml}
        </nav>
      </main>
      <footer class="text-center text-gray-500 text-sm py-4 md:ml-52">
        © 2025 Camagru
      </footer>
      <script src="${config.scriptPath}" type="module"></script>
    </body>
  </html>
`;
};
