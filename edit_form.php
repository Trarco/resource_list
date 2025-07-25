<?php
defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot . '/blocks/edit_form.php');

class block_resource_list_edit_form extends block_edit_form
{
    protected function specific_definition($mform)
    {
        global $DB;

        // Add the block title field.
        $mform->addElement('text', 'config_title', get_string('title', 'block_resource_list'));
        $mform->setDefault('config_title', get_string('pluginname', 'block_resource_list'));
        $mform->setType('config_title', PARAM_TEXT);

        // Tab "Description".
        $mform->addElement('header', 'descriptionsettings', get_string('descriptionsettings', 'block_resource_list'));

        // Ensure block context is set for the editor.
        if (!isset($this->block->context)) {
            $this->block->context = context_system::instance();
        }

        // Add the description editor.
        $mform->addElement('editor', 'config_description', get_string('description', 'block_resource_list'), null, array(
            'maxfiles' => EDITOR_UNLIMITED_FILES,
            'noclean' => true,
            'context' => $this->block->context,
        ));
        $mform->setType('config_description', PARAM_RAW);

        // Add an option to select activity types dynamically from the database.
        $sql = "SELECT DISTINCT name 
                FROM {modules} 
                WHERE visible = 1
                ORDER BY name ASC";
        $modules = $DB->get_records_sql($sql);

        // Create a dynamic list of activity types.
        $activitytypes = array('all' => get_string('allactivities', 'block_resource_list'));
        foreach ($modules as $module) {
            $activitytypes[$module->name] = get_string('modulename', $module->name);
        }

        // Add the activity type select field with multiple selection enabled.
        $mform->addElement('select', 'config_activitytype', get_string('filterbyactivity', 'block_resource_list'), $activitytypes, array('multiple' => true));
        // Set default value (e.g., 'all' is selected by default).
        $mform->setDefault('config_activitytype', array('all'));

        // Add a checkbox to choose if the activities are grouped by sections.
        $mform->addElement('advcheckbox', 'config_groupsections', get_string('groupsections', 'block_resource_list'));
        $mform->setDefault('config_groupsections', 1); // Default: checked.

        // Add a checkbox to remove indentation
        $mform->addElement('advcheckbox', 'config_removeindentation', get_string('removeindentation', 'block_resource_list'));
        $mform->setDefault('config_removeindentation', 0); // Default: unchecked.

        $mform->addElement('advcheckbox', 'config_enabletagfrontendfilter', get_string('enabletagfrontendfilter', 'block_resource_list'));
        $mform->addHelpButton('config_enabletagfrontendfilter', 'enabletagfrontendfilter', 'block_resource_list');
        $mform->setDefault('config_enabletagfrontendfilter', 1);

        $mform->addElement('advcheckbox', 'config_enabletypefrontendfilter', get_string('enabletypefrontendfilter', 'block_resource_list'));
        $mform->addHelpButton('config_enabletypefrontendfilter', 'enabletypefrontendfilter', 'block_resource_list');
        $mform->setDefault('config_enabletypefrontendfilter', 1);


        if (!get_config('core', 'usetags')) {
            $mform->freeze('config_enabletagfrontendfilter');
            $mform->addElement('static', 'tagsdisabledinfo', '', get_string('tagsnotenabled', 'block_resource_list'));
        }


        // Recupera i filtri salvati dalla configurazione del blocco
        $filters = [];
        if (isset($this->block->config->activitytitlefilters) && is_array($this->block->config->activitytitlefilters)) {
            // Filtra stringhe non vuote e rimuovi spazi
            $filters = array_filter(array_map('trim', $this->block->config->activitytitlefilters));
        }

        // Se non c'è nessun filtro valido, mostra almeno un campo
        if (empty($filters)) {
            $filters = [''];
        }

        // Numero iniziale di filtri
        $repeats = count($filters);

        // Definizione degli elementi ripetibili
        $repeatarray = [];
        $repeatarray[] = $mform->createElement('text', 'config_activitytitlefilters', get_string('activitytitlefilter', 'block_resource_list'));
        $repeatarray[] = $mform->createElement('static', 'activitytitlefilters_info', '', get_string('activitytitlefilterinfo', 'block_resource_list'));

        // Impostazione tipo per il campo ripetibile
        $mform->setType('config_activitytitlefilters', PARAM_TEXT);

        // Crea gli elementi ripetibili
        $this->repeat_elements(
            $repeatarray,
            $repeats,
            [],
            'activitytitlefilters_repeats',
            'activitytitlefilters_add_fields',
            1,
            get_string('addmorefilters', 'block_resource_list'),
            true
        );

        // Inizializza i valori salvati (deve avvenire dopo `repeat_elements`)
        foreach ($filters as $i => $filter) {
            $mform->setDefault("config_activitytitlefilters[$i]", $filter);
            $mform->addHelpButton("config_activitytitlefilters[$i]", 'activitytitlefilters', 'block_resource_list');
        }

        $mform->addElement('advcheckbox', 'config_excludefiltermatches', get_string('excludefiltermatches', 'block_resource_list'));
        $mform->addHelpButton('config_excludefiltermatches', 'excludefiltermatches', 'block_resource_list');
    }
}
