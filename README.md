# verbalfeedback
[![Moodle Plugin CI](https://github.com/bfh/moodle-mod_verbalfeedback/workflows/Moodle%20Plugin%20CI/badge.svg?branch=main)](https://github.com/bfh/moodle-mod_verbalfeedback/actions?query=workflow%3A%22Moodle+Plugin+CI%22+branch%3Amain)

Verbal Feedback allows structured feedback of student's presentations by one or several persons.

# Use cases
## Main use cases

## Teacher rating
![Teacher rating stundents](./docs/img/core_use_case_teacher_students.png)

1-n Teachers rate n students and teachers can access the rating of all students.

## Peer review
![Peer review with student groups](./docs/img/core_use_case_peer_review.png)

Students group A evaluates group B which evaluates group C.

## Installing via uploaded ZIP file ##

1. Log in to your Moodle site as an admin and go to _Site administration >
   Plugins > Install plugins_.
2. Upload the ZIP file with the plugin code. You should only be prompted to add
   extra details if your plugin type is not automatically detected.
3. Check the plugin validation report and finish the installation.

## Installing manually ##

The plugin can be also installed by putting the contents of this directory to

    {your/moodle/dirroot}/mod/verbalfeedback

Afterwards, log in to your Moodle site as an admin and go to _Site administration >
Notifications_ to complete the installation.

Alternatively, you can run

    $ php admin/cli/upgrade.php

to complete the installation from the command line.

## verbalfeedback
https://github.com/bfh/moodle-mod_verbalfeedback
