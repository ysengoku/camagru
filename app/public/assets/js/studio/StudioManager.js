class StudioManager {
  constructor() {
    this.state = {
        inEditor: false,
        userPhoto: null,
        captureButtonDisabled : true,
        selectedTool : 'stickers',
        selectedStickers : [],
    }

    this.config = {
        toolMenuItems: [],
    }

    this.videoStream = null;

    this.canvasAspectRatio = null;
    this.stickerInitialPosX = null;
    this.stickerInitialPosY = null;

    this.studioMenu = document.getElementById('studio-menu');
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
    this.uploadButton = document.getElementById('upload-button');
    this.shareButton = document.getElementById('share-button');
    this.resetButton = document.getElementById('reset-button');

    this.initTools();
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

  initCanvas() {
    const computedStyle = getComputedStyle(this.canvas);
    const cssWidth = parseInt(computedStyle.width);
    const cssHeight = parseInt(computedStyle.height);

    this.canvas.width = cssWidth;
    this.canvas.height = cssHeight;
    this.canvasAspectRatio = cssWidth / cssHeight;
    this.stickerInitialPosX = cssWidth * 0.25;
    this.stickerInitialPosY = cssHeight * 0.25;
    this.canvasContext = this.canvas.getContext('2d');
  }

  initTools() {
    this.config.toolMenuItems = [
        { button: this.stickerToolButton, panel: this.stickerTool, id: 'stickers' },
        { button: this.textToolButton, panel: this.textTool, id: 'textTool' },
        { button: this.filtersToolButton, panel: this.filtersTool, id: 'filters' }
    ];

    this.toolMenu?.addEventListener('click', (e) => this.selectTool(e.target));

    this.stickerList?.addEventListener('scroll', () => {
      this.updateScrollButtons();
    });
    this.scrollLeftBtn.addEventListener('click', () => this.scroll('left'));
    this.scrollRightBtn.addEventListener('click', () => this.scroll('right'));
}

  updateCaptureButtonState() {
    if (this.state.selectedStickers.length > 0 && this.state.captureButtonDisabled) {
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

  selectTool(target) {
    if (!this.state.inEditor) {
      return;
    }

    const selectedTool = target.closest('button[data-tool]');
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
          'cursor-not-allowed',
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
      this.aspectRatio = img.naturalWidth / img.naturalHeight;

      let drawWidth, drawHeight;
      if (this.aspectRatio > this.canvasAspectRatio) {
        drawWidth = this.canvas.width * 0.5;
        drawHeight = (this.canvas.width / this.aspectRatio) * 0.5;
      } else {
        drawHeight = this.canvas.height * 0.5;
        drawWidth = this.canvas.height * this.aspectRatio * 0.5;
      }
      this.canvasContext.drawImage(
        img,
        this.stickerInitialPosX,
        this.stickerInitialPosY,
        drawWidth,
        drawHeight,
      );
      this.stickerData = {
        path: stickerPath,
        x: this.stickerInitialPosX,
        y: this.stickerInitialPosY,
        width: drawWidth,
        height: drawHeight,
      };
      this.state.selectedStickers.push(this.stickerData);
    };
    img.src = stickerPath;
    this.updateCaptureButtonState();
  }

  applyFilter(filterName) {
    // (removed) filter application handled elsewhere or reverted
  }

  capturePhoto() {}

  openUploadModal() {}

  sharePhoto() {}

  resetStudio() {
    this.shareButton.classList.add('display-none');
    this.resetButton.classList.add('display-none');
    this.captureButton.classList.remove('display-none');
    this.uploadButton.classList.remove('display-none');
    this.state.selectedStickers = [];
    this.canvasContext = this.canvas.getContext('2d');
    this.canvasContext.clearRect(0, 0, this.canvas.width, this.canvas.height);
  }
}

this.studio = new StudioManager();
