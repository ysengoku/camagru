import { studioStore } from '../../store/studioStore.js';
import { studioConfig } from '../studioConfig.js';

export class ToolManager {
  constructor(editor, panel) {
    this.editor = editor;
    this.panel = panel;
  }

  get store() {
    return studioStore;
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

  /**
  * Binds mouse interaction events to a given element, allowing for drag and click detection.
  * @param {HTMLDivElement} container - The container element that holds the target element.
  * @param {HTMLElement|String} target - The DOM element or selector to bind the events to OR selector.
  * @param {Object} handlers - An object containing handler functions for various events.
  * @param {Function} handlers.shouldIgnore - A function that determines if the event should be ignored.
  * @param {Function} handlers.onDragMove - A function called when the element is being dragged.
  * @param {Function} handlers.onDragEnd - A function called when the drag operation ends.
  * @param {Function} handlers.onSingleClick - A function called on a single click event.
  * @param {Function} handlers.onDoubleClick - A function called on a double click event.
  * 
  * @returns {Function} A cleanup function to remove the event listeners.
  **/
  bindMouseInteraction(container, target, handlers = {}) {
    const resolveTarget = (e) => {
      if (typeof target === 'string') {
        return e.target.closest(target);
      }
      return e.target === target || target.contains(e.target)
        ? target
        : null;
    };

    const drag = {
      target: null,
      isDragging: false,
      hasMoved: false,
      startX: 0,
      startY: 0,
      originX: 0,
      originY: 0,
      elementWidth: 0,
      elementHeight: 0,
      containerWidth: 0,
      containerHeight: 0,
      clickCount: 0,
      clickTimer: null,
    };

    const onPointerDown = (e) => {
      const target = resolveTarget(e);
      if (!target || !container.contains(target)) {
        return;
      }
      if (handlers.shouldIgnore?.(e, target)) {
        return;
      }

      drag.target = target;
      drag.isDragging = true;
      drag.hasMoved = false;
      target.setPointerCapture(e.pointerId);
      drag.startX = e.clientX;
      drag.startY = e.clientY;

      const containerRect = container.getBoundingClientRect();
      const elRect = target.getBoundingClientRect();
      drag.originX = elRect.left - containerRect.left;
      drag.originY = elRect.top - containerRect.top;
      drag.elementWidth = elRect.width;
      drag.elementHeight = elRect.height;
      drag.containerWidth = containerRect.width;
      drag.containerHeight = containerRect.height;
    };

    const onPointerMove = (e) => {
      if (!drag.isDragging || !drag.target) {
        return;
      }
      const deltaX = e.clientX - drag.startX;
      const deltaY = e.clientY - drag.startY;
      if (Math.abs(deltaX) > 3 || Math.abs(deltaY) > 3) {
        drag.hasMoved = true;
      }
      if (drag.hasMoved) {
        const rawX = drag.originX + deltaX;
        const rawY = drag.originY + deltaY;

        const maxX = drag.containerWidth - drag.elementWidth;
        const maxY = drag.containerHeight - drag.elementHeight;
        const x = Math.min(Math.max(rawX, 0), Math.max(maxX, 0));
        const y = Math.min(Math.max(rawY, 0), Math.max(maxY, 0));
        handlers.onDragMove?.({
          target: drag.target,
          x: x,
          y: y,
          event: e,
        });
      }
    };

    const onPointerUp = (e) => {
      if (!drag.isDragging || !drag.target) {
        return;
      }
      const target = drag.target;
      drag.isDragging = false;
      target.releasePointerCapture(e.pointerId);

      if (drag.hasMoved) {
        handlers.onDragEnd?.({ target, event: e });
        drag.target = null;
        return;
      }

      ++drag.clickCount;
      if (drag.clickTimer) {
        clearTimeout(drag.clickTimer);
      }

      drag.clickTimer = setTimeout(() => {
        if (drag.clickCount === 1) {
          handlers.onSingleClick?.({ target, event: e });
        }
        drag.clickCount = 0;
        drag.clickTimer = null;
      }, this.config.doubleClickThreshold);

      if (drag.clickCount === 2) {
        clearTimeout(drag.clickTimer);
        drag.clickCount = 0;
        drag.clickTimer = null;
        handlers.onDoubleClick?.({ target, event: e });
      }

      drag.target = null;
    };

    container.addEventListener('pointerdown', onPointerDown);
    container.addEventListener('pointermove', onPointerMove);
    container.addEventListener('pointerup', onPointerUp);

    return () => {
      container.removeEventListener('pointerdown', onPointerDown);
      container.removeEventListener('pointermove', onPointerMove);
      container.removeEventListener('pointerup', onPointerUp);
    };
  }
}
