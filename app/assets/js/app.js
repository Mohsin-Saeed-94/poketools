const $ = require('jquery');
require('bootstrap');
require('mathjax/es5/mml-chtml');
require('autocomplete.js/dist/autocomplete.jquery');

$(document).ready(function () {
    const navbarLinks = $('.pkt-navbar-main ul.navbar-nav a.nav-link');
    const versionSelector = $('#pkt-version-select');

    // Replace __VERSION__ in navbar links with the actual version slug.
    function updateNavVersionLinks() {
        const selectedVersion = versionSelector.val();

        for (let link of navbarLinks) {
            if (link.dataset.uriTemplate !== undefined) {
                link.href = link.dataset.uriTemplate.replace('__VERSION__', selectedVersion);
            }
        }
    }

    // Version selector value change
    versionSelector.change(function () {
        const selectedVersion = versionSelector.val();

        updateNavVersionLinks();
        if (document.body.dataset.uriTemplate !== undefined) {
            // Redirect to the proper page.
            window.location = document.body.dataset.uriTemplate.replace('__VERSION__', selectedVersion);
        }
    });

    updateNavVersionLinks();

    // Autocomplete
    const searchForm = $('.pkt-form-search input[type=search]');
    const autoCompleteSourceUrl = searchForm.data('autocomplete-source');
    const autocompleteSource = function (query, cb) {
        $.getJSON(autoCompleteSourceUrl, {q: query}, cb);
    };
    searchForm.autocomplete({
        debug: true
    }, [
        {
            source: autocompleteSource,
            debounce: 500,
            templates: {
                suggestion: (suggestion) => suggestion.html,
                empty: () => '<div class="aa-empty">No results found.</div>'
            }
        }
    ]);
});
