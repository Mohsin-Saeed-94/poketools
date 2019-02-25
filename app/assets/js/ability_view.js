require('./app');
require('../css/ability_view.scss');
const $ = require('jquery');
require('bootstrap');
require('popper.js');
require('datatables.net-bs4');
require('datatables.net-fixedheader-bs4');
require('datatables-bundle/datatables');

$(document).ready(function () {
    const abilityPokemonTable = $('#pkt-ability-view-pokemon');
    const tableSettings = abilityPokemonTable.data('table-settings');
    abilityPokemonTable.initDataTables(tableSettings);
});
