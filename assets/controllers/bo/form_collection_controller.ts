import { Controller } from '@hotwired/stimulus';

export default class extends Controller {
    static targets = ['collection'];
    static values = { prototype: String };

    declare readonly collectionTarget: HTMLElement;
    declare readonly prototypeValue: string;

    index = 0;
    _dragging: Element | null = null;
    _form: HTMLFormElement | null = null;
    _submitHandler: (() => void) | null = null;

    connect(): void {
        this.index = this.collectionTarget.children.length;
        this._syncOrdering();

        if (this.index === 0) {
            this.addItem();
        }

        // `submit` fires on the ancestor <form>, which is outside this controller's
        // subtree. Stimulus data-action only works within the controller element, so
        // addEventListener on the form is the only option here.
        this._form = this.element.closest('form');
        if (this._form) {
            this._submitHandler = () => {
                [...this.collectionTarget.children].forEach((item) => {
                    const input = item.querySelector<HTMLInputElement>(
                        'input[type="text"]',
                    );
                    if (input && input.value.trim() === '') item.remove();
                });
            };
            this._form.addEventListener('submit', this._submitHandler);
        }
    }

    disconnect(): void {
        if (this._form && this._submitHandler) {
            this._form.removeEventListener('submit', this._submitHandler);
        }
    }

    addItem(): void {
        const item = document.createElement('div');
        item.innerHTML = this.prototypeValue.replace(
            /__name__/g,
            String(this.index),
        );
        const el = item.firstElementChild;
        if (el) this.collectionTarget.appendChild(el);
        this.index++;
        this._syncOrdering();
    }

    removeItem(event: Event): void {
        (event.target as HTMLElement).closest('[data-collection-item]')
            ?.remove();
        this._notifyChange();
    }

    sortAlphabetically(): void {
        const items = [...this.collectionTarget.children];
        items.sort((a, b) => {
            const textA =
                (a.querySelector<HTMLInputElement>('input[type="text"]')
                    ?.value ?? '').toLowerCase();
            const textB =
                (b.querySelector<HTMLInputElement>('input[type="text"]')
                    ?.value ?? '').toLowerCase();
            return textA.localeCompare(textB);
        });
        items.forEach((item) => this.collectionTarget.appendChild(item));
        this._syncOrdering();
        this._notifyChange();
    }

    randomize(): void {
        const items = [...this.collectionTarget.children];
        for (let i = items.length - 1; i > 0; i--) {
            const j = Math.floor(Math.random() * (i + 1));
            [items[i], items[j]] = [items[j], items[i]];
        }
        items.forEach((item) => this.collectionTarget.appendChild(item));
        this._syncOrdering();
        this._notifyChange();
    }

    autoExpand(event: Event): void {
        const target = event.target as HTMLInputElement;
        if (target.type !== 'text') return;
        const item = target.closest('[data-collection-item]');
        const last = [...this.collectionTarget.children].at(-1);
        if (item && item === last && target.value.trim() !== '') {
            this.addItem();
        }
    }

    // — drag-and-drop —

    dragStart(event: DragEvent): void {
        this._dragging = (event.currentTarget as HTMLElement).closest(
            '[data-collection-item]',
        );
        this._dragging?.classList.add('opacity-50');
        event.dataTransfer!.effectAllowed = 'move';
    }

    dragEnd(event: DragEvent): void {
        (event.currentTarget as HTMLElement).closest('[data-collection-item]')
            ?.classList.remove('opacity-50');
        this._dragging = null;
        this.collectionTarget.querySelectorAll('[data-collection-item]')
            .forEach((i) =>
                i.classList.remove(
                    'border-top',
                    'border-bottom',
                    'border-primary',
                )
            );
    }

    dragOver(event: DragEvent): void {
        event.preventDefault();
        const el = event.currentTarget as HTMLElement;
        if (!this._dragging || this._dragging === el) return;
        event.dataTransfer!.dropEffect = 'move';
        const rect = el.getBoundingClientRect();
        const isBottom = event.clientY > rect.top + rect.height / 2;
        el.classList.toggle('border-top', !isBottom);
        el.classList.toggle('border-bottom', isBottom);
        el.classList.add('border-primary');
    }

    dragLeave(event: DragEvent): void {
        (event.currentTarget as HTMLElement).classList.remove(
            'border-top',
            'border-bottom',
            'border-primary',
        );
    }

    drop(event: DragEvent): void {
        event.preventDefault();
        const el = event.currentTarget as HTMLElement;
        el.classList.remove('border-top', 'border-bottom', 'border-primary');
        if (!this._dragging || this._dragging === el) return;
        const rect = el.getBoundingClientRect();
        const isBottom = event.clientY > rect.top + rect.height / 2;
        this.collectionTarget.insertBefore(
            this._dragging,
            isBottom ? el.nextSibling : el,
        );
        this._syncOrdering();
        this._notifyChange();
    }

    _notifyChange(): void {
        this.element.dispatchEvent(new Event('change', { bubbles: true }));
    }

    _syncOrdering(): void {
        [...this.collectionTarget.children].forEach((el, i) => {
            const input = el.querySelector<HTMLInputElement>(
                'input[name*="[ordering]"]',
            );
            if (input) input.value = String(i);
        });
    }
}
