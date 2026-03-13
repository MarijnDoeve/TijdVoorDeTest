import {Controller} from '@hotwired/stimulus';

export default class extends Controller {
  next() {
    const currentUrl = new URL(window.location.href);
    const pathParts = currentUrl.pathname.split('/');
    // Remove the last segment
    pathParts.pop();
    // Update the pathname
    currentUrl.pathname = pathParts.join('/');
    // Navigate
    window.location.href = currentUrl.href;
  }
}
