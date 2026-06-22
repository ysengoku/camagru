import { ToolManager } from './ToolManager.js';

export class StickerManager extends ToolManager {
  constructor(editor, panel) {
    super(editor, panel);
  }

  init() {
    this.panel = {
      ...this.panel,
      list: document.getElementById('sticker-list'),
      scrollLeftButton: document.querySelector('.scroll-left'),
      scrollRightButton: document.querySelector('.scroll-right')
    };

    this.panel.list?.addEventListener('scroll', () => {
      this.updateScrollButtons();
    });
    this.panel.scrollLeftButton.addEventListener('click', () =>
      this.scroll('left')
    );
    this.panel.scrollRightButton.addEventListener('click', () =>
      this.scroll('right')
    );

    this.panel.list?.addEventListener('click', (e) => {
      const stickerBtn = e.target.closest('button[data-sticker]');
      if (!stickerBtn) {
        return;
      }
      const stickerPath = stickerBtn.dataset.sticker;
      this.addSticker(stickerPath);
    });

    this.bindStickerEvents();
    this.setupStoreSubscriptions();
  }

  updateScrollButtons() {
    const scrollLeft = this.panel.list.scrollLeft;
    const scrollWidth = this.panel.list.scrollWidth;
    const clientWidth = this.panel.list.clientWidth;

    scrollLeft === 0
      ? this.panel.scrollLeftButton.classList.add(
          'opacity-50',
          'cursor-not-allowed'
        )
      : this.panel.scrollLeftButton.classList.remove(
          'opacity-50',
          'cursor-not-allowed'
        );

    scrollLeft + clientWidth >= scrollWidth - 10
      ? this.panel.scrollRightButton.classList.add(
          'opacity-50',
          'cursor-not-allowed'
        )
      : this.panel.scrollRightButton.classList.remove(
          'opacity-50',
          'cursor-not-allowed'
        );
  }

  scroll(direction) {
    const scrollAmount = 120;

    if (direction === 'left') {
      this.panel.list.scrollBy({
        left: -scrollAmount,
        behavior: 'smooth',
      });
    } else if (direction === 'right') {
      this.panel.list.scrollBy({
        left: scrollAmount,
        behavior: 'smooth',
      });
    }

    setTimeout(() => this.updateScrollButtons(), 100);
  }

  addSticker(stickerPath) {
    if (!this.inEditor) {
      return;
    }

    const img = new Image();
    img.onload = () => {
      const aspectRatio = img.naturalWidth / img.naturalHeight;

      let drawWidth, drawHeight;
      if (aspectRatio > this.config.canvasAspectRatio) {
        drawWidth = this.editor.canvas.width * 0.5;
        drawHeight = (this.editor.canvas.width / aspectRatio) * 0.5;
      } else {
        drawHeight = this.editor.canvas.height * 0.5;
        drawWidth = this.editor.canvas.height * aspectRatio * 0.5;
      }

      const stickerData = {
        id: `sticker-${Date.now()}`,
        path: stickerPath,
        x: this.config.stickerInitialPosX,
        y: this.config.stickerInitialPosY,
        width: drawWidth,
        height: drawHeight,
        aspectRatio: aspectRatio,
      };
      this.drawSticker(stickerData);

      this.state = (s) => ({
        selectedStickers: [...s.selectedStickers, stickerData],
      });
    };
    img.src = stickerPath;
  }

  drawSticker(stickerData) {
    const template = document.getElementById('sticker-template');
    const overlay = template.content.firstElementChild.cloneNode(true);

    overlay.id = stickerData.id;
    overlay.dataset.aspectRatio = stickerData.aspectRatio;
    overlay.style.left = `${this.config.stickerInitialPosX}px`;
    overlay.style.top = `${this.config.stickerInitialPosY}px`;
    overlay.style.width = `${stickerData.width}px`;
    overlay.style.height = `${stickerData.height}px`;
    overlay.style.backgroundImage = `url(${stickerData.path})`;

    this.editor.container.appendChild(overlay);
  }

