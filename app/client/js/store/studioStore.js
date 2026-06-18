import { State } from './State';

export const studioStore = new State({
  webcamOn: false,
  editorMode: 'menu', // 'menu','webcam', 'captured', 'upload', 'shared'
  uploadedImage: {
    offsetX: 0,
    offsetY: 0,
    zoom: 1,
    img: null,
  },
  captureButtonDisabled: true,
  selectedTool: 'stickers',
  selectedStickers: [],
  textOverlay: null,
  selectedFilter: 'none',
});
