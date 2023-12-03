import { ApplicationController, useDebounce } from 'stimulus-use'

export default class extends ApplicationController {

    static targets = ['pagesize', 'sorting', 'category', 'details', 'flagged', 'valueFrom', 'valueTo', 'startDate', 'endDate', 'clearFilters']
    static debounces = ['parameterChanged']
    static values = [
        'flagged'
    ]

    connect() {
        useDebounce(this, { wait: 800 });

        const params = new Proxy(new URLSearchParams(window.location.search), {
            get: (searchParams, prop) => searchParams.get(prop),
        });

        if (params.pagesize) {
            this.pagesizeTargets.forEach(el => el.value = params.pagesize);
        } else {
            this.pagesizeTargets.forEach(el => el.value = '30');
        }

        if (this.hasSortingTarget) {
            this.setTargetValueOrDefault(this.sortingTarget, params.sorting, 'date-desc');
        }
        if (this.hasCategoryTarget) {
            this.setTargetValueOrDefault(this.categoryTarget, params.category);
        }
        if (this.hasDetailsTarget) {
            this.setTargetValueOrDefault(this.detailsTarget, params.details);
        }
        if (this.hasValueFromTarget) {
            this.setTargetValueOrDefault(this.valueFromTarget, params.valueFrom);
        }
        if (this.hasValueToTarget) {
            this.setTargetValueOrDefault(this.valueToTarget, params.valueTo);
        }
        if (this.hasStartDateTarget) {
            this.setTargetValueOrDefault(this.startDateTarget, params.startDate);
        }
        if (this.hasEndDateTarget) {
            this.setTargetValueOrDefault(this.endDateTarget, params.endDate);
        }

        if (params.flagged) {
            this.flaggedValue = true;
            this.flaggedTarget.classList.remove("btn-light");
            this.flaggedTarget.classList.add("btn-danger");
        }

        const searchP = new URLSearchParams(window.location.search);
        if (searchP.size == 0) {
            this.clearFiltersTarget.setAttribute('disabled', '');
        }
    }

    parameterChanged(event) {
        let key = event.target.dataset.pagerTarget;
        let url = new URL(document.location.href);
        url.searchParams.delete('page');
        if (!event.target.value) {
            url.searchParams.delete(key);
        } else {
            url.searchParams.set(key, event.target.value);
        }
        document.location.search = url.search;
    }

    flaggedClicked(event) {
        this.flaggedValue = !this.flaggedValue;
        let key = 'flagged';
        let url = new URL(document.location.href);
        url.searchParams.delete('page');
        if (!this.flaggedValue) {
            url.searchParams.delete(key);
        } else {
            url.searchParams.set(key, this.flaggedValue);
        }
        document.location.search = url.search;
    }

    filterClicked(event) {
        let url = new URL(document.location.href);
        url.searchParams.delete('valueFrom');
        url.searchParams.delete('valueTo');
        url.searchParams.delete('startDate');
        url.searchParams.delete('endDate');

        if (this.valueFromTarget.value) {
            url.searchParams.set('valueFrom', this.valueFromTarget.value);
        }
        if (this.valueToTarget.value) {
            url.searchParams.set('valueTo', this.valueToTarget.value);
        }

        if (this.startDateTarget.value) {
            url.searchParams.set('startDate', this.startDateTarget.value);
        }
        if (this.endDateTarget.value) {
            url.searchParams.set('endDate', this.endDateTarget.value);
        }
        document.location.search = url.search;
    }

    clearFiltersClicked(event) {
        // let url = new URL(document.location.href);
        // url.searchParams = [];
        document.location.search = '';
    }

    setTargetValueOrDefault(target, value, defaultValue = '') {
        if (value) {
            target.value = value;
        } else {
            target.value = defaultValue;
        }
    }
}
