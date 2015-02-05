function confirmDelRow(url, id, message)
{
    if (confirm(message)) {
        $.ajax({
            type: "GET",
            url: url
        });
        $('#tr_' + id).hide('slow');
    }
}

function loadFieldTypeValidators(id, url)
{
   var fieldType = $('#' + id).val();
   var url = url + fieldType;

   $.get(url, { fieldType : fieldType })
        .done(
            function(data) {
               $('#evp_bundle_ticketbundle_field_schema_validator').empty();
               $('#evp_bundle_ticketbundle_field_schema_validator').val(data);
            }
        );
}


function loadServiceResponse(url, id, serviceMethodKey, targetFieldId)
{
    if (id == '') {
        $('#' + targetFieldId).val('');
        $('#' + targetFieldId).html('');
    } else {
        var url = url + '/' + id + '/' + serviceMethodKey;

        $.get(url, {} ).done(
            function(data) {
                var jsonObj = JSON.parse(data);
                if (jsonObj.type == 'collection') {
                    $('#' + targetFieldId).html('');
                    jQuery.each(jsonObj.data, function(key, val) {
                        var elem = $('<option>').val(key).text(val)
                        elem.appendTo('#' + targetFieldId);
                    });
                } else {
                    $('#' + targetFieldId).val(jsonObj.data);
                }
            }
        );
    }
}
function removeClassSVG(obj, remove) {
    var classes = $(obj).attr('class');
    var index = classes.search(remove);

    if (index == -1) {
        return false;
    }
    else {
        classes = classes.substring(0, index) + classes.substring((index + remove.length), classes.length);
        $(obj).attr('class', classes);

        return true;
    }
}
function addClassSVG(obj, add) {
    var classes = $(obj).attr('class');
    classes = classes + ' ' + add;
    $(obj).attr('class', classes);
}
function hasClassSVG(obj, has) {
    var classes = $(obj).attr('class');
    var index = classes.search(has);

    return !(index == -1)
}

function refreshEntityBasedOnTargetLocale(url, locale, targetEntity)
{
    url = url + '/' + locale;

    $.get(url, {} ).done(
        function(data) {
            var jsonObj = JSON.parse(data);
            jQuery.each(jsonObj, function(key, val) {
                $('#' + targetEntity + '_' + key).val(val).blur();
            });
            $('textarea.ckeditor').each(function () {
                var $textarea = $(this);
                CKEDITOR.instances[$textarea.attr('id')].setData($textarea.val());
            });
        }
    );
}

$(document).ready(function () {
//    New order in admin side functions start
    var firstRow = $('#evp_bundle_ticketbundle_order_ticketTypes')
        .first();

    firstRow.find(':input').each(
            function () {
                this.name += '[]';
            }
    );

    $('#evp_bundle_ticketbundle_order_ticketTypes_add').on('click', function () {
        var clone = firstRow.clone(true);
        firstRow.after(clone);
    });
//    New order in admin side functions end

//    Seat selection admin side action start
    var url = $('svg').attr('toggle');
    $(".seat").on('click', function () {
        var current = $(this);
        $.get(url + '/' + current.attr('id'), {} ).done( function () {
            if (hasClassSVG(current, 'show')) {
                removeClassSVG(current, 'show');
                addClassSVG(current, 'hide');
            } else {
                removeClassSVG(current, 'hide');
                addClassSVG(current, 'show');
            }
        });
    });
//    Seat selection admin side action end
});
