import { StickerManager } from './managers/StickerManager';
import { studioStore } from '../store/studioStore';
import { studioConfig } from './studioConfig';
import { validateUploadedFile } from './validator';

class StudioManager {
  static #instance = null;

  constructor() {
    if (StudioManager.#instance) {
      return StudioManager.#instance;
    }
    StudioManager.#instance = this;

    this.videoStream = null;

    this.setDomElReferences();
    this.stickerManager = new StickerManager(this.editor, this.tool.stickers);

    this.setupStoreSubscriptions();
    this.initStudioMenu();
    this.initCanvas();
    this.initTools();
    this.setEditorButtonsHandlers();
  }

  get state() {
    return studioStore.state;
  }

  setupStoreSubscriptions() {
    studioStore.subscribe((newState, prevState) => {
      console.log('State changed: ', { prevState, newState });

      if (newState.editorMode !== prevState.editorMode) {
        this.updateEditorView(newState.editorMode);
        this.updateEditorButtonsVisibility(newState);
      }

      const prevHasStickers = prevState.selectedStickers.length > 0;
      const newHasStickers = newState.selectedStickers.length > 0;
      if (newHasStickers !== prevHasStickers) {
        this.updateCaptureButtonState();
        this.updateShareButtonState();
      }
    });
  }

  setDomElReferences() {
    this.studioMenu = {
      container: document.getElementById('studio-menu'),
      webcamButton: document.getElementById('webcam-button'),
      uploadInput: document.getElementById('upload-input'),
    };

    this.editor = {
      container: document.getElementById('studio-editor'),
      video: document.getElementById('webcam'),
      canvas: document.getElementById('studio-preview'),
      photo: document.getElementById('studio-image'),
      text: document.getElementById('studio-preview-text'),
    };

    this.tool = {
      container: document.getElementById('studio-tools'),
      menu: {
        container: document.getElementById('studio-tools-menu'),
        stickerButton: document.getElementById('stickers-tool-btn'),
        textButton: document.getElementById('text-tool-btn'),
        filtersButton: document.getElementById('filters-tool-btn'),
      },
      stickerPanel: document.getElementById('stickers'),
      textPanel: document.getElementById('text-tool'),
      filtersPanel: document.getElementById('filters'),
      stickers: {
        list: document.getElementById('sticker-list'),
        scrollLeftButton: document.querySelector('.scroll-left'),
        scrollRightButton: document.querySelector('.scroll-right'),
      },
    };

    this.editorButtons = {
      capture: document.getElementById('capture-button'),
      share: document.getElementById('share-button'),
      reset: document.getElementById('reset-button'),
      backToMenu: document.getElementById('back-to-menu-button'),
    };
  }

  setEditorButtonsHandlers() {
    this.editorButtons.backToMenu?.addEventListener('click', () =>
      this.backToMenu()
    );
    this.editorButtons.capture?.addEventListener('click', (e) =>
      this.capture(e)
    );
    this.editorButtons.share?.addEventListener('click', () =>
      this.sharePhoto()
    );
    this.editorButtons.reset?.addEventListener('click', () =>
      this.resetCapture()
    );
  }

  initCanvas() {
    const computedStyle = getComputedStyle(this.editor.canvas);
    const cssWidth = parseInt(computedStyle.width);
    const cssHeight = parseInt(computedStyle.height);

    this.editor.canvas.width = cssWidth;
    this.editor.canvas.height = cssHeight;
    studioConfig.canvasAspectRatio = cssWidth / cssHeight;
    studioConfig.stickerInitialPosX = cssWidth * 0.25;
    studioConfig.stickerInitialPosY = cssHeight * 0.25;
    this.canvasContext = this.editor.canvas.getContext('2d');
  }

  initStudioMenu() {
    this.studioMenu.webcamButton?.addEventListener('click', () =>
      this.startWebcam()
    );
    this.studioMenu.uploadInput?.addEventListener('change', async (e) =>
      this.handleFileUpload(e.target.files[0])
    );
  }

  initToolMenu() {
    studioConfig.toolMenuItems = [
      {
        button: this.tool.menu.stickerButton,
        panel: this.tool.stickerPanel,
        id: 'stickers',
      },
      {
        button: this.tool.menu.textButton,
        panel: this.tool.textPanel,
        id: 'text-tool',
      },
      {
        button: this.tool.menu.filtersButton,
        panel: this.tool.filtersPanel,
        id: 'filters',
      },
    ];

    this.tool.menu.container?.addEventListener('click', (e) =>
      this.selectTool(e.target)
    );

    this.tool.stickers.list?.addEventListener('scroll', () => {
      this.stickerManager.updateScrollButtons();
    });
    this.tool.stickers.scrollLeftButton.addEventListener('click', () =>
      this.stickerManager.scroll('left')
    );
    this.tool.stickers.scrollRightButton.addEventListener('click', () =>
      this.stickerManager.scroll('right')
    );
  }

