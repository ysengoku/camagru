import { validateUploadedFile } from './validator';

class StudioManager {
  static #instance = null;

  constructor() {
    if (StudioManager.#instance) {
      return StudioManager.#instance;
    }
    StudioManager.#instance = this;

    this.state = {
      inEditor: false,
      webcamOn: false,
      uploadedImage: {
        offsetX: 0,
        offsetY: 0,
        zoom: 1,
        img: null,
      },
      captureButtonDisabled: true,
      selectedTool: 'stickers',
      selectedStickers: [],
      selectedFilter: 'none',
    };

    this.config = {
      maxUploadFileSize: 5 * 1024 * 1024,
      toolMenuItems: [],
      filterItems: [],
      canvasAspectRatio: null,
      stickerInitialPosX: null,
      stickerInitialPosY: null,
    };

    this.videoStream = null;

    this.studioMenu = {
      container: document.getElementById('studio-menu'),
      webcamButton: document.getElementById('webcam-button'),
      uploadInput: document.getElementById('upload-input'),
    };

    this.editor = {
      container: document.getElementById('studio-editor'),
      video: document.getElementById('webcam'),
      canvas: document.getElementById('studio-preview'),
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

    this.initStudioMenu();
    this.initCanvas();
    this.initTools();
  }

  initCanvas() {
    const computedStyle = getComputedStyle(this.editor.canvas);
    const cssWidth = parseInt(computedStyle.width);
    const cssHeight = parseInt(computedStyle.height);

    this.editor.canvas.width = cssWidth;
    this.editor.canvas.height = cssHeight;
    this.config.canvasAspectRatio = cssWidth / cssHeight;
    this.config.stickerInitialPosX = cssWidth * 0.25;
    this.config.stickerInitialPosY = cssHeight * 0.25;
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
    this.config.toolMenuItems = [
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
      this.updateScrollButtons();
    });
    this.tool.stickers.scrollLeftButton.addEventListener('click', () =>
      this.scroll('left')
    );
    this.tool.stickers.scrollRightButton.addEventListener('click', () =>
      this.scroll('right')
    );
  }

  initStickerTool() {
    this.tool.stickers.list?.addEventListener('click', (e) => {
      const stickerBtn = e.target.closest('button[data-sticker]');
      if (!stickerBtn) {
        return;
      }
      const stickerPath = stickerBtn.dataset.sticker;
      this.selectSticker(stickerPath);
    });
  }

