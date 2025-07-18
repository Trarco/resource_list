define(['jquery'], function ($) {
    return {
        init: function () {
            console.log('[typefilter.js] Init chiamato');

            $('.resource-type-dropdown').each(function () {
                const $wrapper = $(this);
                const uniqid = $wrapper.data('uniqid');

                const $toggleBtn = $('#resourceListTypeToggleBtn-' + uniqid);
                const $dropdown = $('#resourceListTypefilterinput-' + uniqid);
                const $icon = $toggleBtn.find('.icon');

                if ($toggleBtn.length === 0 || $dropdown.length === 0) {
                    console.warn('[typefilter.js] ToggleBtn o dropdown non trovati per', uniqid);
                    return;
                }

                console.log('[typefilter.js] Inizializzo blocco:', uniqid);

                // Mostra/nasconde dropdown
                $toggleBtn.on('click', function (e) {
                    e.stopPropagation();
                    $dropdown.toggle();
                    $icon.toggleClass('fa-chevron-down fa-chevron-up');
                    console.log('[typefilter.js] Toggle dropdown:', $dropdown.is(':visible'));
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
                    const selected = $dropdown.find('input.type-filter-checkbox:checked');
                    const $text = $toggleBtn.find('.selected-types-text');

                    if (selected.length === 0) {
                        $text.text($toggleBtn.data('showalltypes'));
                    } else {
                        const typeList = selected.map(function () {
                            return $(this).val().trim();
                        }).get().join(', ');
                        $text.text(typeList);
                    }
                };

                const filterActivities = function () {
                    const selectedTypes = $dropdown.find('input.type-filter-checkbox:checked')
                        .map(function () {
                            return $(this).val().toLowerCase().trim();
                        }).get();

                    console.log('[typefilter.js] Filtri attivi:', selectedTypes);

                    $wrapper.closest('.block').find('details.course-section, ul.rui-section[data-section]').each(function () {
                        const $container = $(this);
                        let visibleCount = 0;

                        $container.find('li.activity-wrapper').each(function () {
                            const $activity = $(this);
                            const modClass = $activity.attr('class') || '';
                            const match = modClass.match(/modtype_([a-z0-9_]+)/i);
                            const modname = match ? match[1] : '';

                            const show = selectedTypes.length === 0 || selectedTypes.includes(modname);
                            $activity.toggle(show);
                            if (show) visibleCount++;
                        });

                        $container.toggle(visibleCount > 0);
                    });
                };

                $dropdown.on('change', 'input.type-filter-checkbox', function () {
                    updateButtonText();
                    filterActivities();
                });

                // Inizializza stato testo bottone
                updateButtonText();
            });
        }
    };
});