  initStickerTool() {
    this.tool.stickers.list?.addEventListener('click', (e) => {
      const stickerBtn = e.target.closest('button[data-sticker]');
      if (!stickerBtn) {
        return;
      }
      const stickerPath = stickerBtn.dataset.sticker;
      this.stickerManager.selectSticker(stickerPath);
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
      if (editingSticker) {
        // editingSticker.classList.remove('sticker-editing');
      }
    });

    this.editor.container?.addEventListener('mousemove', (e) => {
      if (isEditing) {
        this.stickerManager.moveSticker(e);
      }
    });
  }

  async initFiltersTool() {
    // Fetch filter definitions from server config
    try {
      const response = await fetch('/api/filters');
      const filters = await response.json();

      studioConfig.filterItems = Object.entries(filters).map(
        ([key, config]) => ({
          button: document.getElementById(`filter-${key}`),
          filter: key,
          filterValue: config.css,
        })
      );

      // Dynamically create CSS classes for filters
      const styleSheet = document.createElement('style');
      Object.entries(filters).forEach(([key, config]) => {
        styleSheet.textContent += `.filter-${key} { filter: ${config.css}; }\n`;
      });
      document.head.appendChild(styleSheet);
    } catch (error) {
      console.error('Error loading filters:', error);
      // TODO: Show error message to user
      return;
    }

    this.tool.filtersPanel?.addEventListener('click', (e) => {
      const filterBtn = e.target.closest('button[data-filter]');
      if (!filterBtn) {
        return;
      }
      const filterName = filterBtn.dataset.filter;
      this.applyFilter(filterName);
    });
  }

  initTextTool() {}

  async initTools() {
    this.initToolMenu();
    this.initStickerTool();
    await this.initFiltersTool();
    this.initTextTool();
  }

  // ======== UI State Handling =========================================

  updateEditorView(mode) {
    if (mode === 'menu') {
      if (this.state.webcamOn) {
        this.stopWebcam();
      }
      this.editor.container.classList.add('display-none');
      this.studioMenu.container.classList.remove('display-none');
      this.tool.menu.container.classList.add('disabled');
      this.tool.container.classList.add('disabled');
    } else {
      this.editor.container.classList.remove('display-none');
      this.studioMenu.container.classList.add('display-none');
      this.tool.menu.container.classList.remove('disabled');
      this.tool.container.classList.remove('disabled');
    }
  }

  updateEditorButtonsVisibility(state = this.state) {
    switch (state.editorMode) {
      case 'menu':
        Object.values(this.editorButtons).forEach((btn) =>
          btn.classList.add('display-none')
        );
        break;
      case 'webcam':
        this.editorButtons.backToMenu?.classList.remove('display-none');
        this.editorButtons.capture?.classList.remove('display-none');
        this.editorButtons.reset?.classList.add('display-none');
        this.editorButtons.share?.classList.add('display-none');
        this.updateCaptureButtonState();
        break;
      case 'captured':
        this.editorButtons.capture?.classList.add('display-none');
        this.editorButtons.share?.classList.remove('display-none');
        this.editorButtons.reset?.classList.remove('display-none');
        break;
      case 'upload':
        this.editorButtons.backToMenu?.classList.remove('display-none');
        this.editorButtons.capture?.classList.add('display-none');
        this.editorButtons.reset?.classList.add('display-none');
        this.editorButtons.share?.classList.remove('display-none');
        this.updateShareButtonState();
        break;
      case 'shared':
        this.editorButtons.backToMenu?.classList.remove('display-none');
        this.editorButtons.capture?.classList.add('display-none');
        this.editorButtons.reset?.classList.add('display-none');
        this.editorButtons.share?.classList.add('display-none');
        break;
      default:
      // TODO handle error: unknown editor mode
    }
  }

  updateCaptureButtonState() {
    if (this.state.editorMode !== 'webcam') {
      return;
    }
    this.state.selectedStickers.length > 0
      ? this.editorButtons.capture?.classList.remove('disabled')
      : this.editorButtons.capture?.classList.add('disabled');
  }

  updateShareButtonState() {
    if (
      this.state.editorMode !== 'captured' &&
      this.state.editorMode !== 'upload'
    ) {
      return;
    }
    this.state.selectedStickers.length > 0
      ? this.editorButtons.share?.classList.remove('disabled')
      : this.editorButtons.share?.classList.add('disabled');
  }

  clearEditor() {
    this.stickerManager.clearStickers();
    studioStore.setState({
      uploadedImage: {
        offsetX: 0,
        offsetY: 0,
        zoom: 1,
        img: null,
      },
      selectedStickers: [],
      selectedFilter: 'none',
    });
    this.editor.canvas.style.filter = 'none';
  }

  clearCanvas() {
    this.canvasContext.clearRect(
      0,
      0,
      this.editor.canvas.width,
      this.editor.canvas.height
    );
  }

  // ======== Webcam Handling ===========================================

  async initWebcam() {
    try {
      this.stream = await navigator.mediaDevices.getUserMedia({
        video: {
          width: this.editor.canvas.width,
          height: this.editor.canvas.height,
        },
      });
      this.editor.video.srcObject = this.stream;
      this.videoStream = this.stream;

      studioStore.setState({ webcamOn: true, editorMode: 'webcam' });
    } catch (error) {
      console.error('Error accessing webcam:', error);
      // TODO : Show error message to user
    }
  }

