require('./app');
require('../css/move_index.scss');
const $ = require('jquery');
require('bootstrap');
require('popper.js');
require('datatables.net-bs4');
require('datatables.net-fixedheader-bs4');
require('datatables-bundle/datatables');

$(document).ready(function () {
    const moveTable = $('#pkt-move-index-table');
    if (moveTable.length > 0) {
        const tableSettings = moveTable.data('table-settings');
        moveTable.initDataTables(tableSettings);
    }
});
