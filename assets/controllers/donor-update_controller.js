// assets/controllers/donor-update_controller.js

import { Controller } from '@hotwired/stimulus';

export default class extends Controller {
    static targets = ["searchPatternsContainer", "searchPatternTemplate", "searchPatternInputTemplate"];

    static values = {
        index: Number,
    }

    remove(event) {
        event.preventDefault();

        event.target.parentElement.remove();
    }

    add(event) {
        event.preventDefault();
        // wygenerowanie input dla nowego search pattern
        let tpl = document.createElement('div');
        tpl.innerHTML = this.searchPatternInputTemplateTarget.content.querySelector('input').outerHTML.replace(/__name__/g, this.indexValue);
        let input = tpl.querySelector('input');

        let searchPattern = this.searchPatternTemplateTarget.content.cloneNode(true);
        let inputPlaceholder = searchPattern.querySelector('input');
        inputPlaceholder.replaceWith(input);
        // searchPattern.querySelector('button').setAttribute('data-pattern-id', this.indexValue);
        this.searchPatternsContainerTarget.appendChild(searchPattern);
        this.indexValue++;
    }
}