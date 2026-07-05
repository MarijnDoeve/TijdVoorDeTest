import {Controller} from '@hotwired/stimulus';

export default class extends Controller {
  static targets = ['collection'];
  static values = {prototype: String};

  connect() {
    this.index = this.collectionTarget.children.length;
    this._setupDrag();
  }

  addItem() {
    const item = document.createElement('div');
    item.innerHTML = this.prototypeValue.replace(/__name__/g, this.index);
    const el = item.firstElementChild;
    this.collectionTarget.appendChild(el);
    this._makeDraggable(el);
    this.index++;
  }

  removeItem(event) {
    event.target.closest('[data-collection-item]').remove();
  }

  sortAlphabetically() {
    const items = [...this.collectionTarget.children];
    items.sort((a, b) => {
      const textA = (a.querySelector('input[type="text"]')?.value ?? '').toLowerCase();
      const textB = (b.querySelector('input[type="text"]')?.value ?? '').toLowerCase();
      return textA.localeCompare(textB);
    });
    items.forEach(item => this.collectionTarget.appendChild(item));
    this._syncOrdering();
  }

  randomize() {
    const items = [...this.collectionTarget.children];
    for (let i = items.length - 1; i > 0; i--) {
      const j = Math.floor(Math.random() * (i + 1));
      [items[i], items[j]] = [items[j], items[i]];
    }
    items.forEach(item => this.collectionTarget.appendChild(item));
    this._syncOrdering();
  }

  // — drag-and-drop —

  _setupDrag() {
    [...this.collectionTarget.children].forEach(el => this._makeDraggable(el));
  }

  _makeDraggable(el) {
    const handle = el.querySelector('[data-drag-handle]');
    if (!handle) return;

    handle.setAttribute('draggable', 'true');

    handle.addEventListener('dragstart', (e) => {
      this._dragging = el;
      el.classList.add('opacity-50');
      e.dataTransfer.effectAllowed = 'move';
    });

    handle.addEventListener('dragend', () => {
      this._dragging = null;
      el.classList.remove('opacity-50');
      this.collectionTarget.querySelectorAll('[data-collection-item]').forEach(i => i.classList.remove('border-top', 'border-primary'));
    });

    el.addEventListener('dragover', (e) => {
      e.preventDefault();
      if (!this._dragging || this._dragging === el) return;
      e.dataTransfer.dropEffect = 'move';
      el.classList.add('border-top', 'border-primary');
    });

    el.addEventListener('dragleave', () => {
      el.classList.remove('border-top', 'border-primary');
    });

    el.addEventListener('drop', (e) => {
      e.preventDefault();
      el.classList.remove('border-top', 'border-primary');
      if (!this._dragging || this._dragging === el) return;
      this.collectionTarget.insertBefore(this._dragging, el);
      this._syncOrdering();
    });
  }

  _syncOrdering() {
    [...this.collectionTarget.children].forEach((el, i) => {
      const input = el.querySelector('input[name*="[ordering]"]');
      if (input) input.value = i;
    });
  }
}
