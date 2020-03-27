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
            // Setup tooltip display
            dataTable.on('draw', function () {
                $('[data-toggle="tooltip"]').tooltip();
            });

            // Add the encounter note as an extra row
            dataTable.on('init.dt', function (e, settings, data) {
                for (let row of data.data) {
                    if (!row['note']) {
                        continue;
                    }

                    const rowId = row['DT_RowId'];
                    const encounterTable = e.target;
                    const encounterRow = encounterTable.rows.namedItem(rowId);
                    const encounterRowIndex = encounterRow.rowIndex;
                    const noteRow = encounterTable.insertRow(encounterRowIndex + 1);
                    noteRow.setAttribute('role', encounterRow.getAttribute('role'));
                    noteRow.className = encounterRow.className;
                    const noteCell = noteRow.insertCell();
                    noteCell.colSpan = encounterRow.cells.length;
                    noteCell.className = 'pkt-encounter-table-note';
                    noteCell.innerHTML = row['note'];
                }
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
