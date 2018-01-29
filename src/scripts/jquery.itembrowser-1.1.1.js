/* jquery.itembrowser.js
 * Version 1.11
 * Used to display a list of items and execute callback functions when they are selected.
 * Contains options for doing pagination or scrollbar in horizontal or vertical formats.
 * Author: Marshall Miller
 */

(function($){
	$.fn.itemBrowser = function (options){		
		var defaults = {
			pageName:		'page',
			itemsPerPage:	6,
			itemMargin:		5,
			itemPadding:	5,
			viewAll:		false,
			vertical:		false,
			pagination:		true
		};
		var settings = $.extend({}, defaults, options);
		
		return new ItemBrowser(this, settings);
	};
})(jQuery);

//CLASS IS BELOW

ItemBrowser = function (element, settings){
	this.element = element;
	this.settings = settings;
	
	this.generateList();
	this.generateButtons();
};

ItemBrowser.prototype.generateList = function (){
	var itemBrowser = this;
	var element		= itemBrowser.element;
	var settings	= itemBrowser.settings;
	var items		= settings.items;
	var list 		= this.list = $('<ul>');
	var itemMargin	= settings.itemMargin;
	var itemPadding = settings.itemPadding;
	var itemWidth	= ($(element).width() / settings.itemsPerPage) - (itemMargin * 4) - (itemPadding * 2);
	var listItem	= $('<li>');
	$(listItem).width(itemWidth).css('padding', itemPadding + 'px').css('margin-left', itemMargin + 'px').css('margin-right', itemMargin + 'px');

	$(element).addClass('itembrowser').append(list);
	
	if (itemBrowser.settings.vertical === true)
	{
		$(element).addClass('itembrowser-vertical');	
	}
	
	if (itemBrowser.settings.pagination === false)
	{
		$(element).addClass('itembrowser-nopagination');	
	}
	
	if (items)
	{
		var itemsLength = items.length;
		for (var i = 0; i < itemsLength; i++)
		{
			var title		= $('<span>').addClass('title').html(items[i].title);
			var value		= items[i].value;
			var newListItem = $(listItem).clone();
			$(newListItem).append(title);
			$(newListItem).data('value', value);
			$(newListItem).click(function (value)
			{
				if (settings.callback)
					settings.callback($(this).data('value'));
					
				$(list).find('li').removeClass('selected');
				$(this).addClass('selected');
			});
			$(list).append(newListItem);
		}
	}
	
	var difference = items.length % settings.itemsPerPage;
	if (difference !== 0)
	{
		for (var i = 0; i < (settings.itemsPerPage - difference); i++)
		{
			var newListItem = $(listItem).clone().addClass('blank');
			$(list).append(newListItem);
		}
	}
};

ItemBrowser.prototype.generateButtons = function (){
	var itemBrowser = this;
	var element		= itemBrowser.element;
	var settings	= itemBrowser.settings;
	var items		= settings.items;
	var itemsLength	= items.length;
	
	if (itemBrowser.settings.pagination === true)
	{
		var buttonsDiv		= $('<div>').addClass('buttons');
		var buttonPrev		= $('<a>').addClass('link_button').addClass('prev').html('Prev ' + settings.pageName);
		var buttonNext		= $('<a>').addClass('link_button').addClass('next').html('Next ' + settings.pageName);
		var buttonViewAll	= $('<a>').addClass('link_button').addClass('viewAll').html('Full View');
		
		//Bind all the actions to the buttons.
		$(buttonViewAll).click(function ()
		{
			itemBrowser.toggleViewAll();
		});
		
		$(buttonPrev).click(function ()
		{
			itemBrowser.scroll(settings.itemsPerPage, 'prev');
		});
		
		$(buttonNext).click(function ()
		{
			itemBrowser.scroll(settings.itemsPerPage, 'next');
		});
		
		$(element).append(buttonsDiv);
		
		if (itemsLength > settings.itemsPerPage)
		{
			$(buttonsDiv).append(buttonPrev);
			$(buttonsDiv).append(buttonNext);
			$(buttonsDiv).append(buttonViewAll);
		}
		}
};

ItemBrowser.prototype.toggleViewAll = function (){
	var itemBrowser = this;
	var element		= itemBrowser.element;
	var settings	= itemBrowser.settings;
	
	if (!settings.viewAll)
	{
		$(element).css('height', 'auto');
		var animateValue = $(element).height();
		$(element).css('height', '');
		$(element).animate(
		{
			height: animateValue
		}, 500);
	}
	else
	{
		$(element).css('height', '');
		var animateValue = $(element).height();
		$(element).css('height', 'auto');
		$(element).animate(
		{
			height: animateValue
		}, 500);
	}

	settings.viewAll = !settings.viewAll;	
};

ItemBrowser.prototype.scroll = function (amount, direction){
	var itemBrowser = this;
	var list		= itemBrowser.list;
	
	//If we actually have less items than the amount we want to scroll or only have one item, just return.
	if ($(list).children().size() <= 1 || $(list).children().size() <= amount)
		return;
	
	var animateSpeed = 100;
	
	$(list).animate({
		opacity: 0
	  }, animateSpeed, function() {
	  	//Reorder the list elements.
		var listLength = $(list).find('li').length;
		
		//This was going to have vertical directions; maybe later.
		switch (direction)
		{
			default:
			case 'next':
			{
				for (var i = 0; i < amount; i++)
					$(list).find('li:first').insertAfter($(list).find('li:eq(' + (listLength - 1) + ')'));
				break;
			}
			
			case 'prev':
			{
				for (var i = 0; i < amount; i++)
					$(list).find('li:eq(' + (listLength - 1) + ')').insertBefore($(list).find('li:first'));
				break;
			}
		}
		
	  	$(list).animate({
			opacity: 1.0
		  }, animateSpeed + 100, function() {
		  
		  });
	  });
};

//Selects a specific options based on the index.
ItemBrowser.prototype.select = function (index){
	var itemBrowser = this;
	var list		= itemBrowser.list;
	$(list).find('li:eq(' + index + ')').click();
};
