
class StudioManager {
    constructor() {
        this.videoStream = null;
        this.selectedOverlays = [];
        this.userPhoto = null;

        this.canvasAspectRatio = null;
        this.overlayPosX = null;
        this.overlayPosY = null;

        this.studioMenu = document.getElementById('studio-menu');
        this.editorContainer = document.getElementById('studio-editor');
        this.video = document.getElementById('webcam');
        this.canvas = document.getElementById('studio-preview');

        this.overlayList = document.getElementById('overlay-list');
        this.scrollLeftBtn = document.querySelector('.scroll-left');
        this.scrollRightBtn = document.querySelector('.scroll-right');
        
        this.captureButton = document.getElementById('capture-button');
        this.uploadButton = document.getElementById('upload-button');
        this.shareButton = document.getElementById('share-button');
        this.resetButton = document.getElementById('reset-button');
        this.captureButtonDisabled = true;

        this.overlayList?.addEventListener('scroll', () => {
            this.updateScrollButtons();
        });
        this.scrollLeftBtn.addEventListener('click', () => this.scroll('left'));
        this.scrollRightBtn.addEventListener('click', () => this.scroll('right'));
    }

    async initWebcam() {
        try {
            this.stream = await navigator.mediaDevices.getUserMedia({ 
                video: { width: this.canvas.width, height: this.canvas.height } 
            });
            this.video.srcObject = this.stream;
            this.videoStream = this.stream;
            this.initCanvas();
            this.editorContainer.classList.remove('display-none');
            this.studioMenu.classList.add('display-none');
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
        this.overlayPosX = cssWidth * 0.25;
        this.overlayPosY = cssHeight * 0.25;
        this.canvasContext = this.canvas.getContext('2d');
    }

    updateCaptureButtonState() {
        if (this.selectedOverlays.length > 0 && this.captureButtonDisabled) {
            this.captureButtonDisabled = false;
            this.captureButton.removeAttribute('disabled');
        } else if (this.selectedOverlays.length === 0 && !this.captureButtonDisabled) {
            this.captureButtonDisabled = true;
            this.captureButton.setAttribute('disabled', 'disabled');
        }
    }

    updateScrollButtons() {
        const scrollLeft = this.overlayList.scrollLeft;
        const scrollWidth = this.overlayList.scrollWidth;
        const clientWidth = this.overlayList.clientWidth;

        scrollLeft === 0
            ? this.scrollLeftBtn.classList.add('opacity-50', 'cursor-not-allowed')
            : this.scrollLeftBtn.classList.remove('opacity-50', 'cursor-not-allowed');

        scrollLeft + clientWidth >= scrollWidth - 10
            ? this.scrollRightBtn.classList.add('opacity-50', 'cursor-not-allowed')
            : this.scrollRightBtn.classList.remove('opacity-50', 'cursor-not-allowed');
    }

    scroll(direction) {
        const scrollAmount = 120;
        
        if (direction === 'left') {
            this.overlayList.scrollBy({ left: -scrollAmount, behavior: 'smooth' });
        } else if (direction === 'right') {
            this.overlayList.scrollBy({ left: scrollAmount, behavior: 'smooth' });
        }

        setTimeout(() => this.updateScrollButtons(), 100);
    }

    selectOverlay(overlayPath) {
        const img = new Image();
        img.onload = () => {
            this.aspectRatio = img.naturalWidth / img.naturalHeight;

            let drawWidth, drawHeight;
            if (this.aspectRatio > this.canvasAspectRatio) {
                drawWidth = this.canvas.width * 0.5;
                drawHeight = (this.canvas.width / this.aspectRatio) * 0.5;
            } else {
                drawHeight = this.canvas.height * 0.5;
                drawWidth = (this.canvas.height * this.aspectRatio) * 0.5;
            }
            this.canvasContext.drawImage(img, this.overlayPosX, this.overlayPosY, drawWidth, drawHeight);
            this.overlayData = {
                path: overlayPath,
                x: this.overlayPosX,
                y: this.overlayPosY,
                width: drawWidth,
                height: drawHeight
            }
            this.selectedOverlays.push(this.overlayData);
        };
        img.src = overlayPath;
        this.updateCaptureButtonState();
    }

    capturePhoto() {

    }

    openUploadModal() {

    }

    sharePhoto() {

    }

    resetStudio() {
        this.shareButton.classList.add('display-none');
        this.resetButton.classList.add('display-none');
        this.captureButton.classList.remove('display-none');
        this.uploadButton.classList.remove('display-none');
        this.selectedOverlays = [];
        this.canvasContext = this.canvas.getContext('2d');
        this.canvasContext.clearRect(0, 0, this.canvas.width, this.canvas.height);
    }

}

this.studio = new StudioManager();
