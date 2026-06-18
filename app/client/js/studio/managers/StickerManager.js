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
      this.selectSticker(stickerPath);
    });

    let isEditing = false;
    this.editor.container?.addEventListener('mousedown', (e) => {
      if (e.target.className === 'sticker-overlay') {
        isEditing = true;
        // e.target.classList.add('sticker-editing');
      }
    });
    this.editor.container?.addEventListener('mouseup', () => {
      isEditing = false;
      const editingSticker = document.querySelector('.sticker-editing');
      // if (editingSticker) {
        // editingSticker.classList.remove('sticker-editing');
      // }
    });

    this.editor.container?.addEventListener('mousemove', (e) => {
      if (isEditing) {
        this.moveSticker(e);
      }
    });
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

  selectSticker(stickerPath) {
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
      };
      this.drawSticker(stickerData);
      // update immutably via the store so subscribers run
      this.state = (s) => ({
        selectedStickers: [...s.selectedStickers, stickerData],
      });
    };
    img.src = stickerPath;
  }

  drawSticker(stickerData) {
    const overlay = document.createElement('div');
    overlay.className = 'sticker-overlay';
    overlay.id = stickerData.id;
    overlay.style.left = `${this.config.stickerInitialPosX}px`;
    overlay.style.top = `${this.config.stickerInitialPosY}px`;
    overlay.style.width = `${stickerData.width}px`;
    overlay.style.height = `${stickerData.height}px`;
    overlay.style.backgroundImage = `url(${stickerData.path})`;
    this.editor.container.appendChild(overlay);
  }

  moveSticker(e) {
    if (
      this.state.editorMode === 'menu' ||
      this.state.editorMode === 'shared' ||
      e.target.className !== 'sticker-overlay'
    ) {
      return;
    }
    const stickerId = e.target.id;
    const sticker = this.state.selectedStickers.find((s) => s.id === stickerId);
    const stickerElement = document.getElementById(stickerId);
    if (!sticker || !stickerElement) {
      return;
    }

    const newX =
      e.clientX - this.editor.container.offsetLeft - sticker.width / 2;
    const newY =
      e.clientY - this.editor.container.offsetTop - sticker.height / 2;
    stickerElement.style.left = `${newX}px`;
    stickerElement.style.top = `${newY}px`;
    console.log(`Moved sticker ${stickerId} to (${newX}, ${newY})`);

    // Update the sticker's position in the state
    this.state = (s) => ({
      selectedStickers: s.selectedStickers.map((s) =>
        s.id === stickerId ? { ...s, x: newX, y: newY } : s
      ),
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
}
