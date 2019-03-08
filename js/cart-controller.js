;(function ($) {
    var CartController = function() {
        this.addUrl = '/ajax/cart/add/';
        this.updateUrl = '/ajax/cart/update/';
        this.removeUrl = '/ajax/cart/remove/';
    }

    CartController.prototype.add = function (productId, quantity, callback)
    {
        $.ajax({
            url: this.addUrl,
            type: 'POST',
            dataType: 'json',
            data: {
                product_id: productId,
                quantity: quantity
            },
            success: callback
        });
    };

    CartController.prototype.update = function (productId, quantity, callback)
    {
        $.ajax({
            url: this.updateUrl,
            type: 'POST',
            dataType: 'json',
            data: {
                product_id: productId,
                quantity: quantity
            },
            success: callback
        });
    };

    CartController.prototype.remove = function (productId, callback)
    {
        $.ajax({
            url: this.removeUrl,
            type: 'POST',
            dataType: 'json',
            data: {
                product_id: productId
            },
            success: callback
        });
    };

    $.fn.cart = function(method) {
        if ($.fn._imawebCart === void(0)) {
            $.fn._imawebCart = new CartController();
        }

        if (typeof (method) === 'string') {
            if (method == 'add') {
                $.fn._imawebCart.add.apply(this._imawebCart, Array.prototype.slice.call(arguments, 0).splice(1));
            }
            else if (method === 'update') {
                $.fn._imawebCart.update.apply(this._imawebCart, Array.prototype.slice.call(arguments, 0).splice(1));
            }
            else if (method === 'remove') {
                $.fn._imawebCart.remove.apply(this._imawebCart, Array.prototype.slice.call(arguments, 0).splice(1));
            }
        }
    }

})(jQuery);