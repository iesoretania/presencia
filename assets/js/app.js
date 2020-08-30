require('../css/app.scss');

const $ = require('jquery');
global.$ = global.jQuery = $;

require('bootstrap');
require('select2');
require('select2/dist/js/i18n/es');
require('@fortawesome/fontawesome-free');

$(document).ready(function() {
    $('select').select2({
        theme: "bootstrap",
        language: "es"
    });
    $('[data-toggle="popover"]').popover();

    $('.leftmenutrigger').on('click', function (e) {
        $('.side-nav').toggleClass("open");
        $('#wrapper').toggleClass("open");
        e.preventDefault();
    });
});
