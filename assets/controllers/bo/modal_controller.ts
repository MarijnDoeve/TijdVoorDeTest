import { Controller } from '@hotwired/stimulus';
import { Modal } from 'bootstrap';
import { visit } from '@hotwired/turbo';

// Bootstrap's public Modal type doesn't expose `_config` (an internal, but stable, field
// across all 5.x releases) — extend it locally rather than casting to `any` everywhere.
interface ModalWithConfig extends Modal {
    _config: { backdrop: boolean | 'static'; keyboard: boolean };
}

export default class extends Controller {
    static targets = ['modal', 'frame'];

    declare readonly modalTarget: HTMLElement;
    declare readonly frameTarget: HTMLElement;

    _dirty = false;

    open(event: MouseEvent): void {
        event.preventDefault();
        const { src, modalTitle } =
            (event.currentTarget as HTMLElement).dataset;
        if (modalTitle) {
            const titleEl = this.modalTarget.querySelector('.modal-title');
            if (titleEl) titleEl.textContent = modalTitle;
        }
        this.resetDirty();
        this.frameTarget.innerHTML =
            '<div class="modal-body text-center py-4"><div class="spinner-border" role="status"></div></div>';
        this.frameTarget.removeAttribute('src');
        if (src) this.frameTarget.setAttribute('src', src);
        Modal.getOrCreateInstance(this.modalTarget).show();
    }

    frameSubmitEnd(event: CustomEvent<{ success: boolean }>): void {
        if (event.detail.success) {
            Modal.getOrCreateInstance(this.modalTarget).hide();
            visit(window.location.href);
        }
    }

    markDirty(): void {
        if (this._dirty) return;
        this._dirty = true;
        // Using _config instead of preventDefault on hide.bs.modal because we need
        // to block only user-triggered dismissal (backdrop click, Escape key) while
        // keeping programmatic hide() working — frameSubmitEnd() calls hide() after
        // a successful save and must not be blocked. _config.backdrop/keyboard is
        // the correct primitive for that distinction and has been stable across all
        // Bootstrap 5.x releases.
        const modal = Modal.getOrCreateInstance(
            this.modalTarget,
        ) as ModalWithConfig;
        modal._config.backdrop = 'static';
        modal._config.keyboard = false;
    }

    resetDirty(): void {
        this._dirty = false;
        const modal = Modal.getOrCreateInstance(
            this.modalTarget,
        ) as ModalWithConfig;
        modal._config.backdrop = true;
        modal._config.keyboard = true;
    }
}
