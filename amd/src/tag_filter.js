define(['jquery', 'block_resource_list/filter_manager'], function ($, manager) {
    return {
        init: function () {
            $('.resource-tag-dropdown').each(function () {
                const $wrapper = $(this);
                const uniqid = $wrapper.data('uniqid');

                const $toggleBtn = $('#resourceListDropdownToggleBtn-' + uniqid);
                const $dropdown = $('#resourceListTagfilterinput-' + uniqid);
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
                    const selected = $dropdown.find('input.tag-filter-checkbox:checked');
                    const $text = $toggleBtn.find('.selected-tags-text');

                    if (selected.length === 0) {
                        $text.text($toggleBtn.data('showallresources'));
                    } else {
                        const tagList = selected.map(function () {
                            return $(this).val().trim();
                        }).get().join(', ');
                        $text.text(tagList);
                    }
                };

                $dropdown.on('change', 'input.tag-filter-checkbox', function () {
                    const selectedTags = $dropdown.find('input.tag-filter-checkbox:checked')
                        .map(function () { return $(this).val(); }).get();
                    updateButtonText();
                    manager.setTags(uniqid, selectedTags);
                });

                updateButtonText();
            });
        }
    };
});