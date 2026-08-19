import { FilterManager } from './managers/FilterManager';
import { StickerManager } from './managers/StickerManager';
import { TextManager } from './managers/TextManager';
import { studioStore } from '../store/studioStore';
import { studioConfig } from './studioConfig';
import { validateUploadedFile } from './validator';
import { api, endpoints } from '../api';
import { showToast, ToastMessage, ToastType } from '../toast';

class StudioManager {
  static #instance = null;

  constructor() {
    if (StudioManager.#instance) {
      return StudioManager.#instance;
    }
    StudioManager.#instance = this;

    this.videoStream = null;

    this.setDomElReferences();
    this.stickerManager = new StickerManager(
      this.editor,
      this.tool.stickerPanel
    );
    this.textManager = new TextManager(this.editor, this.tool.textPanel);
    this.filterManager = new FilterManager(this.editor, this.tool.filtersPanel);

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
      textPanel: document.getElementById('texttool'),
      filtersPanel: document.getElementById('filters'),
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
    this.editorButtons.share?.addEventListener(
      'click',
      async () => await this.sharePhoto()
    );
    this.editorButtons.reset?.addEventListener('click', () =>
      this.resetCapture()
    );
  }

  initCanvas() {
    this.canvasContext = this.editor.canvas.getContext('2d');
  }

  // Must run only once #studio-editor is actually visible
  measureCanvas() {
    const computedStyle = getComputedStyle(this.editor.canvas);
    const cssWidth = parseInt(computedStyle.width);
    const cssHeight = parseInt(computedStyle.height);
    if (!cssWidth || !cssHeight) {
      return;
    }

    this.editor.canvas.width = cssWidth;
    this.editor.canvas.height = cssHeight;
    studioConfig.canvasAspectRatio = cssWidth / cssHeight;
  }

  initStudioMenu() {
    this.studioMenu.webcamButton?.addEventListener('click', () =>
      this.startWebcam()
    );
    this.studioMenu.uploadInput?.addEventListener('change', async (e) => {
      const file = e.target.files[0];
      e.target.value = '';
      await this.handleFileUpload(file);
    });
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
        id: 'texttool',
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
  }

