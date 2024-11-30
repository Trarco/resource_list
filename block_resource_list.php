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
        global $CFG, $PAGE;

        if ($this->content !== NULL) {
            return $this->content;
        }

        $this->content = new stdClass;
        $this->content->items = array();

        // Imposta il titolo del blocco
        $this->title = !empty($this->config->title) ? $this->config->title : get_string('pluginname', 'block_resource_list');

        $course = $this->page->course; // Ottieni il corso corrente
        $modinfo = get_fast_modinfo($course); // Ottieni informazioni sul corso
        $renderer = $PAGE->get_renderer('block_resource_list'); // Usa il renderer personalizzato

        // Filtraggio delle attività configurate
        $activitytypes = isset($this->config->activitytype) && is_array($this->config->activitytype)
            ? $this->config->activitytype
            : array('all'); // Di default, tutte le attività

        // Funzione per filtrare le attività
        $filter_activities = function ($cms) use ($modinfo, $activitytypes) {
            return array_filter($cms, function ($cmid) use ($modinfo, $activitytypes) {
                $cm = $modinfo->cms[$cmid];
                return $cm->uservisible
                    && $cm->has_view()
                    && $cm->url
                    && (in_array('all', $activitytypes) || in_array($cm->modname, $activitytypes));
            });
        };

        if (!empty($this->config->groupsections)) {
            // Raggruppa le attività per sezione
            $sections = $modinfo->get_section_info_all();

            foreach ($sections as $section) {
                $filtered_cms = $filter_activities($modinfo->sections[$section->section] ?? []);
                if (empty($filtered_cms)) {
                    continue; // Salta sezioni senza moduli filtrati
                }

                $content = $renderer->render_section($course, $section, $modinfo, $filtered_cms);
                if (!empty($content)) {
                    $this->content->items[] = $content;
                }
            }
        } else {
            // Mostra tutte le attività in un elenco semplice
            $sectioncontent = '';
            $filtered_cms = $filter_activities(array_keys($modinfo->cms));

            foreach ($filtered_cms as $cmid) {
                $cm = $modinfo->cms[$cmid];
                // Usa il metodo pix_icon del renderer per generare l'icona
                $icon = $renderer->pix_icon('icon', $cm->modplural, 'mod_' . $cm->modname, ['class' => 'activityicon']);
                $link = html_writer::link($cm->url, $icon . ' ' . $cm->name, ['class' => 'activityinstance']);
                $sectioncontent .= html_writer::tag('li', $link, ['class' => 'activity ' . $cm->modname]);
            }

            if (!empty($sectioncontent)) {
                $this->content->items[] = html_writer::tag('ul', $sectioncontent, ['class' => 'simple-activity-list']);
            }
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
        // Converte il tipo di attività in array se necessario
        if (isset($data->activitytype) && !is_array($data->activitytype)) {
            $data->activitytype = array($data->activitytype);
        }

        parent::instance_config_save($data, $nolongerused);
    }

    public function instance_allow_multiple()
    {
        return true; // Permetti più blocchi
    }
}
