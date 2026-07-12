import { Controller } from '@hotwired/stimulus';
import { Modal, Tooltip } from 'bootstrap';

export default class extends Controller {
    static targets = ['clearModal', 'deleteModal'];

    declare readonly clearModalTarget: HTMLElement;
    declare readonly deleteModalTarget: HTMLElement;

    tooltips: Tooltip[] = [];

    connect(): void {
        this.tooltips = [];
        const tooltipTriggerList = this.element.querySelectorAll(
            '[data-bs-toggle="tooltip"]',
        );
        [...tooltipTriggerList].forEach((tooltipTriggerEl) => {
            this.tooltips.push(Tooltip.getOrCreateInstance(tooltipTriggerEl));
        });
    }

    disconnect(): void {
        this.tooltips.forEach((tooltip) => tooltip.dispose());
    }

    clearQuiz(): void {
        const modal = Modal.getOrCreateInstance(this.clearModalTarget);
        modal.show();
    }

    deleteQuiz(): void {
        const modal = Modal.getOrCreateInstance(this.deleteModalTarget);
        modal.show();
    }
}
