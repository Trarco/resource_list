<?php
defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot . '/blocks/edit_form.php');

class block_resource_list_edit_form extends block_edit_form
{
    protected function specific_definition($mform)
    {
        // Aggiungi il campo per il titolo del blocco.
        $mform->addElement('text', 'config_title', get_string('title', 'block_resource_list'));
        $mform->setDefault('config_title', get_string('pluginname', 'block_resource_list'));
        $mform->setType('config_title', PARAM_TEXT);

        // Tab "Descrizione".
        $mform->addElement('header', 'descriptionsettings', get_string('descriptionsettings', 'block_resource_list'));

        // Controllo contesto per il campo descrizione.
        if (!isset($this->block->context)) {
            $this->block->context = context_system::instance();
        }

        // Campo per la descrizione.
        $mform->addElement('editor', 'config_description', get_string('description', 'block_resource_list'), null, array(
            'maxfiles' => EDITOR_UNLIMITED_FILES,
            'noclean' => true,
            'context' => $this->block->context,
        ));
        $mform->setType('config_description', PARAM_RAW);

        // Aggiungi un'opzione per selezionare il tipo di attività.
        $mform->addElement('select', 'config_activitytype', get_string('filterbyactivity', 'block_resource_list'), array(
            'all' => get_string('allactivities', 'block_resource_list'),
            'assign' => get_string('assignments', 'block_resource_list'),
            'quiz' => get_string('quizzes', 'block_resource_list'),
            'forum' => get_string('forums', 'block_resource_list'),
            'resource' => get_string('resources', 'block_resource_list'),
            'page' => get_string('pages', 'block_resource_list'),
            'scorm' => get_string('scorm', 'block_resource_list'),
        ), array('multiple' => true)); // Usa multiple=true come opzione

        // Imposta il valore di default (ad esempio, 'all' è selezionato di default).
        $mform->setDefault('config_activitytype', array('all'));
    }
}
