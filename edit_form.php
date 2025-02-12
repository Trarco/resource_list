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
            $activitytypes[$module->name] = get_string($module->name, 'block_resource_list');
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

        // Add a checkbox to display only quizzes containing "Quiz di verifica" in their title.
        $mform->addElement('advcheckbox', 'config_showverificationquiz', get_string('showverificationquiz', 'block_resource_list'));
        $mform->setDefault('config_showverificationquiz', 0); // Default: unchecked.
        $mform->addHelpButton('config_showverificationquiz', 'showverificationquiz_help', 'block_resource_list');

        // Add a checkbox to display only quizzes containing "Test di Autovalutazione" in their title.
        $mform->addElement('advcheckbox', 'config_showselfassessmentquiz', get_string('showselfassessmentquiz', 'block_resource_list'));
        $mform->setDefault('config_showselfassessmentquiz', 0); // Default: unchecked.
        $mform->addHelpButton('config_showselfassessmentquiz', 'showselfassessmentquiz_help', 'block_resource_list');

        // Add a checkbox to display only quizzes containing "Esercitazione del caso" in their title.
        $mform->addElement('advcheckbox', 'config_showcasestudyquiz', get_string('showcasestudyquiz', 'block_resource_list'));
        $mform->setDefault('config_showcasestudyquiz', 0); // Default: unchecked.
        $mform->addHelpButton('config_showcasestudyquiz', 'showcasestudyquiz_help', 'block_resource_list');
    }
}
