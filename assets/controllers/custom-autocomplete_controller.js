import { Controller } from '@hotwired/stimulus';

// BASED ON https://symfony.com/bundles/ux-autocomplete/current/index.html#extending-tom-select
export default class extends Controller {

    initialize() {
        this._onPreConnect = this._onPreConnect.bind(this);
        this._onConnect = this._onConnect.bind(this);
    }

    connect() {
        this.element.addEventListener('autocomplete:pre-connect', this._onPreConnect);
        this.element.addEventListener('autocomplete:connect', this._onConnect);
    }

    disconnect() {
        // You should always remove listeners when the controller is disconnected to avoid side-effects
        this.element.removeEventListener('autocomplete:pre-connect', this._onConnect);
        this.element.removeEventListener('autocomplete:connect', this._onPreConnect);
    }

    _onPreConnect(event) {
        // TomSelect has not been initialized - options can be changed
        event.detail.options.render = {
            option_create: function (data, escape) {
                return '<div class="create">Dodaj <strong>' + escape(data.input) + '</strong>&hellip;</div>';
            },
            no_results: function (data, escape) {
                return '<div class="no-results">Nieznaleziono wyników dla "' + escape(data.input) + '".</div>';
            },
            no_more_results: function (data, escape) {
                return `<div class="no-more-results">Nie ma więcej wyników.</div>`;
            },
            loading_more: function (data, escape) {
                return `<div class="loading-more">Wczytuję więcej wyników...</div>`;
            }
        }
        // event.detail.options.onChange = this.onChangeHandler;
        event.detail.options.onItemAdd = this.onItemAddHandler;
        event.detail.options.onOptionAdd = this.onOptionAddHandler;
        // event.detail.options.onChange = (value) => {
        //     // ...
        // };
    }

    onChangeHandler(value) {
    }

    onCreate(input) {
    }

    onItemAddHandler(value, item) {
        console.log("ITEM value=[" + value + "], item=[" + item + "]");
    }

    onOptionAddHandler(value, data) {
        console.log("OPTION value=[" + value + "], item=[" + data + "]");
    }

    _onConnect(event) {
        // TomSelect has just been intialized and you can access details from the event
        // console.log(event.detail.tomSelect); // TomSelect instance
        // console.log(event.detail.options); // Options used to initialize TomSelect
    }
}