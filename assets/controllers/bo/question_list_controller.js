import {Controller} from '@hotwired/stimulus';

export default class extends Controller {
  static targets = ['list', 'item', 'status'];
  static values = {
    reorderUrl: String,
    csrf: String,
    savedLabel: String,
    errorLabel: String,
    errorHint: String,
  };

  connect() {
    this._locked = false;
  }

  dragStart(event) {
    const item = event.currentTarget.closest('[data-bo--question-list-target="item"]');
    this._dragging = item;
    event.dataTransfer.effectAllowed = 'move';
    setTimeout(() => item.classList.add('opacity-50'), 0);
  }

  dragEnd(event) {
    const item = event.currentTarget.closest('[data-bo--question-list-target="item"]');
    item.classList.remove('opacity-50');
    this._dragging = null;
    this._removePlaceholder();
  }

  dragOver(event) {
    event.preventDefault();
    if (!this._dragging) return;
    event.dataTransfer.dropEffect = 'move';

    const target = event.target.closest('[data-bo--question-list-target="item"]');
    if (!target || target === this._dragging) return;

    const rect = target.getBoundingClientRect();
    const insertBefore = event.clientY > rect.top + rect.height / 2 ? target.nextSibling : target;

    if (!this._placeholder) {
      this._placeholder = document.createElement('div');
      this._placeholder.className = 'bg-primary rounded mb-2';
      this._placeholder.style.height = '3px';
    }

    if (this._placeholder.nextSibling !== insertBefore) {
      this.listTarget.insertBefore(this._placeholder, insertBefore);
    }
  }

  dragLeave(event) {
    if (!event.relatedTarget || !this.listTarget.contains(event.relatedTarget)) {
      this._removePlaceholder();
    }
  }

  async drop(event) {
    event.preventDefault();
    if (!this._dragging || !this._placeholder || this._locked) return;
    this.listTarget.insertBefore(this._dragging, this._placeholder);
    this._removePlaceholder();
    await this._persistOrder();
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

    for (let attempt = 0; attempt < 2; attempt++) {
      try {
        const res = await fetch(this.reorderUrlValue, {method: 'POST', body: params});
        if (res.ok) {
          this._setStatus('saved');
          return;
        }
      } catch {
        // network error — retry on first attempt
      }
    }

    this._locked = true;
    this._setStatus('error');

    const alert = document.createElement('div');
    alert.className = 'alert alert-danger alert-dismissible mt-3';
    alert.setAttribute('role', 'alert');
    const hint = this.errorHintValue || 'Refresh the page to try again.';
    alert.innerHTML = `${this.errorLabelValue || 'Error saving order'} &mdash; ${hint} <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>`;
    this.listTarget.after(alert);
  }
}
