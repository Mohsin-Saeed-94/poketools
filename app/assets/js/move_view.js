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
        let defaultSettings = $(table).data('table-settings');
        let customSettings = $(table).data('table-custom');
        if (!customSettings) {
            customSettings = {};
        }
        const tableSettings = Object.assign(defaultSettings, customSettings);
        $(table).initDataTables(tableSettings);
    }
});
