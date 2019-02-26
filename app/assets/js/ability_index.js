require('./app');
require('../css/ability_index.scss');
const $ = require('jquery');
require('bootstrap');
require('popper.js');
require('datatables.net-bs4');
require('datatables.net-fixedheader-bs4');
require('datatables-bundle/datatables');

$(document).ready(function () {
    const abilityTable = $('#pkt-ability-index-table');
    if (abilityTable.length > 0) {
        const tableSettings = abilityTable.data('table-settings');
        abilityTable.initDataTables(tableSettings);
    }
});
