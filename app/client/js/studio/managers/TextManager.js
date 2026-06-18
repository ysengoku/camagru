import { studioStore } from '../../store/studioStore';
import { ToolManager } from './ToolManager.js';

export class TextManager extends ToolManager {
  constructor(editor, panel) {
    super(editor, panel);
  }

  isEditing = false;
  isDragging = false;
  hasMoved = false;
  dragStartX = 0;
  dragStartY = 0;
  originX = 0;
  originY = 0;

  get hasTextOverlay() {
    return this.state.textOverlay !== null;
  }

  async init() {
    try {
      const response = await fetch('/api/text-config');
      const textConfig = await response.json();

      this.config.textToolConfig = textConfig;
    } catch (error) {
      console.error('Error fetching text configuration:', error);
    }
    
    this.overlayMask = document.getElementById('overlay-mask');
    this.textPreviewOverlay = document.getElementById('text-overlay');
    this.addTextButton = document.getElementById('text-add-btn');
    this.textInputOverlay = document.getElementById('text-input-overlay');
    this.textInputField = document.getElementById('text-input-field');
    this.confirmButton = document.getElementById('text-input-confirm');
    this.cancelButton = document.getElementById('text-input-cancel');
    this.fontSelect = document.getElementById('text-font');
    this.fontSizeSelect = document.getElementById('text-size');
    this.colorInput = document.getElementById('text-color');
    this.colorSelectButton = document.querySelector('.color-icon-btn');

    this.applyFontsToOptions();
    this.applyColorToToolButton();

    this.addTextButton?.addEventListener('click', () => {
      this.showTextInput();
    });

    this.confirmButton.addEventListener('click', () => {
      this.isEditing ? this.editTextOverlay() : this.addText();
    });
    
    this.cancelButton.addEventListener('click', () => {
      this.hideTextInput();
      this.isEditing = false;
    });
    
    this.setupStoreSubscriptions();
  }

  // ====== Text tools handling ===============================================

  applyFontsToOptions() {
    if (!this.config.textToolConfig?.fonts) {
      return;
    }

    this.fontSelect.querySelectorAll('option').forEach(option => option.style.fontFamily = option.value);
    this.fontSelect.style.fontFamily = this.fontSelect.value;
    this.fontSelect.addEventListener('change', () => {
      this.fontSelect.style.fontFamily = this.fontSelect.value;
    });
  }

  applyColorToToolButton() {
    this.colorSelectButton.style.color = this.config.textToolConfig.defaultColor || '#001919';
    this.colorInput.addEventListener('input', (e) => {
      this.colorSelectButton.style.color = e.target.value;
    });
  }

  showTextInput() {
    if (!this.inEditor) {
      return;
    }
    this.textInputOverlay.classList.remove('display-none');
    this.overlayMask.classList.remove('display-none');
    this.textPreviewOverlay.classList.add('display-none');
    this.textInputField.focus();
  }

  hideTextInput() {
    this.textInputOverlay.classList.add('display-none');
    this.overlayMask.classList.add('display-none');
    this.textPreviewOverlay.classList.remove('display-none');
    this.textInputField.value = '';
  }

  // ====== Text overlay handling =============================================

  addText() {
    if (!this.inEditor || this.hasTextOverlay || !this.textInputField.value.trim()) {
        return;
    }

    const textData = {
      content: this.textInputField.value,
      fontFamily: this.fontSelect.value,
      fontSize: this.fontSizeSelect.value,
      color: this.colorInput.value,
    }

    this.updateTextPreviewElement(textData);

    this.state = (s) => ({
      ...s,
      textOverlay: textData,
    });

    this.bindTextOverlayEvents();
    this.hideTextInput();
  }

  updateTextPreviewElement(textData) {
    this.textPreviewOverlay.textContent = textData.content;
    this.textPreviewOverlay.style.fontFamily = textData.fontFamily;
    this.textPreviewOverlay.style.fontSize = `${textData.fontSize}px`;
    this.textPreviewOverlay.style.color = textData.color;
  }

