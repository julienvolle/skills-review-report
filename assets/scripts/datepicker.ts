import * as $ from 'jquery';

$(function(){
    $('[data-bs-toggle="popover"]').popover();
    $('.datepicker').datetimepicker({
        datepicker:true,
        timepicker:true,
        format:'d/m/Y H:00'
    });
});
