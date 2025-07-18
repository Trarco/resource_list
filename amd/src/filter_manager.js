define(['jquery'], function ($) {
    const filtersByBlock = {};

    function applyFiltersForBlock(uniqid) {
        const filters = filtersByBlock[uniqid] || { tags: [], types: [] };
        const $block = $('.block[data-blockuniqid="' + uniqid + '"]');

        let anyVisible = false;

        $block.find('details.course-section, ul.rui-section[data-section]').each(function () {
            const $container = $(this);
            let visibleCount = 0;

            $container.find('li.activity-wrapper').each(function () {
                const $activity = $(this);
                const tags = ($activity.data('tags') || '').toLowerCase().split(',');
                const modClass = $activity.attr('class') || '';
                const match = modClass.match(/modtype_([a-z0-9_]+)/i);
                const modname = match ? match[1] : '';

                const tagMatch = filters.tags.length === 0 || filters.tags.some(tag => tags.includes(tag));
                const typeMatch = filters.types.length === 0 || filters.types.includes(modname);

                const show = tagMatch && typeMatch;
                $activity.toggle(show);
                if (show) visibleCount++;
            });

            $container.toggle(visibleCount > 0);
            if (visibleCount > 0) anyVisible = true;
        });

        $block.find('.no-activities-message[data-uniqid="' + uniqid + '"]').toggle(!anyVisible);
    }

    return {
        setTags: function (uniqid, selectedTags) {
            filtersByBlock[uniqid] = filtersByBlock[uniqid] || { tags: [], types: [] };
            filtersByBlock[uniqid].tags = selectedTags.map(t => t.toLowerCase().trim());
            applyFiltersForBlock(uniqid);
        },
        setTypes: function (uniqid, selectedTypes) {
            filtersByBlock[uniqid] = filtersByBlock[uniqid] || { tags: [], types: [] };
            filtersByBlock[uniqid].types = selectedTypes.map(t => t.toLowerCase().trim());
            applyFiltersForBlock(uniqid);
        }
    };
});