import { studioStore } from '../../store/studioStore';
import { ToolManager } from './ToolManager.js';

export class TextManager extends ToolManager {
  constructor(editor, panel) {
    super(editor, panel);
  }

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
    this.textPreviewContainer = document.getElementById('text-preview-container');
    this.textPreview = document.getElementById('text-preview');
    this.addTextButton = document.getElementById('text-add-btn');
    this.textInputOverlay = document.getElementById('text-input-overlay');
    this.textInputField = document.getElementById('text-input-field');
    this.confirmButton = document.getElementById('text-input-confirm');
    this.cancelButton = document.getElementById('text-input-cancel');
    this.fontSelect = document.getElementById('text-font');
    this.fontSizeSelect = document.getElementById('text-size');
    this.colorInput = document.getElementById('text-color');
    this.colorSelectButton = document.querySelector('.color-icon-btn');
    this.deleteTextButton = document.getElementById('text-delete-btn');

    this.applyFontsToOptions();
    this.applyColorToButton();

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

    this.fontSelect.addEventListener('change', () => {
      this.applyStylesToInput();
    });

    this.fontSizeSelect.addEventListener('change', () => {
      this.applyStylesToInput();
    });

    this.colorInput.addEventListener('input', () => {
      this.applyStylesToInput();
    });

    this.deleteTextButton.addEventListener('click', (e) => {
      console.log('Delete button clicked');
      e.stopPropagation();
      this.removeText();
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

  applyColorToButton() {
    this.colorSelectButton.style.color = this.config.textToolConfig.defaultColor || '#001919';
    this.colorInput.addEventListener('input', (e) => {
      this.colorSelectButton.style.color = e.target.value;
    });
  }

  applyStylesToInput() {
    this.textInputField.style.fontFamily = this.fontSelect.value;
    this.textInputField.style.fontSize = `${this.fontSizeSelect.value}px`;
    this.textInputField.style.color = this.colorInput.value;
  }

  showTextInput() {
    if (!this.inEditor) {
      return;
    }
    this.textInputOverlay.classList.remove('display-none');
    this.overlayMask.classList.remove('display-none');
    this.textPreviewContainer.classList.add('display-none');
    this.applyStylesToInput();
    this.textInputField.focus();
  }

  hideTextInput() {
    this.textInputOverlay.classList.add('display-none');
    this.overlayMask.classList.add('display-none');
    this.textPreviewContainer.classList.remove('display-none');
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
    this.textPreview.textContent = textData.content;
    this.textPreview.style.fontFamily = textData.fontFamily;
    this.textPreview.style.fontSize = `${textData.fontSize}px`;
    this.textPreview.style.color = textData.color;
  }

  removeText() {
    this.textPreview.textContent = '';
    this.textPreviewContainer.classList.remove('overlay-editing');
    if (this.clickTimer) {
      clearTimeout(this.clickTimer);
      this.clickTimer = null;
      this.clickCount = 0;
    }
    this.state = (s) => ({ ...s, textOverlay: null });
  }

  // ====== Edit text methods =================================================

  bindTextOverlayEvents() {
    this.bindMouseInteraction(this.editor.container, this.textPreviewContainer, {
      shouldIgnore: (e) => e.target.closest('#text-delete-btn'),
      onDragMove: ({ target, x, y }) => {
        target.style.left = `${x}px`;
        target.style.top = `${y}px`;
      },
      onDragEnd: ({ target }) => {
        const containerRect = this.editor.container.getBoundingClientRect();
        const elRect = target.getBoundingClientRect();
        this.state = (s) => ({
          ...s,
          textOverlay: { ...s.textOverlay, x: elRect.left - containerRect.left, y: elRect.top - containerRect.top },
        });
      },
      onSingleClick: ({ target }) => target.classList.add('overlay-editing'),
      onDoubleClick: ({ target }) => {
        this.isEditing = true;
        this.editTextOverlay();
        target.classList.add('overlay-editing');
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
      const newTextData = {
        content: this.textInputField.value.trim(),
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
      
      this.textPreview.textContent = newTextData.content;
      this.state = (s) => ({
        ...s,
        textOverlay: { ...s.textOverlay, ...newTextData },
      });
      this.textPreviewContainer.classList.remove('overlay-editing');
    };

    this.confirmButton.addEventListener('click', onConfirm);
  }

  onContainerPointerDown(e) {
    if (!e.target.closest('#text-preview')) {
      this.textPreviewContainer.classList.add('overlay-editing');
    }
  }

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
