import { Controller } from '@hotwired/stimulus';
import { Modal } from 'bootstrap';

export default class extends Controller {
    static targets = ['deleteModal'];

    declare readonly deleteModalTarget: HTMLElement;

    deleteElimination(): void {
        Modal.getOrCreateInstance(this.deleteModalTarget).show();
    }
}
