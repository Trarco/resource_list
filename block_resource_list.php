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

        // Controlla se le sezioni devono essere raggruppate
        $group_sections = !empty($this->config->groupsections);

        $sections = $modinfo->get_section_info_all();
        $all_activities = [];

        foreach ($sections as $sectionnum => $section) {
            $cms = $modinfo->sections[$sectionnum] ?? [];
            $cms = array_filter($cms, function ($cmid) use ($modinfo, $selected_activity_types) {
                $cm = $modinfo->cms[$cmid];
                $is_selected_type = in_array($cm->modname, $selected_activity_types) || in_array('all', $selected_activity_types);

                // Recupera la configurazione dei filtri
                $show_verification_quiz = !empty($this->config->showverificationquiz);
                $show_self_assessment_quiz = !empty($this->config->showselfassessmentquiz);
                $show_case_study_quiz = !empty($this->config->showcasestudyquiz);

                // Se nessun filtro specifico è selezionato, mostra tutto
                if (!$show_verification_quiz && !$show_self_assessment_quiz && !$show_case_study_quiz) {
                    return $cm->uservisible && $cm->has_view() && $cm->url && $is_selected_type;
                }

                // Converte il nome in minuscolo per il confronto
                $activity_name = strtolower($cm->get_formatted_name());

                // Controlla se l'attività è un quiz e applica i filtri selezionati
                if ($cm->modname === 'quiz') {
                    $matched_filters = [];

                    if ($show_verification_quiz && strpos($activity_name, 'quiz di verifica') !== false) {
                        $matched_filters[] = 'quiz di verifica';
                    }
                    if ($show_self_assessment_quiz && strpos($activity_name, 'test di autovalutazione') !== false) {
                        $matched_filters[] = 'test di autovalutazione';
                    }
                    if ($show_case_study_quiz && strpos($activity_name, 'esercitazione del caso') !== false) {
                        $matched_filters[] = 'esercitazione del caso';
                    }

                    // Se l'attività soddisfa almeno uno dei filtri selezionati, mostrarla
                    if (!empty($matched_filters)) {
                        return $cm->uservisible && $cm->has_view() && $cm->url;
                    }

                    return false; // Nasconde se nessun filtro corrisponde
                }

                return $cm->uservisible && $cm->has_view() && $cm->url && $is_selected_type;
            });

            if (empty($cms)) {
                continue; // Salta sezioni vuote
            }

            $sectionname = get_section_name($course, $section);

            $activities = array_map(function ($cmid) use ($modinfo) {
                global $CFG;
                $cm = $modinfo->cms[$cmid];
                $iconurl = $cm->get_icon_url();
                $activityclass = 'modtype_' . $cm->modname;

                // Determina se l'attività è spostata a destra
                $is_indented = $cm->indent > 0;

                // Controlla se l'indentazione deve essere rimossa dalla configurazione del blocco
                if (!empty($this->config->removeindentation)) {
                    $is_indented = false; // Rimuove l'indentazione se il checkbox è selezionato
                }

                $iconurl = $cm->get_icon_url();

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
                    ['class' => 'activitytitle media ' . $activityclass . ' position-relative align-self-start']
                );

                $description = html_writer::tag('div', '', ['class' => 'rui-modavailability description']);

                $activitybasis = html_writer::tag(
                    'div',
                    html_writer::tag(
                        'div',
                        $iconhtml . html_writer::tag('div', $activityname, ['class' => 'activity-name-area activity-instance d-flex flex-column mr-2']),
                        ['class' => 'd-flex flex-row align-items-center mr-auto']
                    ),
                    ['class' => 'activity-basis d-flex align-items-center flex-wrap']
                );

                $content = html_writer::tag(
                    'div',
                    html_writer::tag(
                        'div',
                        $activitybasis . $description,
                        ['class' => 'd-grid align-items-center rui--activity-notautomatic']
                    ),
                    ['class' => 'activity-item focus-control my-1', 'data-activityname' => $cm->get_formatted_name()]
                );

                $additional_classes = $is_indented ? ' indented' : '';

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

            if ($group_sections) {
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
                        'class' => 'course-section',
                        'id' => "course-section-{$sectionnum}",
                    ]
                );

                $this->content->items[] = $sectionhtml;
            } else {
                $all_activities = array_merge($all_activities, $activities);
            }
        }

        if (!$group_sections) {
            $this->content->items[] = html_writer::tag(
                'ul',
                implode('', $all_activities),
                [
                    'class' => 'activity-list',
                    'style' => 'padding-left: 0;',
                ]
            );
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
