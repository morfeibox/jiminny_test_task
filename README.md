# jiminny_test_task
 Waveform Generator (Backend Take-home Task)
## Requirements
Find out more about the project at https://github.com/jiminny/join-the-team/blob/master/backend-task.md
## Local Environment
### Setup
1. Clone this repo in your local host's directory of choice.

### Usage
1. Open a http://localhost:8080/ and the_name_of_the_directory_where_project_it_is at your browser.

## Testing with PHPUnit
* In order to test functionalities of the project run `composer install` from terminal within your project root folder.
This will install PHPUnit dependency from `composer.json`
* Change necessary functions visibility at `/include/AnalizeText.php` named `invertToActiveSpeech`, `getlongestMonologue` & `normalizeValues` to `public`.
* Run the following command `vendor/bin/phpunit tests/AnalizeTextTest.php` from terminal within your project root folder.

