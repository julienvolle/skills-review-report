import * as $ from 'jquery';

$(function () {
    $('body').find('button[type="button"]').each(function () {
        if ($(this).data('button')) {
            $(this).on('click', function () {
                let url = $(this).data('button-href');
                let context = $(this).data('button-context').replace(/'/g, '"');
                $.each($.parseJSON(context), function (key, value) {
                    url = url.replace(new RegExp(key,'g'), $(value).val());
                });
                window.open(url, $(this).data('button-target'));
            });
        }
    });
});
