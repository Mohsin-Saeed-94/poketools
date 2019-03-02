require('./app');
require('../css/type_view.scss');
const $ = require('jquery');
require('bootstrap');
require('popper.js');
require('datatables.net-bs4');
require('datatables.net-fixedheader-bs4');
require('datatables-bundle/datatables');

$(document).ready(function () {
    const typePokemonTable = $('#pkt-type-view-pokemon');
    const movePokemonTable = $('#pkt-type-view-moves');
    typePokemonTable.initDataTables(typePokemonTable.data('table-settings'));
    movePokemonTable.initDataTables(movePokemonTable.data('table-settings'));
});
