// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.
/* jshint node: true, browser: false */
/* eslint-env node */

/**
 * @copyright 2020 Kevin Tippenhauer <kevin.tippenhauer@bfh.ch>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/**
 * Grunt configuration
 */
"use strict";

module.exports = function(grunt) {

    // We need to include the core Moodle grunt file too, otherwise we can't run tasks like "amd".
    require("grunt-load-gruntfile")(grunt);
    grunt.loadGruntfile("../../Gruntfile.js");

    // Load all grunt tasks.
    grunt.loadNpmTasks("grunt-contrib-watch");
    grunt.loadNpmTasks("grunt-contrib-clean");
    grunt.loadNpmTasks('grunt-contrib-uglify');

    grunt.initConfig({
      watch: {
          // If any .less file changes in directory "less" then run the "less" task.
          files: "amd/src/*.js",
          tasks: ["uglify"]
      },
      uglify: {
        development: {
          options: {
            sourceMap: {
              includeSources: true,
            },
          },
          files: [{
            expand: true,
            src: ['*.js'],
            dest: 'amd/build',
            ext: '.min.js',
            cwd: 'amd/src',
            // rename: function (dst, src) {
              // To keep the source js files and make new files as `*.min.js`:
              // return dst + '/' + src.replace('.js', '.min.js');
              // Or to override to src:
              // return src;
            // },
          }]
        }
      },
      // less: {
      //     // Production config is also available.
      //     development: {
      //         options: {
      //             // Specifies directories to scan for @import directives when parsing.
      //             // Default value is the directory of the source, which is probably what you want.
      //             paths: ["less/"],
      //             compress: true
      //         },
      //         files: {
      //             "styles.css": "less/styles.less"
      //         }
      //     },
      // }
  });

    // The default task (running "grunt" in console).
    grunt.registerTask("default", ["eslint:amd", "uglify"]);
};
