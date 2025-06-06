import {Controller} from '@hotwired/stimulus';

export default class extends Controller {
  next() {
    const currentUrl = window.location.href;
    const urlParts = currentUrl.split('/');
    urlParts.pop();
    window.location.href = urlParts.join('/');
  }
}
