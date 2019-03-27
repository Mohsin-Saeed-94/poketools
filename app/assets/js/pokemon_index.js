require('./app');
require('../css/pokemon_index.scss');
const $ = require('jquery');
require('bootstrap');
require('popper.js');
require('datatables.net-bs4');
require('datatables.net-fixedheader-bs4');
require('datatables-bundle/datatables');

$(document).ready(function () {
    const pokemonTable = $('#pkt-pokemon-index-table');
    if (pokemonTable.length > 0) {
        const tableSettings = pokemonTable.data('table-settings');
        pokemonTable.initDataTables(tableSettings);
    }
});
