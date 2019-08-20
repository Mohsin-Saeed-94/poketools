require('./app');
require('../css/location_view.scss');
const $ = require('jquery');
require('bootstrap');
require('popper.js');
require('datatables.net-bs4');
require('datatables.net-fixedheader-bs4');
require('datatables.net-rowgroup');
require('datatables-bundle/datatables');

$(document).ready(function () {
    // Encounter tables
    const encounterTables = $('.pkt-location-view-encounters-table .pkt-datatable-container');
    for (let table of encounterTables) {
        const tableSettings = $(table).data('table-settings');
        $(table).initDataTables(tableSettings, {
            rowGroup: {
                dataSrc: 'method',
            }
        }).then(function (dataTable) {
            dataTable.on('draw', function () {
                $('[data-toggle="tooltip"]').tooltip();
            });
        });
    }

    // Shop inventory tables
    const shopTables = $('.pkt-location-view-shops-table .pkt-datatable-container');
    for (let table of shopTables) {
        const tableSettings = $(table).data('table-settings');
        $(table).initDataTables(tableSettings, {
            rowGroup: {
                dataSrc: 'category'
            }
        });
    }
});
