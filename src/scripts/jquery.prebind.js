jQuery.fn.getEvents = function() {
    if (typeof(jQuery._data) == 'function') {
        return jQuery._data(this.get(0), 'events') || {};
    } else if (typeof(this.data) == 'function') { // jQuery version < 1.7.?
        return this.data('events') || {};
    }
    return {};
};

jQuery.fn.preBind = function(type, data, fn) {
	this.each(function () {
		var $this = jQuery(this);

		$this.bind(type, data, fn);

		var currentBindings = $this.getEvents()[type];
		if (jQuery.isArray(currentBindings)) {
				currentBindings.unshift(currentBindings.pop());
		}
	});

	return this;
};