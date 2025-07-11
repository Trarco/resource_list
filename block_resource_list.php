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
        global $OUTPUT, $PAGE;

        if ($this->content !== null) {
            return $this->content;
        }

        $this->content = new stdClass;
        $this->content->items = [];


        $this->page->requires->css('/blocks/resource_list/style.css');

        // Titolo personalizzato o di default
        $this->title = !empty($this->config->title) ? $this->config->title : get_string('pluginname', 'block_resource_list');

        // Descrizione opzionale
        if (!empty($this->config->description['text'])) {
            $description = format_text($this->config->description['text'], $this->config->description['format']);
            $this->content->items[] = html_writer::tag('div', $description, ['class' => 'block_resource_list_description']);
        }

        $course = $this->page->course;
        $modinfo = get_fast_modinfo($course);

        // Attività selezionate
        $selected_activity_types = !empty($this->config->activitytype) ? $this->config->activitytype : ['all'];
        $selected_activity_types = array_map('trim', $selected_activity_types);

        // Opzioni di filtro per titolo
        $activity_title_filters = [];
        if (!empty($this->config->activitytitlefilters) && is_array($this->config->activitytitlefilters)) {
            $activity_title_filters = array_map('strtolower', array_filter(array_map('trim', $this->config->activitytitlefilters)));
        }

        // Checkbox specifici per quiz
        $show_verification_quiz = !empty($this->config->showverificationquiz);
        $show_self_assessment_quiz = !empty($this->config->showselfassessmentquiz);
        $show_case_study_quiz = !empty($this->config->showcasestudyquiz);

        $group_sections = !empty($this->config->groupsections);
        $sections = $modinfo->get_section_info_all();
        $templatecontext = ['sections' => []];

        foreach ($sections as $sectionnum => $section) {
            $cms_ids = $modinfo->sections[$sectionnum] ?? [];
            if (empty($cms_ids)) {
                continue;
            }

            // Filtra le attività
            $filtered_cms = array_filter($cms_ids, function ($cmid) use ($modinfo, $selected_activity_types, $activity_title_filters, $show_verification_quiz, $show_self_assessment_quiz, $show_case_study_quiz) {
                $cm = $modinfo->cms[$cmid];
                $is_selected_type = in_array($cm->modname, $selected_activity_types) || in_array('all', $selected_activity_types);
                $activity_name = strtolower($cm->get_formatted_name());

                if (!$cm->uservisible || !$cm->has_view() || !$cm->url || !$is_selected_type) {
                    return false;
                }

                if (empty($activity_title_filters)) {
                    return true;
                }

                foreach ($activity_title_filters as $keyword) {
                    if (strpos($activity_name, $keyword) !== false) {
                        return true;
                    }
                }

                return false;
            });

            if (empty($filtered_cms)) {
                continue;
            }

            $activities = [];
            foreach ($filtered_cms as $cmid) {
                $cm = $modinfo->cms[$cmid];
                $activities[] = [
                    'id' => $cm->id,
                    'modname' => $cm->modname,
                    'name' => $cm->get_formatted_name(),
                    'url' => $cm->url->out(),
                    'icon' => $cm->get_icon_url()->out(),
                    'indented' => $cm->indent > 0 && empty($this->config->removeindentation),
                ];
            }

            $templatecontext['sections'][] = [
                'sectionid' => $sectionnum,
                'sectionname' => get_section_name($course, $section),
                'activities' => $activities
            ];
        }

        if (!empty($templatecontext['sections'])) {
            $this->content->items[] = $OUTPUT->render_from_template('block_resource_list/activity_list', $templatecontext);
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
