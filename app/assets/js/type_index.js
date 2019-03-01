require('./app');
require('../css/type_index.scss');
const $ = require('jquery');
require('bootstrap');
require('popper.js');
require('./_hover');

$(document).ready(function () {
    $('table.pkt-type-index-typechart').tableHover();
});