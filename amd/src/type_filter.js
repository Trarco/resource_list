define(['jquery', 'block_resource_list/filter_manager'], function ($, manager) {
    return {
        init: function () {
            $('.resource-type-dropdown').each(function () {
                const $wrapper = $(this);
                const uniqid = $wrapper.data('uniqid');

                const $toggleBtn = $('#resourceListTypeToggleBtn-' + uniqid);
                const $dropdown = $('#resourceListTypefilterinput-' + uniqid);
                const $icon = $toggleBtn.find('.icon');

                if ($toggleBtn.length === 0 || $dropdown.length === 0) return;

                $toggleBtn.on('click', function (e) {
                    e.stopPropagation();
                    $dropdown.toggle();
                    $icon.toggleClass('fa-chevron-down fa-chevron-up');
                });

                $dropdown.on('click', function (e) {
                    e.stopPropagation();
                });

                $(document).on('click', function (e) {
                    if (!$(e.target).closest($wrapper).length) {
                        $dropdown.hide();
                        $icon.removeClass('fa-chevron-up').addClass('fa-chevron-down');
                    }
                });

                const updateButtonText = function () {
                    const selected = $dropdown.find('input.type-filter-checkbox:checked');
                    const $text = $toggleBtn.find('.selected-types-text');

                    if (selected.length === 0) {
                        $text.text($toggleBtn.data('showalltypes'));
                    } else {
                        const typeList = selected.map(function () {
                            return $(this).data('displayname').trim();
                        }).get().join(', ');
                        $text.text(typeList);
                    }
                };

                $dropdown.on('change', 'input.type-filter-checkbox', function () {
                    const selectedTypes = $dropdown.find('input.type-filter-checkbox:checked')
                        .map(function () { return $(this).val(); }).get();
                    updateButtonText();
                    manager.setTypes(uniqid, selectedTypes);
                });

                updateButtonText();
            });
        }
    };
}); 