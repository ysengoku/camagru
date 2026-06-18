import { studioStore } from '../../store/studioStore.js';
import { studioConfig } from '../studioConfig.js';

export class ToolManager {
  constructor(editor, panel) {
    this.editor = editor;
    this.panel = panel;
  }

  get state() {
    return studioStore.state;
  }

  set state(newState) {
    studioStore.setState(newState);
  }

  get config() {
    return studioConfig;
  }

  get inEditor() {
    return this.state.editorMode !== 'menu';
  }

  init() {}
}
