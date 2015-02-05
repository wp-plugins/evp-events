$( document ).ready(function() {
    var checkin = $('#report_generic_form_dateFrom').datepicker({
            format: 'yyyy-mm-dd',
            weekStart: 1
    }).on('changeDate', function(ev) {
        if (ev.date.valueOf() > checkout.getDate().valueOf() || checkout.getDate() == 'Invalid Date') {
            var newDate = new Date(ev.date)
            newDate.setDate(newDate.getDate() + 1);
            checkout.setDate(newDate);
            checkout.setStartDate(ev.date);
        }
        checkin.hide();
        $('#report_generic_form_dateTo')[0].focus();
    }).data('datepicker');

    var checkout = $('#report_generic_form_dateTo').datepicker({
        format: 'yyyy-mm-dd',
        weekStart: 1
    }).on('changeDate', function(ev) {
        checkout.hide();
    }).data('datepicker');


// adds report-specific fields to form
    $('#report_generic_form_report').on('change', function() {
        var url = $(this).attr('fields-url') + '/' + $(this).val() + '/' + $('#report_generic_form_event').val();
        $.get(url, function(data) {
            $('.addedGroup').remove();
            var position = $('.form-group').last();
            $(data).insertBefore(position);
        });
    });
});
