var ss = ss || {};

ss.multiselectfields = [];

(function($) {
	$.entwine(function($) {
		// .multiselect-initialised is added after mulitselect has initialised, otherwise
	    // multiselect will trigger the changetracker during initialisation
	    $("select.multiselect-initialised").entwine({
	        onchange: function() {
	            this.closest('form').trigger('dirty');
	        }
	    });

		$('select.multiselectfield').entwine({
			onmatch: function() {
				this.initialise();
			},
			redraw: function() {
				this.multiselect('refresh');
			},
			initialise: function() {
				var self = this,
					form = this.closest('form');

				if(self.data('initialised')) {
					return;
				}

				self.multiselect({
					availableFirst: true,
					searchable: (self.is('[data-searchable]')) ? true : false,
					sortable: self.is('[data-sortable]')
				});

				var select = $('.ui-multiselect');

				// Fix widths
				select.width('100%');
				select.find('div.available, div.selected').width('50%');
				select.find('ul.available, ul.selected').each(function(i, item) {
					var height = 0;

					$(this).children().not('.ui-helper-hidden-accessible').each(function() {
						height += $(this).outerHeight();
					});

					if(height < self.attr('data-min-height')) {
						height = self.attr('data-min-height');
					} else if(height > self.attr('data-max-height')) {
						height = self.attr('data-max-height');
					}

					$(this).height(height);
				});

				// Input styling
				select.find("input.search").addClass("text");

				// Convert actions to UI buttons
				select.find("a.add-all, a.remove-all")
					.addClass("ss-ui-button cms-panel-link ui-corner-all ui-button ui-widget ui-button-text-icon-primary")
					.wrapInner('<span class="ui-button-text"/>');

				select.find("a.add-all").prepend('<span class="ui-button-icon-primary ui-icon btn-icon-add" />');
				select.find(" a.remove-all").prepend('<span class="ui-button-icon-primary ui-icon btn-icon-delete" />');

				self.data('initialised', true);
				self.addClass('multiselect-initialised');
			}
		});
	});
})(jQuery);