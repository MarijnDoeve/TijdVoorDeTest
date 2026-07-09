import {Controller} from '@hotwired/stimulus';

const STORAGE_KEY = 'tvdt-fullscreen';

export default class extends Controller {
  connect() {
    this.onFullscreenChange = this.onFullscreenChange.bind(this);
    document.addEventListener('fullscreenchange', this.onFullscreenChange);
    this.syncState();

    if (sessionStorage.getItem(STORAGE_KEY) === '1' && !document.fullscreenElement) {
      this.request();
    }
  }

  disconnect() {
    document.removeEventListener('fullscreenchange', this.onFullscreenChange);
  }

  toggle() {
    if (document.fullscreenElement) {
      document.exitFullscreen();
    } else {
      this.request();
    }
  }

  request() {
    document.documentElement.requestFullscreen().catch(() => {});
  }

  onFullscreenChange() {
    sessionStorage.setItem(STORAGE_KEY, document.fullscreenElement ? '1' : '0');
    this.syncState();
  }

  syncState() {
    document.documentElement.classList.toggle('is-fullscreen', Boolean(document.fullscreenElement));
  }
}
