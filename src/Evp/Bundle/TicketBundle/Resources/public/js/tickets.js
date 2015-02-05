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

function recalculatePrice(price) {
    var priceOrig = price;
    price = price.replace(' ', '');

    if (price.indexOf('LTL') > -1) {
        var amount = price.match(/(.+)\s*LTL/);
        amount = parseFloat(amount[1].replace(',', '.')) / 3.4528;
        amount = (Math.round(amount * 100) / 100).toFixed(2).replace('.', ',') + ' EUR';
    } else if (price.indexOf('EUR') > -1) {
        var amount = price.match(/(.+)\s*EUR/);
        amount = parseFloat(amount[1].replace(',', '.')) * 3.4528;
        amount = (Math.round(amount * 100) / 100).toFixed(2).replace('.', ',') + ' LTL';
    }
    return priceOrig + '<br><span style="font-size: 0.8em">' + amount + '</span>';
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
                    $this.closest("div.row").children("div.partialSum").html(recalculatePrice(jsonData.oneDetailSum));

                    $('#discountAmount').html(recalculatePrice(jsonData.discountAmount));
                    $('#totalDiscountedSum').html(recalculatePrice(jsonData.totalDiscountedSum));
                    $('#totalSumBeforeDiscount').html(recalculatePrice(jsonData.totalSumBeforeDiscount)).parent().removeClass('nonShown');
                }
            )
        });
    });
});
