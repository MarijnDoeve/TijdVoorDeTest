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
    this._resetDirty();
    this.frameTarget.innerHTML = '<div class="modal-body text-center py-4"><div class="spinner-border" role="status"></div></div>';
    this.frameTarget.removeAttribute('src');
    this.frameTarget.setAttribute('src', src);
    Modal.getOrCreateInstance(this.modalTarget).show();
  }

  frameLoad() {
    this._bindDirty();
  }

  frameSubmitEnd(event) {
    if (event.detail.success) {
      Modal.getOrCreateInstance(this.modalTarget).hide();
      visit(window.location.href);
    }
  }

  _bindDirty() {
    const modal = Modal.getOrCreateInstance(this.modalTarget);
    const markDirty = () => {
      modal._config.backdrop = 'static';
      modal._config.keyboard = false;
    };
    this.frameTarget.addEventListener('input', markDirty, {once: true});
    this.frameTarget.addEventListener('change', markDirty, {once: true});
    this.modalTarget.addEventListener('hidden.bs.modal', () => this._resetDirty(), {once: true});
  }

  _resetDirty() {
    const modal = Modal.getOrCreateInstance(this.modalTarget);
    modal._config.backdrop = true;
    modal._config.keyboard = true;
  }
}
