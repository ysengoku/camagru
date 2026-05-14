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

    this.studioMenu = document.getElementById('studio-menu');
    this.webcamButton = document.getElementById('webcam-button');
    this.uploadInput = document.getElementById('upload-input');
    this.editorContainer = document.getElementById('studio-editor');
    this.video = document.getElementById('webcam');
    this.canvas = document.getElementById('studio-preview');

    this.toolMenu = document.getElementById('studio-tools-menu');
    this.stickerToolButton = document.getElementById('stickers-tool-btn');
    this.textToolButton = document.getElementById('text-tool-btn');
    this.filtersToolButton = document.getElementById('filters-tool-btn');
    this.stickerTool = document.getElementById('stickers');
    this.textTool = document.getElementById('textTool');
    this.filtersTool = document.getElementById('filters');

    this.stickerList = document.getElementById('sticker-list');
    this.scrollLeftBtn = document.querySelector('.scroll-left');
    this.scrollRightBtn = document.querySelector('.scroll-right');

    this.captureButton = document.getElementById('capture-button');
    this.shareButton = document.getElementById('share-button');
    this.resetButton = document.getElementById('reset-button');

    this.initStudioMenu();
    this.initToolMenu();
    this.initStickerTool();
    this.initFiltersTool();
  }

  initCanvas() {
    // this.initToolMenu();
    // this.initStickerTool();
    // this.initFiltersTool();

    const computedStyle = getComputedStyle(this.canvas);
    const cssWidth = parseInt(computedStyle.width);
    const cssHeight = parseInt(computedStyle.height);

    this.canvas.width = cssWidth;
    this.canvas.height = cssHeight;
    this.config.canvasAspectRatio = cssWidth / cssHeight;
    this.config.stickerInitialPosX = cssWidth * 0.25;
    this.config.stickerInitialPosY = cssHeight * 0.25;
    this.canvasContext = this.canvas.getContext('2d');
  }

  async initWebcam() {
    try {
      this.stream = await navigator.mediaDevices.getUserMedia({
        video: { width: this.canvas.width, height: this.canvas.height },
      });
      this.video.srcObject = this.stream;
      this.videoStream = this.stream;
      this.initCanvas();
      this.editorContainer.classList.remove('display-none');
      this.studioMenu.classList.add('display-none');
      this.state.inEditor = true;
    } catch (error) {
      console.error('Error accessing webcam:', error);
    }
  }

  initStudioMenu() {
    this.webcamButton?.addEventListener('click', () => this.initWebcam());
    this.uploadInput?.addEventListener('change', async (e) =>
      this.handleFileUpload(e.target.files[0])
    );
  }

  initToolMenu() {
    this.config.toolMenuItems = [
      {
        button: this.stickerToolButton,
        panel: this.stickerTool,
        id: 'stickers',
      },
      { button: this.textToolButton, panel: this.textTool, id: 'textTool' },
      {
        button: this.filtersToolButton,
        panel: this.filtersTool,
        id: 'filters',
      },
    ];

    this.toolMenu?.addEventListener('click', (e) => this.selectTool(e.target));

    this.stickerList?.addEventListener('scroll', () => {
      this.updateScrollButtons();
    });
    this.scrollLeftBtn.addEventListener('click', () => this.scroll('left'));
    this.scrollRightBtn.addEventListener('click', () => this.scroll('right'));
  }

  initStickerTool() {
    this.stickerList?.addEventListener('click', (e) => {
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

    this.filtersTool?.addEventListener('click', (e) => {
      const filterBtn = e.target.closest('button[data-filter]');
      if (!filterBtn) {
        return;
      }
      const filterName = filterBtn.dataset.filter;
      this.applyFilter(filterName);
    });
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

        this.editorContainer.classList.remove('display-none');
        this.studioMenu.classList.add('display-none');
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

    this.canvasContext.clearRect(0, 0, this.canvas.width, this.canvas.height);

    const canvasWidth = this.canvas.width;
    const canvasHeight = this.canvas.height;
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
    const scrollLeft = this.stickerList.scrollLeft;
    const scrollWidth = this.stickerList.scrollWidth;
    const clientWidth = this.stickerList.clientWidth;

    scrollLeft === 0
      ? this.scrollLeftBtn.classList.add('opacity-50', 'cursor-not-allowed')
      : this.scrollLeftBtn.classList.remove('opacity-50', 'cursor-not-allowed');

    scrollLeft + clientWidth >= scrollWidth - 10
      ? this.scrollRightBtn.classList.add('opacity-50', 'cursor-not-allowed')
      : this.scrollRightBtn.classList.remove(
          'opacity-50',
          'cursor-not-allowed'
        );
  }

  scroll(direction) {
    const scrollAmount = 120;

    if (direction === 'left') {
      this.stickerList.scrollBy({ left: -scrollAmount, behavior: 'smooth' });
    } else if (direction === 'right') {
      this.stickerList.scrollBy({ left: scrollAmount, behavior: 'smooth' });
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
        drawWidth = this.canvas.width * 0.5;
        drawHeight = (this.canvas.width / aspectRatio) * 0.5;
      } else {
        drawHeight = this.canvas.height * 0.5;
        drawWidth = this.canvas.height * aspectRatio * 0.5;
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
    this.canvas.style.filter = selectedFilterObj?.filterValue || 'none';
  }

  // ======== Capture, Share, Reset =======================================
  updateCaptureButtonState() {
    if (
      this.state.selectedStickers.length > 0 &&
      this.state.captureButtonDisabled
    ) {
      this.state.captureButtonDisabled = false;
      this.captureButton.removeAttribute('disabled');
    } else if (
      this.state.selectedStickers.length === 0 &&
      !this.state.captureButtonDisabled
    ) {
      this.state.captureButtonDisabled = true;
      this.captureButton.setAttribute('disabled', 'disabled');
    }
  }

  capturePhoto() {}

  sharePhoto() {}

  resetEditor() {
    this.shareButton.classList.add('display-none');
    this.resetButton.classList.add('display-none');
    this.captureButton.classList.remove('display-none');
    this.uploadButton.classList.remove('display-none');
    this.state.selectedStickers = [];
    this.canvasContext = this.canvas.getContext('2d');
    this.canvasContext.clearRect(0, 0, this.canvas.width, this.canvas.height);
  }

  resetStudio() {
    if (this.videoStream) {
      this.videoStream.getTracks().forEach((track) => track.stop());
      this.video.srcObject = null;
      this.videoStream = null;
    }
    this.resetEditor();
    this.editorContainer.classList.add('display-none');
    this.studioMenu.classList.remove('display-none');
    this.state.inEditor = false;
  }

  static getInstance() {
    if (!StudioManager.#instance) {
      StudioManager.#instance = new StudioManager();
    }
    return StudioManager.#instance;
  }
}

StudioManager.getInstance();
