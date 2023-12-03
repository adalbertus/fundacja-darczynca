import { Controller } from '@hotwired/stimulus';
import axios from 'axios';

import { setTargetVisibility } from '../js/common';

export default class extends Controller {

    static targets = ['analyze', 'confirmButton', 'errorHelp']
    static values = {
        analyzeUrl: String,
        url: String,
        errorCount: Number,
        goto: Number,
    }

    connect() {
        if (this.hasConfirmButtonTarget) {
            this.confirmButtonTarget.disabled = this.errorCountValue > 0;
        }
        if (this.hasErrorHelpTarget) {
            if (this.errorCountValue > 0) {
                setTargetVisibility(this.errorHelpTarget);
            } else {
                setTargetVisibility(this.errorHelpTarget, false);
            }
        }

        if (this.hasGotoValue) {
            const rowId = document.querySelector('tr[data-id="' + this.gotoValue + '"]');
            if (rowId) {
                rowId.scrollIntoView({ behavior: "smooth", block: "center", inline: "nearest" });
            }
        }
    }

    analyze(event) {
        // prevent default jest zakomentowany bo po wymuszeniu analizy potrzebny jest reload strony
        event.preventDefault();
        // this.analyzeTarget.classList.toggle("is-loading");
        // this.analyzeTarget.
        let icon = this.analyzeTarget.querySelector('i');
        let spiner = this.analyzeTarget.querySelector('span');
        icon.classList.toggle('d-none');
        spiner.classList.toggle('d-none');

        let analyzeButton = this.analyzeTarget;

        axios.post(this.analyzeUrlValue, {
            force: true,
        })
            .then(function (response) {
                // console.log(response);
            })
            .catch(function (error) {
                console.log(error);
            })
            .finally(function () {
                // analyzeButton.classList.toggle("is-loading");
                icon.classList.toggle('d-none');
                spiner.classList.toggle('d-none');
                window.location.href = analyzeButton.href;
            });
    }
}
