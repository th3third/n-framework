$.extend({ buttonAjax: function(button, options) { 
		//Disable submit buttons so the user doesn't navigate to another place or start something else while this is happening.
		$("input[type='submit']").prop("disabled", function (index, value) { 
			if (!value)
			{
				$(this).attr("data-renable-after-request", true);

				return true;
			}

			return value;
		});	

		button = $(button);
		var content = button.html();
		var width = button.width();

		$(button).addClass("loading");
		
		var request = $.ajax(options);

		request.error(function() {
			$(button).removeClass("loading");
			$("[data-renable-after-request='true']").prop("disabled", false).removeAttr("renable-after-request");	
		});

		request.done(function() {
			$(button).removeClass("loading");
			$("[data-renable-after-request='true']").prop("disabled", false).removeAttr("renable-after-request");	
		});

		return request;
	}
});