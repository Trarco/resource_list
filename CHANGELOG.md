# Changelog
## [2.2.1] - 2025-07-18

### Added
* **Combined Filtering Support (AND Logic)**: Users can now apply both *Tag* and *Activity Type* filters simultaneously. Activities are displayed only if they match **both** the selected tags and types.
* **Localized Activity Type Labels**: The filter dropdown and button now show the translated display name of each activity type, matching the Moodle language.

### Changed
* **Filter Reset Behavior**: Resetting one filter (e.g., tags) no longer invalidates the other (e.g., type). This ensures consistent filtering behavior across both filters.

## [2.2.0] - 2025-07-18

### Added
* **Frontend Type Filter**: Introduced a second filter (dropdown with checkboxes) allowing users to filter activities by type (e.g., Quiz, Page, Video) directly from the block interface.
* The filter is only shown if the block setting "Enable frontend type filter" is enabled (default: true).
* Each block instance now provides an isolated list of available types based on the current visible activities.
* The type filter logic is implemented in a separate AMD module (`type_filter.js`) for modularity.

### Changed
* **Dropdown UI**: Tag and type filters are now rendered side-by-side in two columns using `div.col` for improved usability on larger screens.
* **Template Data**: `availabletypes` and `showtypefrontendfilter` are now passed to the Mustache context only if the relevant setting is enabled.
* **Activity Visibility Logic**: Activities hidden from students are now completely excluded at the source — they are no longer passed to the renderer or shown in the block, regardless of filtering.

### Fixed
* **Visibility Check**: Fixed an issue where activities hidden to students (but visible to teachers/admins) were still shown in the block even when logged in as a student.
* **Filter Isolation**: Ensured that both tag and type filters work independently per block instance by using unique `data-uniqid` identifiers throughout the DOM and JavaScript.

## [2.1.0] - 2025-07-16
### Added
* **Frontend Tag Filter**: A dynamic UI filter (checkbox dropdown) has been added to allow users to filter activities by Moodle tags directly from the block interface.
* The filter is only displayed if:
  - The global Moodle setting for tags is enabled (usetags), and
  - The instance-level option "Enable frontend tag filter" is checked (default: enabled).
* The dropdown displays all tags found in the block's activities and highlights selected ones.
* Sections with no visible activities after filtering are automatically hidden.

### Changed
* JS Scoping by Block Instance: Frontend filters now correctly isolate their scope per block instance (data-uniqid), preventing interference between multiple blocks on the same course page.
* Template Context Cleanup: Ensured that showtagfrontendfilter and availabletags are only passed to Mustache if tags are enabled site-wide and relevant for the block.

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
