import {Controller} from '@hotwired/stimulus';
import {Tooltip, Modal} from 'bootstrap';

export default class extends Controller {
  static targets = ['clearModal', 'deleteModal'];

  connect() {
    this.tooltips = [];
    const tooltipTriggerList = this.element.querySelectorAll('[data-bs-toggle="tooltip"]');
    [...tooltipTriggerList].forEach(tooltipTriggerEl => {
      this.tooltips.push(Tooltip.getOrCreateInstance(tooltipTriggerEl));
    });
  }

  disconnect() {
    this.tooltips.forEach(tooltip => tooltip.dispose());
  }

  clearQuiz() {
    const modal = Modal.getOrCreateInstance(this.clearModalTarget);
    modal.show();
  }

  deleteQuiz() {
    const modal = Modal.getOrCreateInstance(this.deleteModalTarget);
    modal.show();
  }
}
