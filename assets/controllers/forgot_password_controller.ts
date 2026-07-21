import { Controller } from '@hotwired/stimulus';

export default class extends Controller {
    static targets = ['email', 'link'];

    declare readonly emailTarget: HTMLInputElement;
    declare readonly linkTarget: HTMLAnchorElement;

    connect(): void {
        this.prefill();
    }

    prefill(): void {
        if (!this.emailTarget.value) {
            return;
        }

        const url = new URL(this.linkTarget.href);
        url.searchParams.set('email', this.emailTarget.value);
        this.linkTarget.href = url.href;
    }
}