  async startWebcam() {
    if (!this.videoStream) {
      await this.initWebcam();
      return;
    }
    this.editor.video.srcObject = this.stream;
    studioStore.setState({ webcamOn: true, editorMode: 'webcam' });
  }

  stopWebcam() {
    this.editor.video.srcObject = null;
    studioStore.setState({ webcamOn: false });
  }

  clearWebcam() {
    if (this.videoStream) {
      this.videoStream.getTracks().forEach((track) => track.stop());
      this.editor.video.srcObject = null;
      this.videoStream = null;
    }
    studioStore.setState({ webcamOn: false });
  }

  capture(e) {
    this.canvasContext.drawImage(
      this.editor.video,
      0,
      0,
      this.editor.canvas.width,
      this.editor.canvas.height
    );

    const data = this.editor.canvas.toDataURL('image/png');
    this.editor.photo.setAttribute('src', data);
    e.preventDefault();

    this.stopWebcam();
    studioStore.setState({ editorMode: 'captured' });
  }

  clearPhoto() {
    this.editor.photo.setAttribute('src', '');
  }

  resetCapture() {
    this.clearPhoto();
    this.stickerManager.clearStickers();
    this.startWebcam();
    studioStore.setState({ editorMode: 'webcam' });
  }

  // ======== File Upload Handling =========================================

  async handleFileUpload(file) {
    const validationError = await validateUploadedFile(
      file,
      studioConfig.maxUploadFileSize
    );
    if (validationError) {
      alert(validationError);
      return;
    }

    const reader = new FileReader();
    reader.onload = (e) => {
      const img = this.editor.photo;
      img.onload = () => {
        studioStore.setState((s) => ({
          uploadedImage: {
            ...s.uploadedImage,
            img,
            offsetX: 0,
            offsetY: 0,
            zoom: 1,
          },
        }));

        this.drawUploadedImage();
      };
      img.onerror = () => {
        alert('Failed to load image');
      };
      img.src = e.target.result;
    };
    reader.readAsDataURL(file);
    studioStore.setState({ editorMode: 'upload' });
  }

  drawUploadedImage() {
    if (!this.state.uploadedImage.img) {
      return;
    }

    const img = this.state.uploadedImage.img;
    const offsetX = this.state.uploadedImage.offsetX;
    const offsetY = this.state.uploadedImage.offsetY;
    const zoom = this.state.uploadedImage.zoom;

    this.canvasContext.clearRect(
      0,
      0,
      this.editor.canvas.width,
      this.editor.canvas.height
    );

    const canvasWidth = this.editor.canvas.width;
    const canvasHeight = this.editor.canvas.height;
    const imgAspectRatio = img.naturalWidth / img.naturalHeight;
    const canvasAspectRatio = canvasWidth / canvasHeight;

    let drawWidth, drawHeight;
    if (imgAspectRatio > canvasAspectRatio) {
      drawWidth = canvasWidth * zoom;
      drawHeight = (canvasWidth / imgAspectRatio) * zoom;
    } else {
      drawHeight = canvasHeight * zoom;
      drawWidth = canvasHeight * imgAspectRatio * zoom;
    }

    const x = (canvasWidth - drawWidth) / 2 + offsetX;
    const y = (canvasHeight - drawHeight) / 2 + offsetY;

    this.canvasContext.drawImage(img, x, y, drawWidth, drawHeight);
  }

  // ======== Tool Handling =============================================

  selectTool(target) {
    if (!this.inEditor) {
      return;
    }

    const selectedTool = target.closest('button[data-tool]');
    if (!selectedTool) {
      return;
    }

    const tool = selectedTool.dataset.tool;
    studioStore.setState({ selectedTool: tool });

    studioConfig.toolMenuItems.forEach(({ button, panel, id }) => {
      const isActive = id === tool;
      console.log(`Updating tool: ${id}, isActive: ${isActive}`);
      button.classList.toggle('tool-active', isActive);
      button.setAttribute('aria-selected', isActive);
      panel.classList.toggle('display-none', !isActive);
    });
  }

  // ----- Filters -----
  applyFilter(filterName) {
    if (!this.inEditor) {
      return;
    }

    const selectedFilterObj = studioConfig.filterItems.find(
      (item) => item.filter === filterName
    );

    studioConfig.filterItems.forEach(({ button, filter }) => {
      const isActive = filter === filterName;
      button.classList.toggle('selected-filter', isActive);
    });

    studioStore.setState({ selectedFilter: filterName });
    this.editor.canvas.style.filter = selectedFilterObj?.filterValue || 'none';
  }

  // ======== Capture, Share, Reset =====================================

  sharePhoto() {}

  backToMenu() {
    this.clearWebcam();
    this.clearEditor();
    this.clearCanvas();
    studioStore.setState({ editorMode: 'menu' });
  }

  // ======== Computed property =========================================

  get inEditor() {
    return this.state.editorMode !== 'menu';
  }

  static getInstance() {
    if (!StudioManager.#instance) {
      StudioManager.#instance = new StudioManager();
    }
    return StudioManager.#instance;
  }
}

StudioManager.getInstance();
