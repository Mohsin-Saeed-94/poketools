const $ = require('jquery');

$(document).ready(function () {
    const images = $('.pkt-imagelist').find('.pkt-imagelist-img');
    let aspects = [];
    images.each(function () {
        // const aspect = this.naturalWidth / this.naturalHeight;
        const aspect = $(this).width() / $(this).height();
        aspects.push(aspect);
    });
    let i = 0;
    for (const aspect of aspects) {
        const img = images[i];
        $(img).wrap(`<div style="flex: ${aspect};"></div>`);
        i++;
    }
});
