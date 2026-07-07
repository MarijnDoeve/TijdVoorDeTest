import {Controller} from '@hotwired/stimulus';
import {Modal} from 'bootstrap';
import {visit} from '@hotwired/turbo';

export default class extends Controller {
  static targets = ['modal', 'frame'];

  open(event) {
    event.preventDefault();
    const {src, modalTitle} = event.currentTarget.dataset;
    if (modalTitle) {
      const titleEl = this.modalTarget.querySelector('.modal-title');
      if (titleEl) titleEl.textContent = modalTitle;
    }
    this.resetDirty();
    this.frameTarget.innerHTML = '<div class="modal-body text-center py-4"><div class="spinner-border" role="status"></div></div>';
    this.frameTarget.removeAttribute('src');
    this.frameTarget.setAttribute('src', src);
    Modal.getOrCreateInstance(this.modalTarget).show();
  }

  frameSubmitEnd(event) {
    if (event.detail.success) {
      Modal.getOrCreateInstance(this.modalTarget).hide();
      visit(window.location.href);
    }
  }

  markDirty() {
    if (this._dirty) return;
    this._dirty = true;
    const modal = Modal.getOrCreateInstance(this.modalTarget);
    modal._config.backdrop = 'static';
    modal._config.keyboard = false;
  }

  resetDirty() {
    this._dirty = false;
    const modal = Modal.getOrCreateInstance(this.modalTarget);
    modal._config.backdrop = true;
    modal._config.keyboard = true;
  }
}
