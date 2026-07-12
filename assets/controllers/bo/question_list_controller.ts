import { Controller } from '@hotwired/stimulus';

type Status = 'saving' | 'saved' | 'error';

export default class extends Controller {
    static targets = ['list', 'item', 'status'];
    static values = {
        reorderUrl: String,
        csrf: String,
        savedLabel: String,
        errorLabel: String,
        errorHint: String,
    };

    declare readonly listTarget: HTMLElement;
    declare readonly itemTargets: HTMLElement[];
    declare readonly statusTarget: HTMLElement;
    declare readonly hasStatusTarget: boolean;

    declare readonly reorderUrlValue: string;
    declare readonly csrfValue: string;
    declare readonly savedLabelValue: string;
    declare readonly errorLabelValue: string;
    declare readonly errorHintValue: string;

    _locked = false;
    _dragging: HTMLElement | null = null;
    _placeholder: HTMLDivElement | null = null;

    connect(): void {
        this._locked = false;
    }

    dragStart(event: DragEvent): void {
        const item = (event.currentTarget as HTMLElement).closest<HTMLElement>(
            '[data-bo--question-list-target="item"]',
        );
        this._dragging = item;
        event.dataTransfer!.effectAllowed = 'move';
        setTimeout(() => item?.classList.add('opacity-50'), 0);
    }

    dragEnd(event: DragEvent): void {
        const item = (event.currentTarget as HTMLElement).closest<HTMLElement>(
            '[data-bo--question-list-target="item"]',
        );
        item?.classList.remove('opacity-50');
        this._dragging = null;
        this._removePlaceholder();
    }

    dragOver(event: DragEvent): void {
        event.preventDefault();
        if (!this._dragging) return;
        event.dataTransfer!.dropEffect = 'move';

        const target = (event.target as HTMLElement).closest<HTMLElement>(
            '[data-bo--question-list-target="item"]',
        );
        if (!target || target === this._dragging) return;

        const rect = target.getBoundingClientRect();
        const insertBefore = event.clientY > rect.top + rect.height / 2
            ? target.nextSibling
            : target;

        if (!this._placeholder) {
            this._placeholder = document.createElement('div');
            this._placeholder.className = 'bg-primary rounded mb-2';
            this._placeholder.style.height = '3px';
        }

        if (this._placeholder.nextSibling !== insertBefore) {
            this.listTarget.insertBefore(this._placeholder, insertBefore);
        }
    }

    dragLeave(event: DragEvent): void {
        if (
            !event.relatedTarget ||
            !this.listTarget.contains(event.relatedTarget as Node)
        ) {
            this._removePlaceholder();
        }
    }

    async drop(event: DragEvent): Promise<void> {
        event.preventDefault();
        if (!this._dragging || !this._placeholder || this._locked) return;
        this.listTarget.insertBefore(this._dragging, this._placeholder);
        this._removePlaceholder();
        await this._persistOrder();
    }

    _removePlaceholder(): void {
        if (this._placeholder) {
            this._placeholder.remove();
            this._placeholder = null;
        }
    }

    _setStatus(state: Status): void {
        if (!this.hasStatusTarget) return;
        const el = this.statusTarget;
        el.classList.remove(
            'd-none',
            'text-bg-success',
            'text-bg-danger',
            'text-bg-warning',
        );
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

    async _persistOrder(): Promise<void> {
        this._setStatus('saving');

        const params = new URLSearchParams();
        params.append('_token', this.csrfValue);
        this.itemTargets.forEach((el, i) => {
            params.append('ordering[]', el.dataset.questionId ?? '');
            const numberEl = el.querySelector('[data-question-number]');
            if (numberEl) numberEl.textContent = String(i + 1);
        });

        for (let attempt = 0; attempt < 2; attempt++) {
            try {
                const res = await fetch(this.reorderUrlValue, {
                    method: 'POST',
                    body: params,
                });
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
        alert.innerHTML = `${
            this.errorLabelValue || 'Error saving order'
        } &mdash; ${hint} <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>`;
        this.listTarget.after(alert);
    }
}
