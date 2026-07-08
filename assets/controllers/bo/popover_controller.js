import {Controller} from '@hotwired/stimulus';
import {Popover} from 'bootstrap';

export default class extends Controller {
  connect() {
    this.popovers = [...this.element.querySelectorAll('[data-bs-toggle="popover"]')]
      .map(popoverTriggerEl => Popover.getOrCreateInstance(popoverTriggerEl));
  }

  disconnect() {
    this.popovers.forEach(popover => popover.dispose());
  }
}
