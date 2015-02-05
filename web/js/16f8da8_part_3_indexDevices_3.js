$(document).ready(function() {
    $('table.Devices .records tr').each(function(ev) {
        if (!$(ev.target).hasClass('actions')) {
            $(this).on('click', function() {
                $('img', this).fadeToggle();
            });
        }
    });
});
