import { Controller } from '@hotwired/stimulus';
import { Popover } from 'bootstrap';

export default class extends Controller {
    popovers: Popover[] = [];

    connect(): void {
        this.popovers = [
            ...this.element.querySelectorAll('[data-bs-toggle="popover"]'),
        ]
            .map((popoverTriggerEl) =>
                Popover.getOrCreateInstance(popoverTriggerEl)
            );
    }

    disconnect(): void {
        this.popovers.forEach((popover) => popover.dispose());
    }
}
