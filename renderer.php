<?php
defined('MOODLE_INTERNAL') || die();

/**
 * Renderer personalizzato per il blocco Resource List.
 */
class block_resource_list_renderer extends plugin_renderer_base
{
    /**
     * Renderizza una sezione del corso.
     *
     * @param stdClass $course Il corso corrente.
     * @param section_info $section La sezione da renderizzare.
     * @param course_modinfo $modinfo Informazioni sui moduli del corso.
     * @param array $filtered_cms Elenco dei moduli filtrati.
     * @return string HTML della sezione.
     */
    public function render_section($course, $section, $modinfo, $filtered_cms)
    {
        $sectionname = get_section_name($course, $section);

        if (empty($filtered_cms)) {
            return ''; // Se la sezione non contiene moduli filtrati, non renderizzarla.
        }

        // Genera l'elenco delle attività.
        $sectioncontent = '';
        foreach ($filtered_cms as $cmid) {
            $cm = $modinfo->cms[$cmid];
            $icon = $this->output->pix_icon('icon', $cm->modplural, 'mod_' . $cm->modname, ['class' => 'activityicon']);
            $link = html_writer::link($cm->url, $icon . ' ' . $cm->name, ['class' => 'activityinstance']);
            $sectioncontent .= html_writer::tag('li', $link, ['class' => 'activity ' . $cm->modname]);
        }

        // Racchiudi il contenuto della sezione.
        $sectioncontent = html_writer::tag('ul', $sectioncontent, ['class' => 'section img-text']);

        // Racchiudi tutto in un div della sezione.
        return html_writer::tag(
            'div',
            html_writer::tag('div', $sectionname, ['class' => 'sectionname']) .
                html_writer::tag('div', $sectioncontent, ['class' => 'content']),
            ['class' => 'course-section']
        );
    }
}
