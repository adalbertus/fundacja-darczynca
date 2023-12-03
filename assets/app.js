import './stimulus.js';

import './styles/global.scss';

import * as bootstrap from 'bootstrap';

window.bootstrap = require("bootstrap");

// this waits for Turbo Drive to load
document.addEventListener('turbo:load', function (e) {
    const tooltipTriggerList = document.querySelectorAll('[data-bs-toggle="tooltip"]')
    const tooltipList = [...tooltipTriggerList].map(tooltipTriggerEl => new bootstrap.Tooltip(tooltipTriggerEl, {
        delay: { "show": 700, "hide": 200 }
    }))


    // automatyczne oddanie history.back do każdego elementu z atrybutem data-history-back
    const historyBackTriggerList = document.querySelectorAll('[data-history-back]');
    const historyBackList = [...historyBackTriggerList].map(button => {
        button.addEventListener('click', (event) => {
            event.preventDefault();
            window.history.back(); // This is an alternative fallback for non-Turbo environments.
            // Turbo.visit(window.location.href, { action: 'replace' });
            // Turbo.navigator.history.back();
        });
    })


});