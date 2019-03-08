require('./app');
require('../css/move_view.scss');
const $ = require('jquery');
require('bootstrap');
require('popper.js');
require('datatables.net-bs4');
require('datatables.net-fixedheader-bs4');
// require('datatables.net-rowgroup');
require('datatables-bundle/datatables');

$(document).ready(function () {
    const tables = $('.pkt-datatable-container');
    for (let table of tables) {
        const tableSettings = $(table).data('table-settings');
        $(table).initDataTables(tableSettings);
    }
});
