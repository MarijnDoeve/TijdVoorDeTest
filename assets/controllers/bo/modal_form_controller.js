import {Controller} from '@hotwired/stimulus';
import {Modal} from 'bootstrap';

export default class extends Controller {
  static targets = ['modal', 'modalBody', 'modalFooter'];

  async open(event) {
    event.preventDefault();
    const url = event.currentTarget.dataset.url;
    const title = event.currentTarget.dataset.modalTitle;
    if (title) {
      const titleEl = this.modalTarget.querySelector('.modal-title');
      if (titleEl) titleEl.textContent = title;
    }
    const modal = Modal.getOrCreateInstance(this.modalTarget);
    this.modalBodyTarget.innerHTML = '<div class="text-center py-4"><div class="spinner-border" role="status"></div></div>';
    if (this.hasModalFooterTarget) this.modalFooterTarget.innerHTML = '';
    modal.show();
    await this._loadForm(url);
  }

  async _loadForm(url) {
    const res = await fetch(url, {headers: {'X-Modal-Request': '1'}});
    this.modalBodyTarget.innerHTML = await res.text();
    this._moveFooter();
    this._bindDirty();
    this._bindForm(url);
  }

  _bindDirty() {
    const modal = Modal.getOrCreateInstance(this.modalTarget);
    modal._config.backdrop = true;
    modal._config.keyboard = true;

    const markDirty = () => {
      modal._config.backdrop = 'static';
      modal._config.keyboard = false;
    };

    this.modalBodyTarget.addEventListener('input', markDirty, {once: true});
    this.modalBodyTarget.addEventListener('change', markDirty, {once: true});

    this.modalTarget.addEventListener('hidden.bs.modal', () => {
      modal._config.backdrop = true;
      modal._config.keyboard = true;
    }, {once: true});
  }

  _moveFooter() {
    if (!this.hasModalFooterTarget) return;
    const template = this.modalBodyTarget.querySelector('template[data-modal-footer]');
    this.modalFooterTarget.innerHTML = template ? template.innerHTML : '';
    if (template) template.remove();
  }

  _bindForm(url) {
    const form = this.modalBodyTarget.querySelector('form');
    if (!form) return;

    if (this.hasModalFooterTarget) {
      const saveBtn = this.modalFooterTarget.querySelector('[type="submit"]');
      saveBtn?.addEventListener('click', () => form.requestSubmit(), {once: true});
    }

    form.addEventListener('submit', async (e) => {
      e.preventDefault();
      const res = await fetch(url, {
        method: 'POST',
        headers: {'X-Modal-Request': '1'},
        body: new FormData(form),
      });
      if (res.status === 204) {
        Modal.getOrCreateInstance(this.modalTarget).hide();
        window.location.reload();
      } else {
        this.modalBodyTarget.innerHTML = await res.text();
        this._moveFooter();
        this._bindDirty();
        this._bindForm(url);
      }
    }, {once: true});
  }
}
