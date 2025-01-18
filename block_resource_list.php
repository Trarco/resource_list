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

        if ($this->content !== null) {
            return $this->content;
        }

        $this->content = new stdClass;
        $this->content->items = [];

        // Debug della configurazione
        debugging('Valori activitytype: ' . json_encode($this->config->activitytype));

        // Imposta il titolo del blocco
        $this->title = !empty($this->config->title) ? $this->config->title : get_string('pluginname', 'block_resource_list');

        $course = $this->page->course;
        $modinfo = get_fast_modinfo($course);

        // Determina il renderer da utilizzare
        try {
            $renderer = $PAGE->get_renderer('theme_universe', 'core_course_renderer');
        } catch (coding_exception $e) {
            $renderer = $PAGE->get_renderer('core', 'course');
        }

        // Ottieni i tipi di attività selezionati
        $selected_activity_types = !empty($this->config->activitytype) ? $this->config->activitytype : ['all'];
        $selected_activity_types = array_map('trim', $selected_activity_types);

        $sections = $modinfo->get_section_info_all();

        foreach ($sections as $sectionnum => $section) {
            $cms = $modinfo->sections[$sectionnum] ?? [];
            $cms = array_filter($cms, function ($cmid) use ($modinfo, $selected_activity_types) {
                $cm = $modinfo->cms[$cmid];
                $is_selected_type = in_array($cm->modname, $selected_activity_types) || in_array('all', $selected_activity_types);

                debugging("Modulo: {$cm->modname}, Selezionato: " . ($is_selected_type ? 'Sì' : 'No'));

                return $cm->uservisible && $cm->has_view() && $cm->url && $is_selected_type;
            });

            if (empty($cms)) {
                continue; // Salta sezioni vuote
            }

            $sectionname = get_section_name($course, $section);

            $activities = array_map(function ($cmid) use ($modinfo) {
                $cm = $modinfo->cms[$cmid];
                $iconurl = $cm->get_icon_url(); // Ottieni l'URL dell'icona
                $activityclass = 'modtype_' . $cm->modname; // Classe basata sul tipo di attività

                // Determina se l'attività è spostata a destra
                $is_indented = $cm->indent > 0; // `indent` è maggiore di 0 se l'attività è spostata a destra

                // Icona dell'attività con il link
                $iconhtml = html_writer::tag(
                    'a',
                    html_writer::empty_tag('img', [
                        'src' => $iconurl,
                        'alt' => ucfirst($cm->modname) . ' icon',
                        'class' => 'activityicon'
                    ]),
                    [
                        'href' => $cm->url,
                        'class' => 'activity-icon activityiconcontainer smaller courseicon align-self-start position-relative'
                    ]
                );

                // Nome dell'attività
                $activityname = html_writer::tag(
                    'div',
                    html_writer::tag(
                        'div',
                        html_writer::tag(
                            'div',
                            html_writer::tag(
                                'a',
                                html_writer::tag('span', $cm->get_formatted_name(), ['class' => 'instancename']) .
                                    html_writer::tag('span', ' ' . ucfirst($cm->modname), ['class' => 'accesshide']),
                                ['href' => $cm->url, 'class' => 'aalink stretched-link', 'onclick' => '']
                            ),
                            ['class' => 'activityname']
                        ),
                        ['class' => 'media-body align-self-center']
                    ),
                    ['class' => 'activitytitle media  ' . $activityclass . ' position-relative align-self-start']
                );

                // Sezione della descrizione (aggiunta dinamica se necessario)
                $description = html_writer::tag('div', '', ['class' => 'rui-modavailability description']);

                // Wrapper del nome e dell'icona
                $activitybasis = html_writer::tag(
                    'div',
                    html_writer::tag(
                        'div',
                        $iconhtml .
                            html_writer::tag('div', $activityname, ['class' => 'activity-name-area activity-instance d-flex flex-column mr-2']),
                        ['class' => 'd-flex flex-row align-items-center mr-auto']
                    ),
                    ['class' => 'activity-basis d-flex align-items-center flex-wrap']
                );

                // Contenitore principale
                $content = html_writer::tag(
                    'div',
                    html_writer::tag(
                        'div',
                        $activitybasis . $description,
                        ['class' => 'd-grid align-items-center rui--activity-notautomatic']
                    ),
                    ['class' => 'activity-item focus-control', 'data-activityname' => $cm->get_formatted_name()]
                );

                // Determina la classe aggiuntiva `indented` in base all'attributo `indent`
                $additional_classes = $is_indented ? ' indented' : '';

                // Elemento `<li>` finale
                return html_writer::tag(
                    'li',
                    $content,
                    [
                        'class' => 'activity activity-wrapper ' . $cm->modname . ' ' . $activityclass . ' hasinfo' . $additional_classes,
                        'id' => 'module-' . $cm->id,
                        'data-for' => 'cmitem',
                        'data-id' => $cm->id,
                        'data-indexed' => $is_indented ? 'true' : 'false'
                    ]
                );
            }, $cms);


            $sectioncontent = html_writer::tag(
                'ul',
                implode('', $activities),
                [
                    'class' => 'rui-section section img-text d-block w-100',
                    'data-section' => $sectionnum,
                ]
            );

            $sectiontitle = html_writer::tag(
                'div',
                html_writer::tag(
                    'h3',
                    $sectionname,
                    [
                        'class' => 'sectionname course-content-item d-flex align-self-stretch align-items-center mb-0',
                        'id' => "sectionid-{$sectionnum}-title",
                        'data-for' => 'section_title',
                        'data-id' => $sectionnum,
                        'data-number' => $sectionnum,
                    ]
                ),
                [
                    'class' => 'rui-course-header-btn d-flex align-items-start position-relative col pl-0 my-3',
                ]
            );

            $sectionhtml = html_writer::tag(
                'div',
                $sectiontitle . $sectioncontent,
                [
                    'class' => 'course-section', // Contenitore della sezione
                    'id' => "course-section-{$sectionnum}", // ID unico per la sezione
                ]
            );

            $this->content->items[] = $sectionhtml;
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
        if (isset($data->activitytype) && !is_array($data->activitytype)) {
            $data->activitytype = explode(',', $data->activitytype);
        }

        parent::instance_config_save($data, $nolongerused);
    }

    public function instance_allow_multiple()
    {
        return true; // Permetti più blocchi
    }
}
