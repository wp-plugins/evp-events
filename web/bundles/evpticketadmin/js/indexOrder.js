$(document).ready(function() {
    $('.pagination a.clickable').each(function() {
        $(this).on('click', function() {
            var form = $('form');

            if (form.length > 0) {
                form.attr('action', $(this).attr('href'));
                form.submit();
            } else {
                window.location.href = $(this).attr('href');
            }
        });
    });
});
