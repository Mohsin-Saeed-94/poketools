const $ = require('jquery');
require('bootstrap');
require('datatables.net');
require('datatables.net-bs4');
require('datatables.net-fixedcolumns-bs4');
require('datatables.net-fixedheader-bs4');
require('datatables.net-scroller-bs4');
require('mathjax/es5/mml-chtml');
require('../css/debug.scss');

$(document).ready(function () {
    // Apply datatables to certain tables
    const defaultSettings = {
        fixedColumns: true,
        scrollX: true,
        paging: false
    };
    const tables = $('[data-tables]')
    tables.each(function () {
        const options = Object.assign(defaultSettings, $(this).data('tables'));
        $(this).DataTable(options);
    });
});
