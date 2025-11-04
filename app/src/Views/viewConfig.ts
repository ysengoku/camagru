export interface ViewConfig {
  title: string,
  scriptPath: string,
}

export const VIEW_CONFIG = {
  feed: {
    title: 'Feed | Camagru',
    scriptPath: '/assets/js/feed.js',
  },
  edit: {
    title: 'Edit | Camagru',
    scriptPath: '/assets/js/edit.js'
  },
  settings: {
    title: 'Settings | Camagru',
    scriptPath: '/assets/js/settings.js'
  },
	notFound: {
		title: 'Page Not Found | Camagru',
    scriptPath: '',
	}
}
