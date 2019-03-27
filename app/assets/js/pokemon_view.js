require('./app');
require('../css/pokemon_view.scss');
const $ = require('jquery');
require('bootstrap');
require('popper.js');
require('chart.js');
require('datatables.net-bs4');
require('datatables.net-fixedheader-bs4');
require('datatables-bundle/datatables');
require('orgchart');
const Plyr = require('plyr');
const Masonry = require('masonry-layout/dist/masonry.pkgd');

Chart.defaults.global.animation.duration = 0;
Chart.defaults.global.legend.display = false;

$(document).ready(function () {
    $('[data-toggle="tooltip"]').tooltip();

    // Sprites layout
    new Masonry(document.querySelector('#pkt-pokemon-view-media-sprites .grid'), {
        itemSelector: '.grid-item',
    });

    // Cry audio player
    new Plyr($('.pkt-pokemon-view-media-cry audio'), {
        controls: ['play']
    });

    // Load experience table for growth rate
    const expTable = $('.pkt-pokemon-view-growthrate-experience');
    const expChartCanvas = $('.pkt-pokemon-view-growthrate-chart');
    // const expEndpoint = expTable.data('source');
    const expMap = expTable.data('exp');
    let expLevels = [];
    let expData = [];
    for (let level in expMap) {
        if (!expMap.hasOwnProperty(level)) {
            continue;
        }

        let exp = expMap[level];
        expLevels.push(level);
        expData.push(exp);
    }

    const expChart = new Chart(expChartCanvas[0], {
        type: 'line',
        data: {
            labels: expLevels,
            datasets: [{
                // label: 'Experience',
                data: expData,
                fill: false,
            }],
        },
        options: {
            scales: {
                xAxes: [
                    {
                        scaleLabel: {
                            display: true,
                            labelString: 'Level'
                        }
                    }
                ],
                yAxes: [
                    {
                        scaleLabel: {
                            display: true,
                            labelString: 'Experience'
                        }
                    }
                ]
            }
        }
    });

    const breedingPokemonTable = $('#pkt-pokemon-view-breeding-compatibility-pokemon');
    if (breedingPokemonTable.length > 0) {
        const breedingPokemonTableSettings = breedingPokemonTable.data('table-settings');
        breedingPokemonTable.initDataTables(breedingPokemonTableSettings);
    }
    const heldItemTable = $('#pkt-pokemon-view-helditems');
    if (heldItemTable.length > 0) {
        const heldItemTableSettings = heldItemTable.data('table-settings');
        heldItemTable.initDataTables(heldItemTableSettings);
    }
    const moveTables = $('#pkt-pokemon-view-moves').find('.pkt-datatable-container');
    if (moveTables.length > 0) {
        moveTables.each(function () {
            const moveTableSettings = $(this).data('table-settings');
            $(this).initDataTables(moveTableSettings, {
                // This is necessary because datatable.js doesn't know about column classes
                // until the data is loaded.  This results in the createdCell callbacks below
                // happening before the columns have classes, making the built-in "columnDefs"
                // target resolution useless.  This will find set the callback when the column is known.
                preDrawCallback: function (settings) {
                    for (let column of settings['aoColumns']) {
                        if (column['className'] === 'pkt-move-index-table-type') {
                            column['fnCreatedCell'] = function (cell, cellData, rowData, row, col) {
                                if (!!rowData['stab']) {
                                    $(cell).addClass('pkt-pokemon-view-moves-stab');
                                }
                            };
                        } else if (column['className'] === 'pkt-move-index-table-damageclass') {
                            column['fnCreatedCell'] = function (cell, cellData, rowData, row, col) {
                                if (!!rowData['same_damage_class']) {
                                    $(cell).addClass('pkt-pokemon-view-moves-stab');
                                }
                            }
                        }
                    }
                }
            })
        });
    }

    // Evolution tree
    const evoTree = $('.pkt-pokemon-view-evolution');
    if (evoTree.length > 0) {
        evoTree.orgchart({
            data: evoTree.data('tree'),
            nodeTemplate: data => `${data.html}`
        });
        evoTree.children('.orgchart').addClass('noncollapsable');
    }
});
