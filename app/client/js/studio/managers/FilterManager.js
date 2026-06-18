import { ToolManager } from './ToolManager.js';

export class FilterManager extends ToolManager {
  constructor(editor, panel) {
    super(editor, panel);
  }

  async init() {
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
    this.editor.canvas.style.filter = selectedFilterObj?.filterValue || 'none';
  }
}