define(['jquery'], function ($) {
    return {
        init: function () {
            $('.resource-tag-dropdown').each(function () {
                const $wrapper = $(this);
                const uniqid = $wrapper.data('uniqid');

                const $toggleBtn = $('#resourceListDropdownToggleBtn-' + uniqid);
                const $dropdown = $('#resourceListTagfilterinput-' + uniqid);
                const $icon = $toggleBtn.find('.icon');

                if ($toggleBtn.length === 0 || $dropdown.length === 0) return;

                // Mostra/nasconde dropdown
                $toggleBtn.on('click', function (e) {
                    e.stopPropagation();
                    $dropdown.toggle();
                    $icon.toggleClass('fa-chevron-down fa-chevron-up');
                });

                // Previene la chiusura interna
                $dropdown.on('click', function (e) {
                    e.stopPropagation();
                });

                // Chiude se clic fuori
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

                const filterActivities = function () {
                    const selectedTags = $dropdown.find('input.tag-filter-checkbox:checked')
                        .map(function () {
                            return $(this).val().toLowerCase().trim();
                        }).get();

                    // Limita la ricerca solo all'interno di questo blocco (usiamo $wrapper)
                    $wrapper.closest('.block').find('details.course-section').each(function () {
                        const $details = $(this);
                        const sectionId = $details.attr('id'); // es: "course-section-0"
                        const sectionNum = sectionId.replace('course-section-', '');

                        const $section = $details.find('ul.rui-section[data-section="' + sectionNum + '"]');
                        if ($section.length === 0) return;

                        const $activities = $section.find('li.activity-wrapper[data-tags]');
                        let visibleCount = 0;

                        $activities.each(function () {
                            const $activity = $(this);
                            const activityTags = ($activity.data('tags') || '').toLowerCase().split(',');
                            const $badges = $activity.find('.activity-tags .badge');

                            const match = selectedTags.length === 0 ||
                                selectedTags.some(tag => activityTags.includes(tag));

                            $badges.removeClass('badge-primary').addClass('badge-secondary');

                            $badges.each(function () {
                                const badgeText = $(this).text().toLowerCase().trim();
                                if (selectedTags.includes(badgeText)) {
                                    $(this).removeClass('badge-secondary').addClass('badge-primary');
                                }
                            });

                            $activity.toggle(match);
                            if (match) visibleCount++;
                        });

                        $section.toggle(visibleCount > 0);
                        $details.toggle(visibleCount > 0);
                    });
                };

                $dropdown.on('change', 'input.tag-filter-checkbox', function () {
                    updateButtonText();
                    filterActivities();
                });
            });
        }
    };
});