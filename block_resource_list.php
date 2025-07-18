<?php
defined('MOODLE_INTERNAL') || die();
require_once($CFG->libdir . '/filelib.php');

class block_resource_list extends block_list
{
    function init()
    {
        $this->title = get_string('pluginname', 'block_resource_list');
    }

    function get_content()
    {
        global $OUTPUT;

        if ($this->content !== null) {
            return $this->content;
        }

        $this->content = new stdClass;
        $this->content->items = [];

        $tagsenabled = get_config('core', 'usetags');
        $tagfrontendfilterenabled = $tagsenabled && (!isset($this->config->enabletagfrontendfilter) || $this->config->enabletagfrontendfilter);

        $typefrontendfilterenabled = !isset($this->config->enabletypefrontendfilter) || $this->config->enabletypefrontendfilter;
        $templatecontext['showtypefrontendfilter'] = $typefrontendfilterenabled;


        $this->page->requires->css('/blocks/resource_list/styles.css');

        if ($tagfrontendfilterenabled && !defined('BLOCK_RESOURCE_LIST_JS_INCLUDED')) {
            define('BLOCK_RESOURCE_LIST_JS_INCLUDED', true);
            $this->page->requires->js_call_amd('block_resource_list/tag_filter', 'init');
        }

        if (!defined('BLOCK_RESOURCE_LIST_TYPEFILTER_INCLUDED')) {
            define('BLOCK_RESOURCE_LIST_TYPEFILTER_INCLUDED', true);
            $this->page->requires->js_call_amd('block_resource_list/type_filter', 'init');
        }

        $this->title = !empty($this->config->title) ? $this->config->title : get_string('pluginname', 'block_resource_list');

        if (!empty($this->config->description['text'])) {
            $description = format_text($this->config->description['text'], $this->config->description['format']);
            $this->content->items[] = html_writer::tag('div', $description, ['class' => 'block_resource_list_description']);
        }

        $blockid = 'resource_block_' . $this->instance->id;

        $course = $this->page->course;
        $modinfo = get_fast_modinfo($course);

        $selected_activity_types = !empty($this->config->activitytype) ? $this->config->activitytype : ['all'];
        $selected_activity_types = array_map('trim', $selected_activity_types);

        $activity_title_filters = [];
        if (!empty($this->config->activitytitlefilters) && is_array($this->config->activitytitlefilters)) {
            $activity_title_filters = array_map('strtolower', array_filter(array_map('trim', $this->config->activitytitlefilters)));
        }

        $group_sections = !empty($this->config->groupsections);
        $sections = $modinfo->get_section_info_all();

        $available_tags = [];
        $available_types = [];
        $templatecontext = ['sections' => []];

        foreach ($sections as $sectionnum => $section) {
            $cms_ids = $modinfo->sections[$sectionnum] ?? [];
            if (empty($cms_ids)) {
                continue;
            }

            $filtered_cms = [];

            foreach ($cms_ids as $cmid) {
                $cm = $modinfo->cms[$cmid];

                if (!$cm->uservisible || !$cm->has_view() || !$cm->url) {
                    continue;
                }

                $is_selected_type = in_array($cm->modname, $selected_activity_types) || in_array('all', $selected_activity_types);
                if (!$is_selected_type) {
                    continue;
                }

                $activity_name = strtolower($cm->get_formatted_name());
                $exclude_matches = !empty($this->config->excludefiltermatches);

                if (empty($activity_title_filters)) {
                    $filtered_cms[] = $cmid;
                    continue;
                }

                $matched = false;
                foreach ($activity_title_filters as $keyword) {
                    if (strpos($activity_name, $keyword) !== false) {
                        $matched = true;
                        break;
                    }
                }

                if (($matched && !$exclude_matches) || (!$matched && $exclude_matches)) {
                    $filtered_cms[] = $cmid;
                }
            }

            if (empty($filtered_cms)) {
                continue;
            }

            $activities = [];
            foreach ($filtered_cms as $cmid) {
                $cm = $modinfo->cms[$cmid];

                $tagobjects = [];
                if ($tagsenabled) {
                    $tagobjects = core_tag_tag::get_item_tags('core', 'course_modules', $cm->id);
                }

                $tags = [];

                foreach ($tagobjects as $tag) {
                    $tags[] = [
                        'id' => $tag->id,
                        'name' => $tag->name,
                        'rawname' => $tag->rawname,
                        'url' => core_tag_tag::make_url($tag->tagcollid, $tag->rawname)->out()
                    ];

                    $available_tags[$tag->rawname] = [
                        'id' => $tag->id,
                        'name' => $tag->name,
                        'rawname' => $tag->rawname,
                        'url' => core_tag_tag::make_url($tag->tagcollid, $tag->rawname)->out()
                    ];
                }

                if (!isset($available_types[$cm->modname])) {
                    $available_types[$cm->modname] = [
                        'modname' => $cm->modname,
                        'displayname' => get_string('modulename', $cm->modname)
                    ];
                }

                $activities[] = [
                    'id' => $cm->id,
                    'modname' => $cm->modname,
                    'name' => $cm->get_formatted_name(),
                    'url' => $cm->url->out(),
                    'icon' => $cm->get_icon_url()->out(),
                    'indented' => $cm->indent > 0 && empty($this->config->removeindentation),
                    'taglist' => $tags,
                    'has_tags' => !empty($tags)
                ];
            }

            $templatecontext['sections'][] = [
                'sectionid' => $sectionnum,
                'sectionname' => get_section_name($course, $section),
                'activities' => $activities,
                'uniqid' => $blockid . '_section_' . $sectionnum
            ];
        }

        $templatecontext['showtagfrontendfilter'] = $tagsenabled ? $tagfrontendfilterenabled : false;
        $templatecontext['availabletags'] = $tagsenabled ? array_values($available_tags) : [];
        $templatecontext['availabletypes'] = array_values($available_types);
        $templatecontext['showtypefrontendfilter'] = true;
        $templatecontext['uniqid'] = $blockid;

        if (!empty($this->config->groupsections)) {
            $this->content->items[] = $OUTPUT->render_from_template('block_resource_list/activity_list_grouped', $templatecontext);
        } else {
            $this->content->items[] = $OUTPUT->render_from_template('block_resource_list/activity_list_flat', $templatecontext);
        }

        return $this->content;
    }

    function applicable_formats()
    {
        return array(
            'course-view' => true, // Mostra solo nelle pagine corso
            'site' => false,
            'my' => false,
        );
    }

    public function instance_config_save($data, $nolongerused = false)
    {
        if (isset($data->activitytitlefilters)) {
            $filters = $data->activitytitlefilters;
            if (!is_array($filters)) {
                $filters = [$filters];
            }
            $data->activitytitlefilters = array_values(array_filter(array_map('trim', $filters)));
        }

        parent::instance_config_save($data, $nolongerused);
    }

    public function instance_allow_multiple()
    {
        return true; // Permetti più blocchi
    }
}
