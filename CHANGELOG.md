# Changelog

## \[2.0.0] - 2025-07-11

### Added

* **Custom Text Filters for Activity Titles**: Introduced a repeatable field allowing users to define multiple keyword filters. Activities are shown if their title contains *any* of the specified keywords (OR logic).
* **Exclude Filter Matches Option**: Added a checkbox that allows users to invert the logic of the title filters — displaying all activities *except* those that match the keywords.
* **Help Message for Custom Filters**: Added inline help text (tooltip) for each filter field, explaining how to use the keyword matching (e.g., to filter activities containing "Simulazione").
* **Persistent Filter Fields**: Configuration form now properly pre-populates the custom filters when editing an existing block instance.
* **Mustache Template Integration**: Replaced raw HTML output with a clean Mustache template (`activity_list.mustache`) to render sections and activities, improving maintainability and separation of concerns.

### Changed

* **Filter Logic**: Extended the filtering system to apply to **all activity types**, not just quizzes.
* **Activity Name Comparison**: Normalized title filtering to use lowercase comparison with `strpos()` for case-insensitive matching.

### Fixed

* **Form Rendering**: Fixed an issue where the repeatable elements for custom filters were not displaying previously saved values when editing the block.
* **Config Reference Error**: Corrected use of `$this->block->config` instead of `$this->config` inside the `edit_form.php`, preventing a fatal error during form rendering.

## [1.0.0] - 2025-02-04 

### Added
- **Block Title Customization**: Added the ability to configure and display a custom block title in the `edit_form.php`.
- **Activity Filters**: Added three new filters to display only specific quizzes based on the title:
  - **Verification Quiz**: Shows quizzes containing either "Quiz di verifica" or "Test di verifica".
  - **Self-Assessment Quiz**: Shows quizzes containing either "Test di autovalutazione" or "Quiz di autovalutazione".
  - **Case Study Quiz**: Shows quizzes containing "Esercitazione del caso".
- **Indentation Option**: Added an option to remove the indentation from activities via a checkbox in the block configuration.

### Changed
- **Dynamic Title Handling**: The block now correctly handles and applies a custom title without duplicating it elsewhere on the page.
- **Custom CSS Loading**: The block now dynamically loads a `styles.css` file, if present in the `styles` directory.

### Fixed
- **Indentation Bug**: Fixed an issue where indentation was not correctly applied when the "group sections" setting was disabled.
- **Filter Logic**: Improved filter logic to handle variations in quiz titles (e.g., both "Quiz di verifica" and "Test di verifica").

### Documentation
- **Help Texts**: Added help tooltips in the block configuration for the new filter checkboxes to guide users on how each filter works.