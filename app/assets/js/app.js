const $ = require('jquery');
require('bootstrap');

$(document).ready(function () {
    const navbarLinks = $('.pkt-navbar-main ul.navbar-nav a.nav-link');
    const versionSelector = $('#pkt-version-select');

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
});
