$(document).ready(function() {
    switch ($('#admin_parameters_form_type_mailer_transport').val()) {
        case 'smtp':
            $('#admin_parameters_form_type_mailer_host').parent().parent().show();
            $('#admin_parameters_form_type_mailer_user').parent().parent().show();
            $('#admin_parameters_form_type_mailer_password').parent().parent().show();
            $('#admin_parameters_form_type_mailer_auth_mode').parent().parent().show();
            $('#admin_parameters_form_type_mailer_port').parent().parent().show();
            $('#admin_parameters_form_type_mailer_encryption').parent().parent().show();
            break;
        case 'gmail':
            $('#admin_parameters_form_type_mailer_host').parent().parent().hide();
            $('#admin_parameters_form_type_mailer_auth_mode').parent().parent().hide();
            $('#admin_parameters_form_type_mailer_port').parent().parent().hide();
            $('#admin_parameters_form_type_mailer_encryption').parent().parent().hide();
            break;
        default :
            $('#admin_parameters_form_type_mailer_host').parent().parent().hide();
            $('#admin_parameters_form_type_mailer_user').parent().parent().hide();
            $('#admin_parameters_form_type_mailer_password').parent().parent().hide();
            $('#admin_parameters_form_type_mailer_auth_mode').parent().parent().hide();
            $('#admin_parameters_form_type_mailer_port').parent().parent().hide();
            $('#admin_parameters_form_type_buttons_testConnection').hide();
            $('#admin_parameters_form_type_mailer_encryption').parent().parent().hide();
            break;
    }
    $('#admin_parameters_form_type_mailer_transport').on('change', function() {
        switch ($(this).val()) {
            case 'smtp':
                $('#admin_parameters_form_type_mailer_host').parent().parent().fadeIn();
                $('#admin_parameters_form_type_mailer_user').parent().parent().fadeIn();
                $('#admin_parameters_form_type_mailer_password').parent().parent().fadeIn();
                $('#admin_parameters_form_type_mailer_auth_mode').parent().parent().fadeIn();
                $('#admin_parameters_form_type_mailer_port').parent().parent().fadeIn();
                $('#admin_parameters_form_type_mailer_encryption').parent().parent().fadeIn();
                $('#admin_parameters_form_type_buttons_testConnection').show();
                $('#admin_parameters_form_type_buttons_save')
                    .css({'display':'inline', 'width': '49.5%'});
                break;
            case 'gmail':
                $('#admin_parameters_form_type_mailer_host').parent().parent().fadeOut();
                $('#admin_parameters_form_type_mailer_user').parent().parent().fadeIn();
                $('#admin_parameters_form_type_mailer_password').parent().parent().fadeIn();
                $('#admin_parameters_form_type_mailer_auth_mode').parent().parent().fadeOut();
                $('#admin_parameters_form_type_mailer_port').parent().parent().fadeOut();
                $('#admin_parameters_form_type_mailer_encryption').parent().parent().fadeOut();
                $('#admin_parameters_form_type_buttons_testConnection').show();
                $('#admin_parameters_form_type_buttons_save')
                    .css({'display':'inline', 'width': '49.5%'});
                break;
            default :
                $('#admin_parameters_form_type_mailer_host').parent().parent().fadeOut();
                $('#admin_parameters_form_type_mailer_user').parent().parent().fadeOut();
                $('#admin_parameters_form_type_mailer_password').parent().parent().fadeOut();
                $('#admin_parameters_form_type_mailer_auth_mode').parent().parent().fadeOut();
                $('#admin_parameters_form_type_mailer_port').parent().parent().fadeOut();
                $('#admin_parameters_form_type_mailer_encryption').parent().parent().fadeOut();
                $('#admin_parameters_form_type_buttons_testConnection').hide();
                $('#admin_parameters_form_type_buttons_save').css('width', '100%');
                break;
        }
    });


    switch ($('#admin_parameters_form_type_pdf_converter').val()) {
        case 'shell_exec':
            $('#admin_parameters_form_type_pdf_over_http_auth_header').parent().parent().hide();
            break;
        case 'over_http':
            $('#admin_parameters_form_type_pdf_converter').parent().parent().hide();
            $('#admin_parameters_form_type_pdf_over_http_auth_header').parent().parent().show();
    }
    $('#admin_parameters_form_type_pdf_converter').on('change', function() {
        switch ($(this).val()) {
            case 'shell_exec':
                $('#admin_parameters_form_type_pdf_over_http_auth_header').parent().parent().fadeOut();
                break;
            case 'over_http':
                $('#admin_parameters_form_type_pdf_over_http_auth_header').parent().parent().fadeIn();
                break;
        }
    });


    $('#admin_parameters_form_type_buttons_testConnection').on('click', function() {
        var testUrl = $(this).attr('test-url');
        $.ajax({
            type: "POST",
            url: testUrl,
            data: $('form').serialize(),
            success: mail_test_completed
        });
    });

    var mail_test_completed = function(data) {
        var testButton = $('#admin_parameters_form_type_buttons_testConnection');
        if (data == 'SUCCESS') {
            testButton.removeClass('btn btn-danger');
            testButton.addClass('btn btn-success');
        }
        if (data == 'FAILURE') {
            testButton.removeClass('btn btn-success')
            testButton.addClass('btn btn-danger');
        }
    }
});
