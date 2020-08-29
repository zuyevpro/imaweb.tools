;(function ($) {

    if(!window.jQuery) {
        throw new Error('jQuery is required');
    }

    const CartController = function() {
    };

    CartController.prototype.add = function (productId, quantity, cb)
    {
        const ctx = this;
        const body = $('body');

        $.ajax({
            url: '/ajax/cart/add/',
            type: 'POST',
            dataType: 'json',
            data: {
                product_id: productId,
                quantity: quantity
            },
            success: function(json) {
                $(ctx).trigger('cart.add', {
                    'id': json.basketItemId,
                    'productId': productId
                });

                let cartItems = body.data('cart-items');
                cartItems.push({
                    'id': json.basketItemId,
                    'productId': productId
                });
                body.data('cart-items', cartItems);

                if (typeof cb === 'function') {
                    cb.apply(ctx, arguments);
                }
            }
        });
    };

    CartController.prototype.update = function (basketItemId, quantity, cb)
    {
        const ctx = this;

        $.ajax({
            url: '/ajax/cart/update/',
            type: 'POST',
            dataType: 'json',
            data: {
                id: basketItemId,
                quantity: quantity
            },
            success: function(json) {
                $(ctx).trigger('cart.update', {
                    id: basketItemId,
                    quantity: quantity
                });
                if (typeof cb === 'function') {
                    cb.apply(ctx, arguments);
                }
            }
        });
    };

    CartController.prototype.remove = function (productId, cb)
    {
        const ctx = this;
        const body = $('body');

        $.ajax({
            url: '/ajax/cart/remove/',
            type: 'POST',
            dataType: 'json',
            data: {
                product_id: productId
            },
            success: function() {
                $(ctx).trigger('cart.remove', {
                    productId: productId
                });
                let cartItems = body.data('cart-items');
                for (let key in cartItems) {
                    if (cartItems.hasOwnProperty(key) && cartItems[key]['productId'] === productId) {
                        delete cartItems[key];
                    }
                }
                $('body').data('cart-items', cartItems);
                if (typeof cb === 'function') {
                    cb.apply(ctx, arguments);
                }
            }
        });
    };

    CartController.prototype.check = function (productId, cb) {
        const cartItems = $('body').data('cart-items');

        let result = false;

        for (let key in cartItems) {
            if (cartItems.hasOwnProperty(key) && parseInt(cartItems[key]['productId']) === parseInt(productId)) {
                result = true;
            }
        }

        if (typeof cb === 'function') {
            cb.call(this, result);
        }
    };

    CartController.prototype.setProp = function (basketItemId, propName, propVal, cb) {
        const ctx = this;
        const body = $('body');

        $.ajax({
            url: '/ajax/cart/setprop/',
            type: 'POST',
            data: {
                id: basketItemId,
                propName: propName,
                propVal: propVal
            },
            success: function() {
                $(ctx).trigger('cart.update', {
                    basketItemId: basketItemId
                });
                let cartItems = body.data('cart-items');
                for (let key in cartItems) {
                    if (cartItems.hasOwnProperty(key) && parseInt(cartItems[key]['id']) === parseInt(basketItemId)) {
                        delete cartItems[key];
                    }
                }
                body.data('cart-items', cartItems);
                if (typeof cb === 'function') {
                    cb.apply(ctx, arguments);
                }
            }
        });
    };

    $.fn.cart = function(method) {
        if ($.fn._imawebCart === void(0)) {
            $.fn._imawebCart = new CartController();
        }

        if (typeof (method) === 'string') {
            if (([
                'add',
                'update',
                'remove',
                'setProp',
                'check'
            ]).indexOf(method) > -1) {
                $.fn._imawebCart[method].apply(this, Array.prototype.slice.call(arguments, 0).splice(1));
            }
        }
    }

})(jQuery);