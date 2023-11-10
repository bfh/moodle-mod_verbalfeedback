## Dependencies for PHP

The Verbal Feedback Plugin depends on external libraries:
* `goat1000/svggraph` for creating SVG charts
* `dallgoot/yaml` for Yaml parsing


The Yaml library in version 0.3.2 doesn't work with PHP 8.1 anymore.
However, the plugin needs to support all PHP versions that are
supported in Moodle where this plugin can be installed.

Therefore, the plugin has the two directories:
* `dependency_74x`
* `dependency_81x`

that contain basically the composer information which libraries need
to be installed for either PHP < 8.0 (in directory `dependency_74x`)
or for PHP 8.1 onwards (in `dependency_81x`).

