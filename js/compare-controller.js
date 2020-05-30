;(function ($) {
	const CompareController = function() {
	};

	CompareController.prototype.add = function (productId, cb)
	{
		const ctx = this;

		$.ajax({
			url: '/ajax/compare/add/',
			type: 'POST',
			data: {
				id: productId
			},
			success: function() {
				$(ctx).trigger('compare.add', {
					productId: productId
				});

				const compareItems = $('body').data('compare-items');
				compareItems.push(productId);
				$('body').data('compare-items', compareItems);

				if (typeof cb === 'function') {
					cb.apply(ctx, arguments);
				}
			}
		});
	};

	CompareController.prototype.remove = function (productId, cb)
	{
		const ctx = this;

		$.ajax({
			url: '/ajax/compare/remove/',
			type: 'POST',
			data: {
				id: productId
			},
			success: function() {
				$(ctx).trigger('compare.remove', {
					productId: productId
				});
				const compareItems = $('body').data('compare-items');
				for (let key in compareItems) {
					if (compareItems[key] == productId) {
						delete compareItems[key];
					}
				}
				$('body').data('compare-items', compareItems);
				if (typeof cb === 'function') {
					cb.apply(ctx, arguments);
				}
			}
		});
	};

	CompareController.prototype.check = function (productId, cb) {
		const compareItems = $('body').data('compare-items');
		let result = false;

		for (let key in compareItems) {
			if (parseInt(compareItems[key]) == parseInt(productId)) {
				result = true;
			}
		}

		if (typeof cb === 'function') {
			cb.call(this, result);
		}
	};

	$.fn.compare = function(method) {
		if ($.fn._imawebCompare === void(0)) {
			$.fn._imawebCompare = new CompareController();
		}

		if (typeof (method) === 'string') {
			if (([
				'add',
				'remove',
				'check'
			]).indexOf(method) > -1) {
				$.fn._imawebCompare[method].apply(this, Array.prototype.slice.call(arguments, 0).splice(1));
			}
		}
	}

})(jQuery);