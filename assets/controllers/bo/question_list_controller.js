import {Controller} from '@hotwired/stimulus';

export default class extends Controller {
  static targets = ['list', 'item', 'status'];
  static values = {
    reorderUrl: String,
    csrf: String,
    canModify: Boolean,
    savedLabel: String,
    errorLabel: String,
  };

  connect() {
    if (this.canModifyValue) {
      this._setupDrag();
    }
  }

  _setupDrag() {
    this.itemTargets.forEach(el => {
      const handle = el.querySelector('[data-drag-handle]');
      if (!handle) return;

      handle.setAttribute('draggable', 'true');

      handle.addEventListener('dragstart', (e) => {
        this._dragging = el;
        e.dataTransfer.effectAllowed = 'move';
        setTimeout(() => el.classList.add('opacity-50'), 0);
      });

      handle.addEventListener('dragend', () => {
        el.classList.remove('opacity-50');
        this._dragging = null;
        this._removePlaceholder();
      });
    });

    this.listTarget.addEventListener('dragover', (e) => {
      e.preventDefault();
      if (!this._dragging) return;
      e.dataTransfer.dropEffect = 'move';

      const target = e.target.closest('[data-bo--question-list-target="item"]');
      if (!target || target === this._dragging) return;

      const rect = target.getBoundingClientRect();
      const insertBefore = e.clientY > rect.top + rect.height / 2 ? target.nextSibling : target;

      if (!this._placeholder) {
        this._placeholder = document.createElement('div');
        this._placeholder.className = 'bg-primary rounded mb-2';
        this._placeholder.style.height = '3px';
      }

      if (this._placeholder.nextSibling !== insertBefore) {
        this.listTarget.insertBefore(this._placeholder, insertBefore);
      }
    });

    this.listTarget.addEventListener('dragleave', (e) => {
      if (!e.relatedTarget || !this.listTarget.contains(e.relatedTarget)) {
        this._removePlaceholder();
      }
    });

    this.listTarget.addEventListener('drop', async (e) => {
      e.preventDefault();
      if (!this._dragging || !this._placeholder) return;
      this.listTarget.insertBefore(this._dragging, this._placeholder);
      this._removePlaceholder();
      await this._persistOrder();
    });
  }

  _removePlaceholder() {
    if (this._placeholder) {
      this._placeholder.remove();
      this._placeholder = null;
    }
  }

  _setStatus(state) {
    if (!this.hasStatusTarget) return;
    const el = this.statusTarget;
    el.classList.remove('d-none', 'text-bg-success', 'text-bg-danger', 'text-bg-warning');
    if (state === 'saving') {
      el.classList.add('text-bg-warning');
      el.textContent = '…';
    } else if (state === 'saved') {
      el.classList.add('text-bg-success');
      el.textContent = this.savedLabelValue || 'Saved';
    } else if (state === 'error') {
      el.classList.add('text-bg-danger');
      el.textContent = this.errorLabelValue || 'Error';
    }
  }

  async _persistOrder() {
    this._setStatus('saving');

    const params = new URLSearchParams();
    params.append('_token', this.csrfValue);
    this.itemTargets.forEach((el, i) => {
      params.append('ordering[]', el.dataset.questionId);
      const numberEl = el.querySelector('[data-question-number]');
      if (numberEl) numberEl.textContent = String(i + 1);
    });

    try {
      const res = await fetch(this.reorderUrlValue, {method: 'POST', body: params});
      if (res.ok) {
        this._setStatus('saved');
      } else {
        this._setStatus('error');
      }
    } catch {
      this._setStatus('error');
    }
  }
}
