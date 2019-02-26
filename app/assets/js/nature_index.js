require('./app');
require('../css/nature_index.scss');
const $ = require('jquery');
require('bootstrap');
require('popper.js');
require('datatables.net-bs4');
require('datatables.net-fixedheader-bs4');
require('datatables-bundle/datatables');

$(document).ready(function () {
    const natureTable = $('#pkt-nature-index-table');
    const tableSettings = natureTable.data('table-settings');
    natureTable.initDataTables(tableSettings);
});
