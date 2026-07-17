import { ToolManager } from './ToolManager.js';

export class FilterManager extends ToolManager {
  constructor(editor, panel) {
    super(editor, panel);
  }

  init() {
    this.config.filterItems = Object.entries(this.config.filters).map(
      ([key, config]) => ({
        button: document.getElementById(`filter-${key}`),
        filter: key,
        filterValue: config.css,
      })
    );

    // Dynamically create CSS classes for filter buttons
    const styleSheet = document.createElement('style');
    Object.entries(this.config.filters).forEach(([key, config]) => {
      styleSheet.textContent += `.filter-${key} { filter: ${config.css}; }\n`;
    });
    document.head.appendChild(styleSheet);

    this.panel?.addEventListener('click', (e) => {
      const filterBtn = e.target.closest('button[data-filter]');
      if (!filterBtn) {
        return;
      }
      const filterName = filterBtn.dataset.filter;
      this.applyFilter(filterName);
    });
  }

  applyFilter(filterName) {
    if (!this.inEditor) {
      return;
    }

    const selectedFilterObj = this.config.filterItems.find(
      (item) => item.filter === filterName
    );

    this.config.filterItems.forEach(({ button, filter }) => {
      const isActive = filter === filterName;
      button.classList.toggle('selected-filter', isActive);
    });

    this.state = (s) => ({ ...s, selectedFilter: filterName });
    const filterValue = selectedFilterObj?.filterValue || 'none';
    this.editor.canvas.style.filter = filterValue;
    this.editor.video.style.filter = filterValue;
  }
}
