(function($) {
    $.entwine(function($) {
        $('select.multiselectfield').entwine({
            onchange: function() {
                // Ensure field is initialised - multiselect can trigger changetracker during initialisation
                if (this.data('initialised')) {
                    this.closest('form').trigger('dirty');
                }
            },
            onmatch: function() {
                this.initialise();
            },
            redraw: function() {
                this.multiselect('refresh');
            },
            initialise: function() {
                if (this.data('initialised')) {
                    return;
                }

                this.multiselect({
                    availableFirst: true,
                    searchable: this.is('[data-searchable]'),
                    sortable: this.is('[data-sortable]')
                });

                var select = this.parents('.field').find('.ui-multiselect');

                // Fix heights - forces field to respect min/max height settings
                var height = 0,
                    minHeight = this.attr('data-min-height'),
                    maxHeight = this.attr('data-max-height'),
                    lists = select.find('ul.connected-list');
                
                lists.each(function(i, item) {
                    var listHeight = 0;

                    $(this).children().not('.ui-helper-hidden-accessible').each(function() {
                        listHeight += $(this).outerHeight();
                    });

                    if (listHeight < minHeight) {
                        listHeight = minHeight;
                    } else if (listHeight > maxHeight) {
                        listHeight = maxHeight;
                    }

                    if (listHeight > height) {
                        height = listHeight;
                    }
                });

                // Set the height
                lists.height(height);

                // Input styling
                select.find('input.search').addClass('text');

                // Convert actions to UI buttons
                var actions = select.find('a.add-all, a.remove-all');

                actions.filter('.add-all').attr('data-icon', 'add');
                actions.filter('.remove-all').attr('data-icon', 'delete');

                // Initialise buttons
                actions.addClass('ss-ui-button').button();

                this.data('initialised', true);
            }
        });
    });
})(jQuery);
