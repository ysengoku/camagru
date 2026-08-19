import { ToolManager } from './ToolManager.js';

export class TextManager extends ToolManager {
  constructor(editor, panel) {
    super(editor, panel);
  }

  get hasTextOverlay() {
    return this.state.textOverlay !== null;
  }

  init() {
    this.overlayMask = document.getElementById('overlay-mask');
    this.textPreviewContainer = document.getElementById(
      'text-preview-container'
    );
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

    this.applyStylesToToolMenu();

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
      this.textInputField.style.fontFamily = this.fontSelect.value;
      this.fontSelect.style.fontFamily = this.fontSelect.value;
    });

    this.fontSizeSelect.addEventListener('change', () => {
      this.textInputField.style.fontSize = `${this.fontSizeSelect.value}px`;
    });

    this.colorInput.addEventListener('input', () => {
      this.textInputField.style.color = this.colorInput.value;
      this.colorSelectButton.style.color = this.colorInput.value;
    });

    this.deleteTextButton.addEventListener('click', (e) => {
      e.stopPropagation();
      this.removeText();
    });

    this.setupStoreSubscriptions();
    this.setupResizeObserver();
  }

  // Position is stored as a fraction (0-1) of the editor
  // so that it stays correctly placed as the responsive layout resizes.
  setupResizeObserver() {
    const observer = new ResizeObserver(() => this.repositionText());
    observer.observe(this.editor.container);
  }

  repositionText() {
    if (!this.hasTextOverlay) {
      return;
    }
    this.applyTextGeometry(this.state.textOverlay);
  }

  applyTextGeometry(textData) {
    const rect = this.editor.container.getBoundingClientRect();
    this.textPreviewContainer.style.left = `${textData.xFraction * rect.width}px`;
    this.textPreviewContainer.style.top = `${textData.yFraction * rect.height}px`;
  }

  // ====== Text tools handling ===============================================

  applyStylesToToolMenu() {
    this.fontSelect
      .querySelectorAll('option')
      .forEach((option) => (option.style.fontFamily = option.value));
    this.fontSelect.style.fontFamily = this.fontSelect.value;
    this.colorSelectButton.style.color =
      this.config.text.defaultColor || '#001919';
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
    if (
      !this.inEditor ||
      this.hasTextOverlay ||
      !this.textInputField.value.trim()
    ) {
      return;
    }

    const textData = {
      content: this.textInputField.value,
      fontFamily: this.fontSelect.value,
      fontSize: this.fontSizeSelect.value,
      color: this.colorInput.value,
    };

    this.updateTextPreviewElement(textData);
    this.hideTextInput();

    // Converts the initial CSS-centered position into a fraction
    const containerRect = this.editor.container.getBoundingClientRect();
    const previewRect = this.textPreviewContainer.getBoundingClientRect();
    const xFraction =
      (previewRect.left - containerRect.left) / containerRect.width;
    const yFraction =
      (previewRect.top - containerRect.top) / containerRect.height;
    const fontSizeFraction = textData.fontSize / containerRect.height;

    this.textPreviewContainer.style.transform = 'none';

    this.state = (s) => ({
      ...s,
      textOverlay: {
        ...textData,
        xFraction,
        yFraction,
        fontSizeFraction,
      },
    });

    this.applyTextGeometry(this.state.textOverlay);
    this.bindTextOverlayEvents();
  }

  updateTextPreviewElement(textData) {
    this.textPreview.textContent = textData.content;
    this.textPreview.style.fontFamily = textData.fontFamily;
    this.textPreview.style.fontSize = `${textData.fontSize}px`;
    this.textPreview.style.color = textData.color;
  }

  resetTextStyle() {
    this.fontSelect.value = this.config.text.defaultFont;
    this.fontSelect.style.fontFamily = this.config.text.defaultFont;
    this.fontSizeSelect.value = this.config.text.defaultSize;
    this.colorInput.value = this.config.text.defaultColor;
    this.colorSelectButton.style.color = this.config.text.defaultColor;

    this.textPreviewContainer.style.left = '';
    this.textPreviewContainer.style.top = '';
    this.textPreviewContainer.style.transform = '';
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
    this.bindMouseInteraction(
      this.editor.container,
      this.textPreviewContainer,
      {
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
            textOverlay: {
              ...s.textOverlay,
              xFraction:
                (elRect.left - containerRect.left) / containerRect.width,
              yFraction:
                (elRect.top - containerRect.top) / containerRect.height,
            },
          });
        },
        onSingleClick: ({ target }) => {
          target.classList.add('overlay-editing');
        },
        onDoubleClick: ({ target }) => {
          this.isEditing = true;
          this.editTextOverlay();
          target.classList.add('overlay-editing');
        },
      }
    );

    this.editor.container.addEventListener('pointerdown', (e) => {
      if (!e.target.closest('#text-preview-container')) {
        this.isEditing = false;
        this.textPreviewContainer.classList.remove('overlay-editing');
      }
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
      const containerRect = this.editor.container.getBoundingClientRect();
      const newTextData = {
        content: this.textInputField.value.trim(),
        fontFamily: this.fontSelect.value,
        fontSize: this.fontSizeSelect.value,
        fontSizeFraction: this.fontSizeSelect.value / containerRect.height,
        color: this.colorInput.value,
      };
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

  // ====== Store subscription methods ========================================

  setupStoreSubscriptions() {
    this.store.subscribe((newState, prevState) => {
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
