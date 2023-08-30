Change Log
All notable changes to this project will be documented in this file, formatted via [this recommendation](https://keepachangelog.com/).

## [1.8.0] - 2023-08-15
### Added
- The Captcha field in the Form Builder can now be searched by name or related keywords.

### Changed
- Minimum WPForms version supported is 1.8.3.

## [1.7.0] - 2023-03-13
### Added
- Compatibility with the upcoming WPForms 1.8.1.

### Fixed
- Improved compatibility with Elementor popups v3.9+.

## [1.6.0] - 2022-09-21
## Added
- Custom Captcha's "Math" type is now supported and works properly inside Elementor popups.

### Changed
- Minimum WPForms version is now 1.7.5.

## Fixed
- Empty questions and answers had incorrect validation.

## [1.5.0] - 2022-08-29
### Changed
- Do not add a second question with an empty question and answer values that were added by default.
- Empty questions are now removed from the list on form save.

### Fixed
- Implemented various fixes to prevent questions with an empty question or answer values from being saved or displayed.

## [1.4.0] - 2022-03-16
### Added
- Compatibility with WPForms 1.7.1 to avoid displaying Captcha field on Edit Entry page.
- Compatibility with WPForms 1.7.3 and Form Revisions.

## [1.3.2] - 2021-09-07
### Changed
- Prevent saving empty values for "Question and Answer" Captcha fields.

### Fixed
- Compatibility with WPForms 1.6.8 and the updated Form Builder.
- Incorrect "Math" Captcha preview in the Block Editor (Gutenberg).
- Incorrect "Question and Answer" Captcha preview in Builder.

## [1.3.1] - 2021-03-31
### Fixed
- Empty Form Builder preview when the "Questions and Answer" type has been selected and the first question has been removed.
- "Questions and Answers" section may not be displayed for some users in the Form Builder when the "Questions and Answers" type has been selected.

## [1.3.0] - 2019-07-23
### Added
- Complete translations for French and Portuguese (Brazilian).

### Fixed
- `wpforms_math_captcha` filter running too early.

## [1.2.0] - 2019-02-06
### Added
- Complete translations for Spanish, Italian, Japanese, and German.

### Fixed
- Typos, grammar, and other i18n related issues.

## [1.1.2] - 2018-12-27
### Changed
- Captcha field display priority in the form builder.

## [1.1.1] - 2018-03-19
### Fixed
- JS file not loading on frontend, causing error.

## [1.1.0] - 2018-03-15
### Changed
- Refactored addon and improved code.

### Fixed
- Zero (0) math equation answers not being allowed.

## [1.0.3] - 2017-06-20
### Fixed
- Issue with QA PHP validation.

## [1.0.2] - 2017-06-15
### Fixed
- Missing input class causing equation validation issues.

## [1.0.1] - 2017-06-13
### Changed
- Updated captcha field to new field class format.

### Fixed
- Field size issue in the form builder when using Math format.

## [1.0.0] - 2016-08-03
- Initial release.
