<?php
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

/**
 * Plugin strings are defined here.
 *
 * @package     mod_verbalfeedback
 * @category    string
 * @copyright   2020 Kevin Tippenhauer <kevin.tippenhauer@bfh.ch>
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();
$string['activity'] = 'Activity';
$string['addanewquestion'] = 'Add a new question';
$string['additem'] = 'Add item';
$string['allowundodecline'] = 'Allow participants to undo declined feedback submissions';
$string['allparticipants'] = 'All course participants';
$string['anonymous'] = 'Anonymous';
$string['averagerating'] = 'Average rating:';
$string['backtoverbalfeedbackdashboard'] = 'Back to the verbal feedback dashboard';
$string['calendarend'] = '{$a} closes';
$string['calendarstart'] = '{$a} opens';
$string['categoryheader'] = 'Category header';
$string['categoryplural'] = 'Categories';
$string['categoryweight'] = 'Category weight';
$string['closebeforeopen'] = 'You have specified a close date before the open date.';
$string['commentfromuser'] = '{$a->comment} ({$a->fromuser})';
$string['comments'] = 'Comments';
$string['confirmfinaliseanonymousfeedback'] = 'This will anonymise your responses on your feedback for {$a->name}. You will no longer be able to change your responses once it has been done. Proceed?';
$string['confirmquestiondeletion'] = 'Are you sure you want to delete this question?';
$string['course'] = 'Course';
$string['criteria'] = 'Criteria';
$string['criterion'] = 'Criterion';
$string['dataformatinvalid'] = 'The file format that was specified for downloading this report is either invalid or not enabled. Please select a valid file format.';
$string['decline'] = 'Decline';
$string['declinefeedback'] = 'Decline feedback';
$string['declineheading'] = 'Declining verbal feedback for {$a}';
$string['declinereason'] = 'Please provide a reason why you are declining this feedback.';
$string['declinereasonplaceholdertext'] = 'Enter your reason here... (Optional)';
$string['deletecategory'] = 'Delete category';
$string['deletecriterion'] = 'Delete criterion';
$string['deleteitem'] = 'Delete item';
$string['deletelanguage'] = 'Delete language';
$string['deletequestion'] = 'Delete question';
$string['deletetemplate'] = 'Delete template';
$string['detailrating'] = 'Detail rating';
$string['done'] = 'Done';
$string['download'] = 'Download';
$string['downloadreportas'] = 'Download feedback report as...';
$string['downloadtemplate'] = 'Download template';
$string['editcategory'] = 'Edit category';
$string['editcriterion'] = 'Edit criterion';
$string['edititems'] = 'Edit verbal feedback items';
$string['editlanguage'] = 'Edit language';
$string['editquestion'] = 'Edit question';
$string['edittemplate'] = 'Edit template';
$string['enableselfreview'] = 'Enable self-review';
$string['entercomment'] = 'Enter your comment here.';
$string['enterquestion'] = 'Enter question text...';
$string['errorblankdeclinereason'] = 'Required.';
$string['errorblankquestion'] = 'Required.';
$string['errorcannotadditem'] = 'Cannot add the verbal feedback item.';
$string['errorcannotparticipate'] = 'You cannot participate in this verbal feedback activity.';
$string['errorcannotrespond'] = 'You cannot give feedback within this verbal feedback activity.';
$string['errorcannotupdateitem'] = 'Cannot update the verbal feedback item.';
$string['errorcannotviewallreports'] = 'You cannot view other participants results.';
$string['errorinvalidstatus'] = 'Invalid status';
$string['erroritemnotfound'] = 'The verbal feedback item was not found.';
$string['errornocaptoedititems'] = 'Sorry, but you don\'t have the capability to edit verbal feedback items.';
$string['errornotenrolled'] = 'You need to be enrolled in this course in order to be able to participate in this verbal feedback activity.';
$string['errornothingtodecline'] = 'There is no feedback to decline to.';
$string['errornotingroup'] = 'You need to be in a group in order to be able to participate in this verbal feedback activity. Please contact your course administrator.';
$string['errorquestionstillinuse'] = 'This question cannot be deleted as it is still being used by at least one verbal feedback instance.';
$string['errorreportnotavailable'] = 'Your feedback report is not yet available.';
$string['errorresponsesavefailed'] = 'An error has occured while the responses are being saved. Please try again later.';
$string['errorroleconflict'] = 'The current user is student and teacher at the same time for this instance, which is not allowed.';
$string['errorverbalfeedbacknotfound'] = 'verbal feedback not found.';
$string['factor'] = 'Factor';
$string['feedbackgiven'] = 'Feedback given';
$string['feedbackreceived'] = 'Feedback received';
$string['feedbacksurvey'] = 'Feedback survey for {$a}';
$string['finaliseanonymousfeedback'] = 'Finalise anonymous feedback';
$string['finalize'] = 'Finalize evaluation';
$string['finalresult'] = 'Final result';
$string['gotoquestionbank'] = 'Go to the verbal question bank';
$string['id'] = 'ID';
$string['instancealreadyclosed'] = 'The verbal feedback activity has already closed.';
$string['instancenotready'] = 'Click the "Make available" button to release the questionnaire to the teachers after editing the verbal feedback items.';
$string['instancenotreadystudents'] = 'The verbal feedback activity is not yet ready. Please try again later.';
$string['instancenotyetopen'] = 'The verbal feedback activity is not yet open.';
$string['instancenowready'] = 'The verbal feedback activity is now ready for use by the participants!';
$string['languageplural'] = 'Languages';
$string['listcategories'] = 'List categories';
$string['listcriteria'] = 'List criteria';
$string['managetemplates'] = 'Manage templates';
$string['messageafterdecline'] = 'Feedback declined.';
$string['modulename'] = 'Verbal feedback';
$string['modulename_help'] = '###### Key features
- Multi-lecturer assessment: Supports simultaneous feedback from multiple lecturers, with results aggregated and averaged automatically.
- Configurable criteria catalogue: Offers a vast set of customizable criteria, including multiple templates. Weights can be adjusted, and criteria can be excluded by assigning 0% weight.
- Flexible feedback input: Lecturers can insert predefined text blocks into feedback fields and edit them later for personalization.
- Private comments: Allows lecturers to add private notes that are not visible to students.
- Student-friendly results: Provides an attractive, clear overview of feedback.

###### Ways to use it
- Presentation grading: Evaluate student presentations systematically using weighted criteria and pre-phrased feedback sentences.
- Collaborative assessment: Enable multiple lecturers to provide feedback on the same presentation, ensuring fairness and diverse perspectives.
- Template-based evaluation: Use predefined templates for different types of presentations or create custom ones for specific courses.
- Constructive feedback delivery: Combine structured scoring with personalized comments for comprehensive student feedback.';
$string['modulename_summary'] = 'Supports structured, multi-lecturer feedback with customizable criteria, predefined comments, and clear, student-friendly results.';
$string['modulenameplural'] = 'Verbal feedbacks';
$string['moveitemdown'] = 'Move item down';
$string['moveitemup'] = 'Move item up';
$string['multiplier'] = 'Multiplier';
$string['negative'] = 'Negative';
$string['newcategory'] = 'New category';
$string['newcriterion'] = 'New criterion';
$string['newlanguage'] = 'New language';
$string['newtemplate'] = 'New template';
$string['nocriterion'] = 'No criterion';
$string['noitemsyet'] = 'The verbal feedback activity doesn\'t have items yet. Add items by clicking on "Edit verbal feedback items".';
$string['notapplicableabbr'] = 'N/A';
$string['notemplate'] = 'No template';
$string['numrespondents'] = 'Number of respondents';
$string['openafterclose'] = 'You have specified an open date after the close date';
$string['percentage'] = 'Percentage';
$string['pick'] = 'Pick';
$string['pickfromquestionbank'] = 'Pick a question from the question bank';
$string['placeholderquestion'] = "Enter question text";
$string['pluginadministration'] = 'Verbal feedback administration';
$string['pluginname'] = 'Verbal feedback';
$string['position'] = 'Position';
$string['positive'] = 'Positive';
$string['preserveids'] = 'Preserve IDs?';
$string['previewinfo'] = 'Questionnaire preview, click <a href="{$a}">here</a> to return to the previous view.';
$string['privacy:metadata:instanceid'] = 'The ID of the verbal feedback instance';
$string['privacy:metadata:verbalfeedback_item'] = 'The ID of the verbal feedback item';
$string['privacy:metadata:verbalfeedback_response'] = 'This table stores the responses of the feedback respondent to the feedback questions to the feedback recipient';
$string['privacy:metadata:verbalfeedback_response:value'] = 'The value of the respondent\'s response to the feedback question';
$string['privacy:metadata:verbalfeedback_submission'] = 'This table stores the information about the statuses of verbal feedback submissions between the participants';
$string['privacy:metadata:verbalfeedback_submission:fromuserid'] = 'The user ID of the person giving the feedback';
$string['privacy:metadata:verbalfeedback_submission:remarks'] = 'The reason why the respondent declined to give feedback to the feedback recipient';
$string['privacy:metadata:verbalfeedback_submission:status'] = 'The status of the feedback submission';
$string['privacy:metadata:verbalfeedback_submission:touserid'] = 'The user ID of the feedback recipient';
$string['privatecomment'] = 'Private comment';
$string['providefeedback'] = 'Provide feedback';
$string['qtypecomment'] = 'Comment';
$string['qtypeinvalid'] = 'Invalid question type';
$string['qtyperated'] = 'Rated';
$string['question'] = 'Question';
$string['questioncategory'] = 'Category';
$string['questiontext'] = 'Question text';
$string['questiontype'] = 'Question type';
$string['ratingaverage'] = 'Average rating';
$string['ratings'] = 'Ratings';
$string['rel_after'] = 'Release after the activity has closed';
$string['rel_closed'] = 'Closed to participants';
$string['rel_manual'] = 'Manual release';
$string['rel_open'] = 'Open to participants';
$string['release'] = 'Release reports to participants';
$string['release_close'] = 'Close reports to participants';
$string['releasetype'] = 'Releasing';
$string['releasetype_help'] = 'Whether to let the participants view the report of the feedback given to them.
<ul>
<li>Closed to participants. Participants cannot view their own feedback report. Only those with the capability to manage the verbal feedback activity (e.g. teacher, manager, admin) can view the participants\' feedback reports.</li>
<li>Open to participants. Participants can view their own feedback report any time.</li>
<li>Manual release. Participants can view their own feedback report when released by a user who has the capability to manage the verbal feedback activity.</li>
<li>Release after the activity has closed. Participants can view their own feedback report after the activity has ended.</li>
</ul>';
$string['removecategories'] = 'Remove categories';
$string['removecriteria'] = 'Remove criteria';
$string['removeresponses'] = 'Remove responses';
$string['removesubmissions'] = 'Remove submissions';
$string['removesubratings'] = 'Remove subratings';
$string['reportimage'] = 'Report PDF header logo';
$string['reportimage_desc'] = 'The image to display as a header logo in the verbal feedback downloaded pdfs.';
$string['responses'] = 'Responses';
$string['responsessaved'] = 'Your responses have been saved.';
$string['saveandreturn'] = 'Save and return';
$string['scale'] = 'Scale';
$string['scaleagree'] = 'Agree';
$string['scaledisagree'] = 'Disagree';
$string['scalenotapplicable'] = 'Not applicable';
$string['scalesomewhatagree'] = 'Somewhat agree';
$string['scalesomewhatdisagree'] = 'Somewhat disagree';
$string['scalestronglyagree'] = 'Strongly agree';
$string['scalestronglydisagree'] = 'Strongly disagree';
$string['selectparticipants'] = 'Select participants';
$string['startend'] = 'Start - End';
$string['status'] = 'Status';
$string['statuscompleted'] = 'Completed';
$string['statusdeclined'] = 'Declined';
$string['statusinprogress'] = 'In progress';
$string['statuspending'] = 'Pending';
$string['statusviewonly'] = 'View only';
$string['student'] = 'Student';
$string['studentcomment'] = 'Student comment';
$string['submissions'] = 'Submissions';
$string['subrating'] = 'Subrating';
$string['subratingplural'] = 'Subratings';
$string['switchtouser'] = 'Switch to user...';
$string['teachers'] = 'Teachers';
$string['template'] = 'Template';
$string['templatecategoryplural'] = 'Template categories';
$string['templatecriteriaplural'] = 'Template criteria';
$string['templateplural'] = 'Templates';
$string['text'] = 'Text';
$string['title'] = 'Verbal feedback';
$string['titlelabel'] = 'Title';
$string['titlemanageitems'] = 'Manage verbal feedback items';
$string['todo'] = 'To Do';
$string['totalpercentage'] = 'Total';
$string['undodecline'] = 'Undo decline';
$string['valuation'] = 'Valuation';
$string['value'] = 'Value';
$string['verbalfeedback:addinstance'] = 'Add a new verbal feedback instance';
$string['verbalfeedback:can_participate'] = 'Participate in a verbal feedback';
$string['verbalfeedback:can_respond'] = 'Respond in a verbal feedback';
$string['verbalfeedback:complete'] = 'Complete a verbal feedback';
$string['verbalfeedback:edititems'] = 'Edit verbal feedback items';
$string['verbalfeedback:editquestions'] = 'Edit verbal feedback questions';
$string['verbalfeedback:managetemplates'] = 'Manage verbal feedback templates';
$string['verbalfeedback:mapcourse'] = 'Map verbal feedback to course';
$string['verbalfeedback:receive_rating'] = 'User can be rated';
$string['verbalfeedback:receivemail'] = 'Receive verbal feedback email';
$string['verbalfeedback:view'] = 'View verbal feedback';
$string['verbalfeedback:view_all_reports'] = 'View verbal feedback reports of all students';
$string['verbalfeedback:viewanalysepage'] = 'View verbal feedback analysis';
$string['verbalfeedbackcategories'] = 'Verbal feedback categories';
$string['verbalfeedbackcriteria'] = 'Verbal feedback criteria';
$string['verbalfeedbacklanguages'] = 'Verbal feedback languages';
$string['verbalfeedbacksettings'] = 'Verbal feedback settings';
$string['verbalfeedbacktemplates'] = 'Verbal feedback templates';
$string['verynegative'] = 'Very negative';
$string['verypositive'] = 'Very positive';
$string['viewfeedbackforuser'] = 'View feedback for user';
$string['viewfeedbackreport'] = 'View feedback report';
$string['weight'] = 'Weight';
$string['weightedaverage'] = 'Weighted Ø';