  removeText() {
    this.textPreviewOverlay.textContent = '';
    this.state = (s) => ({ ...s, textOverlay: null });
  }

  // ====== Edit text methods =================================================

  bindTextOverlayEvents() {
    this.textPreviewOverlay.addEventListener('pointerdown', (e) => this.onTextPointerDown(e));
    this.textPreviewOverlay.addEventListener('pointermove', (e) => this.onTextPointerMove(e));
    this.textPreviewOverlay.addEventListener('pointerup', (e) => this.onTextPointerUp(e));
    // this.editor.container.addEventListener('pointerdown', (e) => this.onContainerPointerDown(e));
  }

  onTextPointerDown(e) {
    e.stopPropagation();
    this.isEditing = true;
    this.isDragging = true;
    this.hasMoved = false;
    this.textPreviewOverlay.setPointerCapture(e.pointerId);

    this.dragStartX = e.clientX;
    this.dragStartY = e.clientY;

    const containerRect = this.editor.container.getBoundingClientRect();
    const elRect = this.textPreviewOverlay.getBoundingClientRect();
    this.originX = elRect.left - containerRect.left;
    this.originY = elRect.top - containerRect.top;
  }

  onTextPointerMove(e) {
    if (!this.isDragging) {
      return;
    }
    
    const deltaX = e.clientX - this.dragStartX;
    const deltaY = e.clientY - this.dragStartY;

    if (Math.abs(deltaX) > 3 || Math.abs(deltaY) > 3) {
      this.hasMoved = true;
    }

    if (this.hasMoved) {
      const newX = this.originX + deltaX;
      const newY = this.originY + deltaY;
      
      this.textPreviewOverlay.style.left = `${newX}px`;
      this.textPreviewOverlay.style.top = `${newY}px`;
    }
  }

  onTextPointerUp(e) {
    if (!this.isDragging) {
      return;
    }
    this.isDragging = false;
    this.textPreviewOverlay.releasePointerCapture(e.pointerId);

    if (!this.hasMoved) {
      this.editTextOverlay();
      return;
    }
    
    const containerRect = this.editor.container.getBoundingClientRect();
    const elRect = this.textPreviewOverlay.getBoundingClientRect();

    this.state = (s) => ({
      ...s,
      textOverlay: {
        ...s.textOverlay,
        x: elRect.left - containerRect.left,
        y: elRect.top - containerRect.top
      },
    });
  }

  editTextOverlay() {
    const textData = this.state.textOverlay;
    if (!textData || !this.isEditing) {
      return;
    }

    this.textInputField.value = textData.content;
    this.showTextInput();

    const onConfirm = () => {
      console.log('Confirm button clicked', this.textInputField.value);
      const newContent = this.textInputField.value.trim();
      const newTextData = {
        content: newContent,
        fontFamily: this.fontSelect.value,
        fontSize: this.fontSizeSelect.value,
        color: this.colorInput.value,
      }
      this.confirmButton.removeEventListener('click', onConfirm);
      this.hideTextInput();
      
      if (!newTextData.content) {
        this.removeText();
        return;
      }
      this.updateTextPreviewElement(newTextData);
      
      this.textPreviewOverlay.textContent = newTextData.content;
      this.state = (s) => ({
        ...s,
        textOverlay: { ...s.textOverlay, ...newTextData },
      });
    };

    this.confirmButton.addEventListener('click', onConfirm);
  }

  // onContainerPointerDown(e) {
  //   if (!e.target.closest('.text-overlay')) {
  //   }
  // }

  // ====== Store subscription methods ========================================

  setupStoreSubscriptions() {
    studioStore.subscribe((newState, prevState) => {
      const prevHasText = !!prevState.textOverlay;
      const newHasText = !!newState.textOverlay;
      if (prevHasText !== newHasText) {
        this.updateAddTextButtonState();
      }
    });
  }

  updateAddTextButtonState() {
    this.addTextButton.disabled = this.hasTextOverlay;
    this.addTextButton.classList.toggle('disabled', this.hasTextOverlay);
  }
}
