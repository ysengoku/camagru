import { studioStore } from '../../store/studioStore.js';
import { studioConfig } from '../studioConfig.js';

export class StickerManager {
  constructor(editor, stickerPanel) {
    this.editor = editor;
    this.stickerPanel = stickerPanel;
  }

  get state() {
    return studioStore.state;
  }

  set state(newState) {
    studioStore.setState(newState);
  }

  get inEditor() {
    return this.state.editorMode !== 'menu';
  }

  updateScrollButtons() {
    const scrollLeft = this.stickerPanel.list.scrollLeft;
    const scrollWidth = this.stickerPanel.list.scrollWidth;
    const clientWidth = this.stickerPanel.list.clientWidth;

    scrollLeft === 0
      ? this.stickerPanel.scrollLeftButton.classList.add(
          'opacity-50',
          'cursor-not-allowed'
        )
      : this.stickerPanel.scrollLeftButton.classList.remove(
          'opacity-50',
          'cursor-not-allowed'
        );

    scrollLeft + clientWidth >= scrollWidth - 10
      ? this.stickerPanel.scrollRightButton.classList.add(
          'opacity-50',
          'cursor-not-allowed'
        )
      : this.stickerPanel.scrollRightButton.classList.remove(
          'opacity-50',
          'cursor-not-allowed'
        );
  }

  scroll(direction) {
    const scrollAmount = 120;

    if (direction === 'left') {
      this.stickerPanel.list.scrollBy({
        left: -scrollAmount,
        behavior: 'smooth',
      });
    } else if (direction === 'right') {
      this.stickerPanel.list.scrollBy({
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
      if (aspectRatio > studioConfig.canvasAspectRatio) {
        drawWidth = this.editor.canvas.width * 0.5;
        drawHeight = (this.editor.canvas.width / aspectRatio) * 0.5;
      } else {
        drawHeight = this.editor.canvas.height * 0.5;
        drawWidth = this.editor.canvas.height * aspectRatio * 0.5;
      }

      const stickerData = {
        id: `sticker-${Date.now()}`,
        path: stickerPath,
        x: studioConfig.stickerInitialPosX,
        y: studioConfig.stickerInitialPosY,
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
    overlay.style.left = `${studioConfig.stickerInitialPosX}px`;
    overlay.style.top = `${studioConfig.stickerInitialPosY}px`;
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
