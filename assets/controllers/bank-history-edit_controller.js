import { Controller } from '@hotwired/stimulus';
import axios from 'axios';

import { setTargetVisibility } from '../js/common';

export default class extends Controller {
    static classes = ["is-loading"]

    static targets = ["category", "categorySelect", 'donor', 'memberSelect', 'cancel', 'assignButton']

    static values = {
        avaiableCategoriesUrl: String,
        assignUrl: String,
        allCategories: Object,
    }

    avaiableCategories = Object;

    connect() {
        this.toggleLoading();
        axios.get(this.avaiableCategoriesUrlValue)
            .then((resp) => {
                // let categoryDefault = this.categoryTarget.value;
                // let subCategoryDefault = this.subCategoryTarget.value;
                this.loadAvaiableCategories(resp.data.results[0]);
                this.toggleLoading();
                this.toggleVisibility();
            });
    }

    get selectedCategory() { return this.categorySelectTarget.value; }
    set selectedCategory(category) {
        this.categorySelectTarget.value = category;
        this.categoryTarget.value = category;
    }

    categoryChanged(event) {
        event.preventDefault();
        this.categoryTarget.value = this.selectedCategory;
        this.toggleVisibility();
    }

    toggleLoading() {
        this.categorySelectTarget.parentElement.classList.toggle("is-loading");
    }

    loadAvaiableCategories(categories) {
        if (!categories) {
            return;
        }
        this.avaiableCategories = categories;
        let keys = Object.keys(categories);
        this.categorySelectTarget.innerHTML = "";
        keys.forEach(key => {
            this.categorySelectTarget.innerHTML += "<option value=\"" + key + "\">" + this.allCategoriesValue[key] + "</option>"
        });

        if (keys.includes(this.categoryTarget.value)) {
            this.selectedCategory = this.categoryTarget.value;
        }
    }

    toggleVisibility() {
        switch (this.selectedCategory) {
            case "brak":
            case 'koszty':
            case 'darowizna':
                setTargetVisibility(this.donorTarget, this.selectedCategory == 'darowizna');
                break;
        }
    }

    cancel(event) {
        event.preventDefault();
        window.history.back();
    }

    donorChanged(event) {
        // if (event.srcElement.value) {
        //     this.assignButtonTarget.disabled = false;
        // } else {
        //     this.assignButtonTarget.disabled = true;
        // }
    }

    async assign(event) {
        event.preventDefault();

        let memberId = this.memberSelectTarget.value;
        // zamieniam 0 w url /member/0/assign na właściwy ID
        const url = this.assignUrlValue.replace(/(\/member\/)\d+(\/assign)/, '$1' + memberId + '$2');
        this.assignButtonTarget.classList.toggle("is-loading");
        const res = await axios.patch(url,
            {
                custom_regexp: this.descriptionTarget.value,
            });
        this.assignButtonTarget.classList.toggle("is-loading");

        console.log(res);
    }
}
