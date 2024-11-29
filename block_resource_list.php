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
        global $CFG, $DB, $OUTPUT;

        if ($this->content !== NULL) {
            return $this->content;
        }

        $this->content = new stdClass;
        $this->content->items = array();
        $this->content->icons = array();

        $this->title = !empty($this->config->title) ? $this->config->title : get_string('pluginname', 'block_resource_list');

        if (!empty($this->config->description)) {
            $descriptiontext = is_array($this->config->description) ? $this->config->description['text'] : $this->config->description;
            $this->content->items[] = $OUTPUT->box(format_text($descriptiontext, FORMAT_HTML), 'description-box');
        }

        $course = $this->page->course;
        $modinfo = get_fast_modinfo($course);

        $activitytypes = isset($this->config->activitytype) && is_array($this->config->activitytype)
            ? $this->config->activitytype
            : array('all');

        foreach ($modinfo->cms as $cm) {
            if (!$cm->uservisible || !$cm->has_view() || !$cm->url) {
                continue;
            }

            if (!in_array('all', $activitytypes) && !in_array($cm->modname, $activitytypes)) {
                continue;
            }

            $icon = $OUTPUT->pix_icon('icon', $cm->modplural, 'mod_' . $cm->modname, ['class' => 'activity-icon']);
            $this->content->items[] = '<a href="' . $cm->url . '">' . $icon . ' ' . $cm->name . '</a>';
        }

        return $this->content;
    }

    /**
     * Returns the HTML for the activity type filter dropdown.
     *
     * @param array $selected The selected activity types (array of keys).
     * @return string HTML of the select element.
     */
    private function get_activity_type_filter($selected)
    {
        global $OUTPUT, $PAGE, $DB;

        // Retrieve cached modules if available.
        $cache = cache::make('block_resource_list', 'modules');
        $modules = $cache->get('all');

        if (!$modules) {
            $sql = "SELECT DISTINCT name 
                FROM {modules} 
                WHERE visible = 1
                ORDER BY name ASC";
            $modules = $DB->get_records_sql($sql);
            $cache->set('all', $modules);
        }

        // Initialize the array for activity types.
        $activitytypes = array(
            'all' => get_string('allactivities', 'block_resource_list')
        );

        foreach ($modules as $module) {
            $activitytypes[$module->name] = get_string($module->name, 'block_resource_list');
        }

        // Generate the filter form.
        $options = '';
        foreach ($activitytypes as $key => $label) {
            $selected_attr = (is_array($selected) && in_array($key, $selected)) ? 'selected="selected"' : '';
            $options .= '<option value="' . s($key) . '" ' . $selected_attr . '>' . s($label) . '</option>';
        }

        $courseid = $PAGE->course->id;

        return '
        <form method="get" action="">
            <input type="hidden" name="id" value="' . s($courseid) . '">
            <label for="activitytype">' . get_string('filterbyactivity', 'block_resource_list') . '</label>
            <select name="activitytype[]" id="activitytype" multiple="multiple" onchange="this.form.submit()">
                ' . $options . '
            </select>
            <noscript>
                <button type="submit">' . get_string('applyfilter', 'block_resource_list') . '</button>
            </noscript>
        </form>';
    }




    public function get_aria_role()
    {
        return 'navigation';
    }

    function applicable_formats()
    {
        return array(
            'all' => true,
            'mod' => false,
            'my' => false,
            'admin' => false,
            'tag' => false
        );
    }

    public function instance_config_save($data, $nolongerused = false)
    {
        // Se 'activitytype' non è un array, convertirlo in uno.
        if (isset($data->activitytype) && !is_array($data->activitytype)) {
            $data->activitytype = array($data->activitytype); // Convertire in array se è una stringa
        }

        parent::instance_config_save($data, $nolongerused);
    }


    public function instance_allow_multiple()
    {
        return true;
    }
}
