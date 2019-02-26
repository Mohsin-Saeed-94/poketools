require('./app');
require('../css/nature_view.scss');
const $ = require('jquery');
require('bootstrap');
require('popper.js');
require('datatables.net-bs4');
require('datatables.net-fixedheader-bs4');
require('datatables-bundle/datatables');

$(document).ready(function () {
    const naturePokemonTable = $('#pkt-nature-view-pokemon');
    const tableSettings = naturePokemonTable.data('table-settings');
    naturePokemonTable.initDataTables(tableSettings);
});
