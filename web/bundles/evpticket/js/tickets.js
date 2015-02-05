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

$( document ).ready(function() {
    $('[name="evp_bundle_ticketbundle_payment_type_select[paymentChoice]"]').click(function() {
        if ($(this).attr('value') == 'invoice') {
            $('div.invoice').css('visibility', 'hidden');
        } else {
            $('div.invoice').css('visibility', 'visible');
        }
    });

//    Seat selection user side action start
    var url = $('svg').attr('toggle');
    $(".seat.show.free, .seat.show.reserved").on('click', function () {
        var current = $(this);
        $.get(url + '/' + current.attr('id'), {} ).done( function (data) {
            if (hasClassSVG(current, 'free')) {
                removeClassSVG(current, 'free');
                addClassSVG(current, 'reserved');
            } else {
                removeClassSVG(current, 'reserved');
                addClassSVG(current, 'free');
            }
            $('.requested').html(data);
        });
    });
//    Seat selection user side action end


//    Catches change event in ticketCount
    $(".ticketCountBox").each(function() {
        var $this = $(this);
        $(this).on('change', function(){
            var fullUrl = $(this).attr('change-url') + '/' + $('option:selected', this).val();
            $.get(fullUrl).done(
                function(data) {
                    var jsonData = JSON.parse(data);
                    $this.closest("div.row").children("div.partialSum").text(jsonData.oneDetailSum);

                    $('#discountAmount').text(jsonData.discountAmount);
                    $('#totalDiscountedSum').text(jsonData.totalDiscountedSum);
                    $('#totalSumBeforeDiscount').text(jsonData.totalSumBeforeDiscount).parent().removeClass('nonShown');
                }
            )
        });
    });
});
