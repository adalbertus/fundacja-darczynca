// mydropzone_controller.js

import { Controller } from '@hotwired/stimulus';

export default class extends Controller {
    connect() {
        this.element.addEventListener('dropzone:change', this._onChange);
    }

    disconnect() {
        this.element.removeEventListener('dropzone:change', this._onChange);
    }

    _onChange(event) {
        this.fileSubmit.querySelector('div').classList.toggle('d-none');
        this.submit();
    }
}