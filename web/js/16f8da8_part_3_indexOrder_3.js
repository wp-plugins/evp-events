$(document).ready(function() {
    $('.pagination a.clickable').each(function() {
        $(this).on('click', function() {
            var form = $('form');
            form.attr('action', $(this).attr('href'));
            form.submit();
        });
    });
});
