# Changelog

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