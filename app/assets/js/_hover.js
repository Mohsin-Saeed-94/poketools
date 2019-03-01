/**
 * jQuery plugin to enable row/column hovering on tables.
 *
 * There is one option: "shade" may be "row", "col", or "both" to set what
 * will be shaded.
 */

(function ($) {
    $.fn.tableHover = function (options) {
        const settings = $.extend({
            'shade': 'both',
        }, options);

        let shadeRow = false;
        let shadeCol = false;
        switch (settings.shade) {
            case 'row':
                shadeRow = true;
                break;
            case 'col':
                shadeCol = true;
                break;
            case 'both':
                shadeRow = true;
                shadeCol = true;
                break;
            default:
                throw `Incorrect cell shading specifier "${settings.shade}", expected "row", "col", or "both".`
        }

        return this.each(function () {
            const table = this;
            const allCells = $(table).find('td, th');
            $(this).find('td, th').hover(function () {
                let otherCells = allCells;
                if (shadeCol) {
                    const columnIndex = this.cellIndex + 1;
                    const matchingColumn = $(table).find(`td:nth-child(${columnIndex}), th:nth-child(${columnIndex})`);
                    matchingColumn.addClass('pkt-hover');
                    otherCells = otherCells.not(matchingColumn);
                }
                if (shadeRow) {
                    const matchingRow = $(this).siblings('th, td');
                    matchingRow.addClass('pkt-hover');
                    otherCells = otherCells.not(matchingRow);
                }
                $(otherCells).removeClass('pkt-hover');
            });
        });
    };
}(require('jquery')));