  selectStickerForEditing(target) {
    this.deselectStickers();
    target.classList.add('sticker-editing');
  }

  deselectStickers() {
    document
      .querySelectorAll('.sticker-overlay.sticker-editing')
      .forEach((el) => el.classList.remove('sticker-editing'));
  }

  removeSticker(id) {
    const overlay = document.getElementById(id);
    if (overlay) {
      overlay.remove();
    }
    this.state = (s) => ({
      selectedStickers: s.selectedStickers.filter((sticker) => sticker.id !== id),
    });
  }

  clearStickers() {
    this.state.selectedStickers.forEach((sticker) => {
      const overlay = document.getElementById(sticker.id);
      if (overlay) {
        overlay.remove();
      }
    });
    this.state = (s) => ({ ...s, selectedStickers: [] });
  }

  bindStickerEvents() {
    this.bindMouseInteraction(this.editor.container, '.sticker-overlay', {
      shouldIgnore: (e) =>
        e.target.closest('.sticker-delete-btn') || e.target.closest('.sticker-resize-handle'),
      onDragMove: ({ target, x, y }) => {
        target.style.left = `${x}px`;
        target.style.top = `${y}px`;
      },
      onDragEnd: ({ target }) => {
        const id = target.id;
        this.state = (s) => ({
          selectedStickers: s.selectedStickers.map((sticker) =>
            sticker.id === id
              ? { ...sticker, x: parseFloat(target.style.left), y: parseFloat(target.style.top) }
              : sticker
          ),
        });
      },
      onSingleClick: ({ target }) => this.selectStickerForEditing(target),
    });

    // Resize handling
    this.bindMouseInteraction(this.editor.container, '.sticker-resize-handle', {
      onDragMove: ({ target, event }) => {
        const overlay = target.closest('.sticker-overlay');
        const overlayRect = overlay.getBoundingClientRect();
        const aspectRatio = parseFloat(overlay.dataset.aspectRatio);
        const newWidth = Math.max(20, event.clientX - overlayRect.left);
        const newHeight = newWidth / aspectRatio;

        overlay.style.width = `${newWidth}px`;
        overlay.style.height = `${newHeight}px`;
      },
      onDragEnd: ({ target }) => {
        const overlay = target.closest('.sticker-overlay');
        this.state = (s) => ({
          selectedStickers: s.selectedStickers.map((sticker) =>
            sticker.id === overlay.id
              ? { ...sticker, width: parseFloat(overlay.style.width), height: parseFloat(overlay.style.height) }
              : sticker
          ),
        });
      },
    });

    this.editor.container.addEventListener('click', (e) => {
      const deleteBtn = e.target.closest('.sticker-delete-btn');
      if (!deleteBtn) {
        return;
      }
      const stickerEl = deleteBtn.closest('.sticker-overlay');
      this.removeSticker(stickerEl.id);
    });

    this.editor.container.addEventListener('pointerdown', (e) => {
      if (!e.target.closest('.sticker-overlay')) {
        this.deselectStickers();
      }
    });
  }

  // ====== Store subscription methods ========================================

  setupStoreSubscriptions() {
    this.store.subscribe((newState) => {
      if (newState.selectedStickers.length >= this.config.maxStickerCount) {
        this.disableStickerSelection();
        return;
      }
      this.enableStickerSelection();
    });
  }

  disableStickerSelection() {
    this.panel.list.querySelectorAll('button[data-sticker]').forEach((btn) => {
      btn.disabled = true;
      btn.classList.add('opacity-50', 'cursor-not-allowed');
    });
  }

  enableStickerSelection() {
    this.panel.list.querySelectorAll('button[data-sticker]').forEach((btn) => {
      btn.disabled = false;
      btn.classList.remove('opacity-50', 'cursor-not-allowed');
    });
  }
}