  async initFiltersTool() {
    // Fetch filter definitions from server config
    try {
      const response = await fetch('/api/filters');
      const filters = await response.json();

      this.config.filterItems = Object.entries(filters).map(
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

  // ======== Switch Editor <-> Studio Menu ================================
  toggleEditorView() {
    if (this.state.inEditor) {
      if (this.state.webcamOn) {
        this.stopWebcam();
      }
      this.editor.container.classList.add('display-none');
      this.studioMenu.container.classList.remove('display-none');
      this.tool.container.classList.add('disabled');
      Object.entries(this.editorButtons).forEach(([key, btn]) => btn.classList.add('invisible'));
    } else {
      this.editor.container.classList.remove('display-none');
      this.studioMenu.container.classList.add('display-none');
      this.tool.container.classList.remove('disabled');
      Object.entries(this.editorButtons).forEach(([key, btn]) => btn.classList.remove('invisible'));
    }
    this.state.inEditor = !this.state.inEditor;
  }

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

      this.toggleEditorView();
      this.state.webcamOn = true;
      this.state.inEditor = true;
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
    this.editor.container.classList.remove('display-none');
    this.studioMenu.container.classList.add('display-none');
    this.state.webcamOn = true;
    this.state.inEditor = true;
  }

  stopWebcam() {
    this.editor.video.srcObject = null;
    this.state.webcamOn = false;
  }

  clearWebcam() {
    if (this.videoStream) {
      this.videoStream.getTracks().forEach((track) => track.stop());
      this.editor.video.srcObject = null;
      this.videoStream = null;
    }
    this.state.webcamOn = false;
  }

  hideEditorButtons() {
    this.editorButtons.forEach((btn) => btn.classList.add('invisible'));
  }

  // ======== File Upload Handling =========================================
  async handleFileUpload(file) {
    if (!file) {
      return;
    }

    const validationError = await validateUploadedFile(
      file,
      this.config.maxUploadFileSize
    );
    if (validationError) {
      alert(validationError);
      return;
    }

    this.initCanvas();

    const reader = new FileReader();
    reader.onload = (e) => {
      const img = new Image();
      img.onload = () => {
        this.state.uploadedImage.img = img;
        this.state.uploadedImage.offsetX = 0;
        this.state.uploadedImage.offsetY = 0;
        this.state.uploadedImage.zoom = 1;

        this.redrawUploadedImage();

        this.editor.container.classList.remove('display-none');
        this.studioMenu.container.classList.add('display-none');
        this.state.inEditor = true;
      };
      img.onerror = () => {
        alert('Failed to load image');
      };
      img.src = e.target.result;
    };
    reader.readAsDataURL(file);
  }

  redrawUploadedImage() {
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

  // ======== Tool Handling ================================================
  selectTool(target) {
    if (!this.state.inEditor) {
      return;
    }

    const selectedTool = target.closest('button[data-tool]');
    console.log('Selected:', selectedTool);
    if (!selectedTool) {
      return;
    }

    const tool = selectedTool.dataset.tool;
    console.log('Selected tool:', tool);
    this.state.selectedTool = tool;

    this.config.toolMenuItems.forEach(({ button, panel, id }) => {
      const isActive = id === tool;
      console.log(`Updating tool: ${id}, isActive: ${isActive}`);
      button.classList.toggle('tool-active', isActive);
      button.setAttribute('aria-selected', isActive);
      panel.classList.toggle('display-none', !isActive);
    });
  }

  // ----- Stickers -----
  updateScrollButtons() {
    const scrollLeft = this.tool.stickers.list.scrollLeft;
    const scrollWidth = this.tool.stickers.list.scrollWidth;
    const clientWidth = this.tool.stickers.list.clientWidth;

    scrollLeft === 0
      ? this.tool.stickers.scrollLeftButton.classList.add(
          'opacity-50',
          'cursor-not-allowed'
        )
      : this.tool.stickers.scrollLeftButton.classList.remove(
          'opacity-50',
          'cursor-not-allowed'
        );

    scrollLeft + clientWidth >= scrollWidth - 10
      ? this.tool.stickers.scrollRightButton.classList.add(
          'opacity-50',
          'cursor-not-allowed'
        )
      : this.tool.stickers.scrollRightButton.classList.remove(
          'opacity-50',
          'cursor-not-allowed'
        );
  }

  scroll(direction) {
    const scrollAmount = 120;

    if (direction === 'left') {
      this.tool.stickers.list.scrollBy({
        left: -scrollAmount,
        behavior: 'smooth',
      });
    } else if (direction === 'right') {
      this.tool.stickers.list.scrollBy({
        left: scrollAmount,
        behavior: 'smooth',
      });
    }

    setTimeout(() => this.updateScrollButtons(), 100);
  }

  selectSticker(stickerPath) {
    if (!this.state.inEditor) {
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
      this.canvasContext.drawImage(
        img,
        this.config.stickerInitialPosX,
        this.config.stickerInitialPosY,
        drawWidth,
        drawHeight
      );
      this.stickerData = {
        path: stickerPath,
        x: this.config.stickerInitialPosX,
        y: this.config.stickerInitialPosY,
        width: drawWidth,
        height: drawHeight,
      };
      this.state.selectedStickers.push(this.stickerData);
    };
    img.src = stickerPath;
    this.updateCaptureButtonState();
  }

  // ----- Filters -----
  applyFilter(filterName) {
    if (!this.state.inEditor) {
      return;
    }

    const selectedFilterObj = this.config.filterItems.find(
      (item) => item.filter === filterName
    );

    this.config.filterItems.forEach(({ button, filter }) => {
      const isActive = filter === filterName;
      button.classList.toggle('selected-filter', isActive);
    });

    this.state.selectedFilter = filterName;
    this.editor.canvas.style.filter = selectedFilterObj?.filterValue || 'none';
  }

  // ======== Capture, Share, Reset =======================================
  updateCaptureButtonState() {
    if (
      this.state.selectedStickers.length > 0 &&
      this.state.captureButtonDisabled
    ) {
      this.state.captureButtonDisabled = false;
      this.captureButton.removeAttribute('invisible');
    } else if (
      this.state.selectedStickers.length === 0 &&
      !this.state.captureButtonDisabled
    ) {
      this.state.captureButtonDisabled = true;
      this.captureButton.setAttribute('disabled', 'disabled');
    }
  }

  capture() {
    this.stopWebcam();
  }

  sharePhoto() {

  }

  static getInstance() {
    if (!StudioManager.#instance) {
      StudioManager.#instance = new StudioManager();
    }
    return StudioManager.#instance;
  }
}

StudioManager.getInstance();
