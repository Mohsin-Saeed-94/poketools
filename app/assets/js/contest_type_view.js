require('./app');
require('../css/contest_type_view.scss');
const $ = require('jquery');
require('bootstrap');
require('popper.js');
require('datatables.net-bs4');
require('datatables.net-fixedheader-bs4');
require('datatables-bundle/datatables');

$(document).ready(function () {
    const moveTable = $('#pkt-type-view-moves');
    moveTable.initDataTables(moveTable.data('table-settings'));
});
