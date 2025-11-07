// models
import { UserModel } from './models/User';
import { SessionModel } from './models/Session';
import { PostModel } from './models/Post';

import type { IPost } from './models/Post';

// views
import { VIEW_CONFIG } from './views/viewConfig';
import { layout } from './views/layout';
import { feedView } from './views/pages/feed';
import { pageNotFoundView } from './views/pages/pageNotFound';
import { signupFormComponent } from './views/components/signup';
import { loginFormComponent } from './views/components/login';
import { postComponent } from './views/components/post';

import type { ViewConfig } from './views/viewConfig';

// controllers
import { feedController } from './controllers/feedController';
import { editController } from './controllers/editController';
import { settingsController } from './controllers/settingsController';
import { pageNotFoundController } from './controllers/pageNotFoundController';

export {
  // models
  UserModel,
  SessionModel,
  PostModel,
  IPost,
  // views
  VIEW_CONFIG,
  layout,
  feedView,
  pageNotFoundView,
  signupFormComponent,
  loginFormComponent,
  postComponent,
  ViewConfig,
  // controllers
  feedController,
  editController,
  settingsController,
  pageNotFoundController,
};
