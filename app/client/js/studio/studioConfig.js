import { api, endpoints } from '../api.js';

async function fetchStudioConfig() {
  try {
    const response = await api.get(endpoints.STUDIO_CONFIG);
    return response.data;
  } catch (error) {
    console.error('Error loading studio config:', error);
  }
}

const config = await fetchStudioConfig();

console.log('Loaded studio config:', config);

export const studioConfig = {
  ...config,
  canvasAspectRatio: null,
  stickerInitialPosX: null,
  stickerInitialPosY: null,
  maxUploadFileSize: 5 * 1024 * 1024,
  toolMenuItems: [],
  filterItems: [],
  textToolConfig: null,
  doubleClickThreshold: 300, // milliseconds
};