  initTools() {
    this.initToolMenu();
    this.stickerManager.init();
    this.textManager.init();
    this.filterManager.init();
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
      // Only measure on menu -> editor; re-running mid-edit would wipe the canvas.
      const wasHidden =
        this.editor.container.classList.contains('display-none');
      this.editor.container.classList.remove('display-none');
      this.studioMenu.container.classList.add('display-none');
      this.tool.menu.container.classList.remove('disabled');
      this.tool.container.classList.remove('disabled');
      if (wasHidden) {
        requestAnimationFrame(() =>
          requestAnimationFrame(() => this.measureCanvas())
        );
      }
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
        this.updateShareButtonState();
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
    }
  }

  updateCaptureButtonState() {
    if (this.state.editorMode !== 'webcam') {
      return;
    }
    if (this.editorButtons.capture) {
      this.editorButtons.capture.disabled =
        this.state.selectedStickers.length === 0;
    }
  }

  updateShareButtonState() {
    if (
      this.state.editorMode !== 'captured' &&
      this.state.editorMode !== 'upload'
    ) {
      return;
    }
    if (this.editorButtons.share) {
      this.editorButtons.share.disabled =
        this.state.selectedStickers.length === 0;
    }
  }

  clearEditor() {
    this.clearPhoto();
    this.stickerManager.clearStickers();
    this.textManager.removeText();
    studioStore.setState({
      uploadedImage: {
        offsetX: 0,
        offsetY: 0,
        zoom: 1,
        img: null,
      },
      selectedStickers: [],
      textOverlays: [],
      selectedFilter: 'none',
    });
    this.filterManager.applyFilter('none');
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
        video: true,
      });
      this.editor.video.srcObject = this.stream;
      this.videoStream = this.stream;

      studioStore.setState({ webcamOn: true, editorMode: 'webcam' });
    } catch (error) {
      showToast(ToastType.ERROR, ToastMessage['webcam-access-error']);
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
    const ctx = this.canvasContext;
    const width = this.editor.canvas.width;
    const height = this.editor.canvas.height;

    const video = this.editor.video;
    const videoWidth = video.videoWidth;
    const videoHeight = video.videoHeight;

    const scale = Math.max(width / videoWidth, height / videoHeight);
    const scaledWidth = width / scale;
    const scaledHeight = height / scale;
    const scaledX = (videoWidth - scaledWidth) / 2;
    const scaledY = (videoHeight - scaledHeight) / 2;

    /**
     * scale(-1, 1): Mirror to match #webcam's CSS mirror — drawImage() ignores CSS transforms.
     * drawImage(image, sx, sy, sWidth, sHeight, dx, dy, dWidth, dHeight):
     *  - image: video element
     *  - sx, sy: source x/y (top-left corner of the rectangle to crop from the source image)
     *  - sWidth, sHeight: source width/height (size of the rectangle to crop from the source image)
     *  - dx, dy: destination x/y (top-left corner of the rectangle to draw on the canvas)
     *  - dWidth, dHeight: destination width/height (size of the rectangle to draw on the canvas)
     *  - Here, we crop a centered rectangle from the video and draw it on the canvas.
     */
    ctx.save();
    ctx.translate(width, 0);
    ctx.scale(-1, 1);
    ctx.drawImage(
      video,
      scaledX,
      scaledY,
      scaledWidth,
      scaledHeight,
      0,
      0,
      width,
      height
    );
    ctx.restore();

    this.editor.canvas.toBlob((blob) => {
      if (this.editor.photo.src) {
        URL.revokeObjectURL(this.editor.photo.src);
      }
      this.editor.photo.setAttribute('src', URL.createObjectURL(blob));

      this.stopWebcam();
      studioStore.setState({ editorMode: 'captured' });
    }, 'image/png');

    e.preventDefault();
  }

  clearPhoto() {
    if (this.editor.photo.src) {
      URL.revokeObjectURL(this.editor.photo.src);
    }
    this.editor.photo.removeAttribute('src');
    this.editor.photo.onload = null;
    this.editor.photo.onerror = null;
  }

  resetCapture() {
    this.clearCanvas();
    this.clearPhoto();
    this.stickerManager.clearStickers();
    this.textManager.removeText();
    this.filterManager.applyFilter('none');
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
      showToast(ToastType.ERROR, validationError);
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
        showToast(ToastType.ERROR, 'Failed to load image');
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

    const canvasWidth = this.editor.canvas.width;
    const canvasHeight = this.editor.canvas.height;

    // Fill so that mismatched-ratio uploads don't turn black on JPEG export
    this.canvasContext.fillStyle = '#f2fcfa';
    this.canvasContext.fillRect(0, 0, canvasWidth, canvasHeight);
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
      button.classList.toggle('tool-active', isActive);
      button.setAttribute('aria-selected', isActive);
      panel.classList.toggle('display-none', !isActive);
    });
  }

  // ======== Capture, Share, Reset =====================================

  async sharePhoto() {
    // Convert stored fractions to canvas pixels for the server
    const canvasWidth = this.editor.canvas.width;
    const canvasHeight = this.editor.canvas.height;
    const stickers = this.state.selectedStickers.map((sticker) => ({
      path: sticker.path,
      x: sticker.xFraction * canvasWidth,
      y: sticker.yFraction * canvasHeight,
      width: sticker.widthFraction * canvasWidth,
      height: sticker.heightFraction * canvasHeight,
    }));

    // Same fraction -> pixel conversion as stickers, including fontSize.
    let textOverlay = null;
    if (this.state.textOverlay) {
      const t = this.state.textOverlay;
      textOverlay = {
        content: t.content,
        fontFamily: t.fontFamily,
        color: t.color,
        x: t.xFraction * canvasWidth,
        y: t.yFraction * canvasHeight,
        fontSize: t.fontSizeFraction * canvasHeight,
      };
    }

    const imageBlob = await new Promise((resolve) =>
      this.editor.canvas.toBlob(resolve, 'image/jpeg')
    );
    const finalImageData = new FormData();
    console.log(JSON.stringify(textOverlay));
    finalImageData.append('image', imageBlob, 'photo.jpg');
    finalImageData.append(
      'data',
      JSON.stringify({
        stickers,
        textOverlay,
        filter: this.state.selectedFilter,
      })
    );

    try {
      const response = await api.post(endpoints.PHOTOS, finalImageData);
      if (!response.ok) {
        throw new Error('Failed to share photo');
      }

      const newPhotoHtml = response.data.html;
      const galleryItemsEl = document.getElementById('gallery-items');
      if (galleryItemsEl) {
        galleryItemsEl.querySelector('.no-posts')?.remove();
        galleryItemsEl.insertAdjacentHTML('afterbegin', newPhotoHtml);
        galleryItemsEl.scrollTo({ top: 0, behavior: 'smooth' });
      }
      showToast(ToastType.SUCCESS, 'Photo shared successfully!');
      this.backToMenu();
    } catch (error) {
      showToast(ToastType.ERROR, 'Failed to share photo');
    }
  }

  backToMenu() {
    this.clearWebcam();
    this.clearEditor();
    this.clearCanvas();
    this.textManager.resetTextStyle();
    this.stickerManager.panel.list?.scrollTo({ left: 0, behavior: 'smooth' });
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
