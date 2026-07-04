import {Controller} from '@hotwired/stimulus';

export default class extends Controller {
  static targets = ['collection'];
  static values = {prototype: String};

  connect() {
    this.index = this.collectionTarget.children.length;
  }

  addItem() {
    const item = document.createElement('div');
    item.innerHTML = this.prototypeValue.replace(/__name__/g, this.index);
    this.collectionTarget.appendChild(item.firstElementChild);
    this.index++;
  }

  removeItem(event) {
    event.target.closest('[data-collection-item]').remove();
  }
}
