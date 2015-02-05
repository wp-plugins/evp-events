$(document).ready(function() {
    $('#evp_bundle_ticketbundle_template_type').each(function() {
        $('#evp_bundle_ticketbundle_template_name').prop('disabled', true);
        $(this).on('change', function() {
            if ($('#evp_bundle_ticketbundle_template_type option:selected').val() == 'custom') {
                $('#evp_bundle_ticketbundle_template_name').prop('disabled', false);
            } else {
                $('#evp_bundle_ticketbundle_template_name').prop('disabled', true);
            }
        });
    });
});
