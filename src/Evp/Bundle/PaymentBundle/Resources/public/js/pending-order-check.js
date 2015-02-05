

var checkOrderStatus = function(checkOptions) {
    $.ajax({
        async: false,
        url: checkOptions.checkUri,
        dataType: 'json',
        type: 'GET',
        success: function(response) {
            if (response.order.status === 'done') {
                checkOptions.onSuccess(response);
                return;
            }

            var currentMethod = checkOrderStatus.bind(this, checkOptions);
            setTimeout(currentMethod, 1000);
        }
    });
};