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
 * AMD code for the frequently used comments chooser for the marking guide grading form.
 *
 * @module     mod_verbalfeedback/questionnaire
 * @class      view
 * @copyright  2020 Kevin Tippenhauer <kevin.tippenhauer@bfh.ch>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
define(['jquery',
    'core/templates',
    'core/notification',
    'core/ajax',
    'core/str',
    'core/modal_save_cancel',
    'core/modal_events'
], function($, Templates, Notification, Ajax, Str, Modal, ModalEvents) {

    var responses = [];

    let editor;
    const getEditor = function() {
        if (editor) {
            return editor;
        }
        if ($('.editor_atto').length > 0) {
            editor = 'atto';
        } else if (window.tinyMCE) {
            editor = 'tiny';
        } else {
            editor = 'textarea';
        }
        return editor;
    };

    const setComment = function(row, classSelector, comment, append = false) {
        if (getEditor() === 'atto') {
            const editorcontent = row.find(classSelector + '.editor_atto_content');
            if (append) {
                editorcontent.append("<ul><li>" + comment + "</li></ul>");
                return;
            }
            editorcontent.html(comment);
            return;
        }
        const commentId = row.find(classSelector).attr('id');
        if (commentId) {
            const $input = $('#' + commentId);
            if (getEditor() === 'tiny') {
                if (append) {
                    window.tinyMCE.get(commentId).insertContent('<ul><li>' + comment + '</li></ul><br/>');
                    return;
                }
                window.tinyMCE.get(commentId).setContent(comment);
                return;
            }
            if (append) {
                const oldComment = $input.val();
                if (oldComment.trim() !== '') {
                    $input.val(oldComment + "\n\n" + comment);
                    return;
                }
            }
            $input.val(comment);
        }
    };

    const getComment = function(row, classSel) {
        if (getEditor() === 'atto') {
            let comment = row.find(classSel + '.editor_atto_content').html();
            return comment.replace(/<[^>]+>/g, '').trim() === '' ? '' : comment; // Drop empty comments.
        }
        const commentId = row.find(classSel).attr('id');
        if (commentId) {
            let comment = '';
            if (getEditor() === 'tiny') {
                comment = window.tinyMCE.get(commentId).getContent();
            } else {
                comment = $('#' + commentId).val();
            }
            return comment.replace(/<[^>]+>/g, '').trim() === '' ? '' : comment; // Drop empty comments.
        }
        return '';
    };

    var questionnaire = function() {
        this.registerEvents();

        // Prefill responses array.
        $('[data-region="question-row"]').each(function() {
            responses[$(this).data('criterionid')] = {
                criterionid: $(this).data('criterionid'),
                value: null,
                studentcomment: "",
                privatecomment: ""
            };
        });

        let questionnaireTable = $('[data-region="questionnaire"]');

        if (questionnaireTable.data('preview') == true) { // Dont use '===' as $preview is '1'  not 'true'.
          // Do not look for existing submission on preview page.
          return;
        }

        let fromUser = questionnaireTable.data('fromuserid');
        let toUser = questionnaireTable.data('touserid');
        let verbalfeedbackId = questionnaireTable.data('verbalfeedbackid');
        let submissionId = questionnaireTable.data('submissionid');

        let promises = Ajax.call([
            {
                methodname: 'mod_verbalfeedback_get_responses',
                args: {
                    verbalfeedbackid: verbalfeedbackId,
                    fromuserid: fromUser,
                    touserid: toUser,
                    submissionid: submissionId
                }
            }
        ]);

        promises[0].done(function(result) {
            $.each(result.responses, function() {
              let response = this;
                responses[response.criterionid].criterionid = response.criterionid;
                responses[response.criterionid].value = response.value;
                responses[response.criterionid].studentcomment = response.studentcomment;
                responses[response.criterionid].privatecomment = response.privatecomment;

                $('[data-region="question-row"]').each(function() {
                    if ($(this).data('criterionid') === response.criterionid) {
                      let options = $(this).find('.scaleoption');
                        if (options) {
                            options.each(function() {
                                // Mark selected option as selected.
                                let selected = $(this).find('label');
                                if (selected.data('value') === response.value) {
                                    selected.removeClass('badge-secondary');
                                    selected.removeClass('badge-info');
                                    selected.addClass('badge-success');
                                } else if (selected.data('value') === "" && response.value === null) {
                                    selected.removeClass('badge-secondary');
                                    selected.removeClass('badge-info');
                                    selected.addClass('badge-success');
                                }
                            });
                        }
                        if (response.studentcomment !== '') {
                            setComment($(this), '.student-comment', response.studentcomment);
                        }
                        if (response.privatecomment !== '') {
                            setComment($(this), '.private-comment', response.privatecomment);
                        }
                    }
                });
            });
        }).fail(Notification.exception);
    };

    questionnaire.prototype.registerEvents = function() {
        $('.scaleoption').click(function(e) {
            e.preventDefault();

            let row = $(this).parents('[data-region="question-row"]');
            let options = row.find('label');

            // Deselect the option that has been selected.
            $.each(options, function() {
                if ($(this).hasClass('badge-success')) {
                    $(this).removeClass('badge-success');
                    $(this).addClass('badge-secondary');

                    var forId = $(this).attr('for');
                    var optionRadio = $("#" + forId);
                    optionRadio.removeAttr('checked');
                }
            });

            // Mark selected option as selected.
            let selected = $(this).find('label');
            selected.removeClass('badge-secondary');
            selected.removeClass('badge-info');
            selected.addClass('badge-success');

            // Mark hidden radio button as checked.
            let radio = $("#" + selected.attr('for'));
            radio.attr('checked', 'checked');
            let criterionid = row.data('criterionid');

            // Add this selected value to the array of responses.
            if (selected.data('value') === "") { // === is necessary because == "0" equals true;
                responses[criterionid].value = null;
            } else {
                responses[criterionid].value = selected.data('value');
            }
        });

        $('.scaleoptionlabel').hover(function(e) {
            e.preventDefault();

            if (!$(this).hasClass('badge-success')) {
                if ($(this).hasClass('badge-secondary')) {
                    $(this).removeClass('badge-secondary');
                    $(this).addClass('badge-info');
                } else {
                    $(this).addClass('badge-secondary');
                    $(this).removeClass('badge-info');
                }
            }
        });

        $('.detail-scaleoption').click(function(e) {
            e.preventDefault();

            let row = $(this).parents('[data-region="detailed-rating"]');
            let value = $(this).find('.detail-scaleoptionlabel').data("value");
            setComment(row, '.student-comment', value, true);
        });

        $('.detail-scaleoptionlabel').hover(function(e) {
            e.preventDefault();

            if (!$(this).hasClass('badge-success')) {
                if ($(this).hasClass('badge-secondary')) {
                    $(this).removeClass('badge-secondary');
                    $(this).addClass('badge-info');
                } else {
                    $(this).addClass('badge-secondary');
                    $(this).removeClass('badge-info');
                }
            }
        });

        $("#save-feedback").click(function() {
            saveResponses(false);
        });

        $("#submit-feedback").click(function() {
            saveResponses(true);
        });

        $(".btn-detail-rating").click(function(e) {
            e.preventDefault();
            let row = $(this).parents('[data-region="question-row"]');
            let detailedRating = row.find(".detailed-rating");
            if (detailedRating.hasClass("hidden")) {
                detailedRating.removeClass("hidden");
                $(this).html("−");
            } else {
                detailedRating.addClass("hidden");
                $(this).html("+");
            }

        });
    };

    /**
     * Save the responses.
     *
     * @param {boolean} finalise
     */
    function saveResponses(finalise) {

        $('.student-comment').each(function() {
            let row = $(this).parents('[data-region="question-row"]');
            responses[row.data('criterionid')].studentcomment = getComment(row, '.student-comment');
        });
        $('.private-comment').each(function() {
            let row = $(this).parents('[data-region="question-row"]');
            responses[row.data('criterionid')].privatecomment = getComment(row, '.private-comment');
        });

        let questionnaireTable = $('[data-region="questionnaire"]');
        let toUser = questionnaireTable.data('touserid');
        let toUserFullname = questionnaireTable.data('tousername');
        let verbalfeedbackId = questionnaireTable.data('verbalfeedbackid');
        let submissionId = questionnaireTable.data('submissionid');
        let anonymous = questionnaireTable.data('anonymous');

        if (anonymous && finalise) {
            // Show confirmation dialogue to anonymise the feedback responses.
            let messageStrings = [
                {
                    key: 'finaliseanonymousfeedback',
                    component: 'mod_verbalfeedback'
                },
                {
                    key: 'confirmfinaliseanonymousfeedback',
                    component: 'mod_verbalfeedback',
                    param: {
                        'name': toUserFullname
                    }
                }
            ];

            Str.get_strings(messageStrings, 'mod_verbalfeedback').done(function(messages) {
                showConfirmationDialogue(messages[0], messages[1], verbalfeedbackId, submissionId, toUser, responses, finalise);
            }).fail(Notification.exception);
        } else {
            // Just save the responses.
            submitResponses(verbalfeedbackId, submissionId, toUser, responses, finalise);
        }
    }

    /**
     * Send the responses to the server.
     *
     * @param {number} verbalfeedbackId
     * @param {number} submissionId
     * @param {number} toUser
     * @param {array} responses
     * @param {boolean} finalise
     */
    function submitResponses(verbalfeedbackId, submissionId, toUser, responses, finalise) {
        let responseObjects = [];
        for (const tuple of Object.entries(responses)) {
          if (tuple[1] !== null) {
            responseObjects.push(tuple[1]);
          }
        }

        let promises = Ajax.call([
            {
                methodname: 'mod_verbalfeedback_save_responses',
                args: {
                    verbalfeedbackid: verbalfeedbackId,
                    submissionid: submissionId,
                    touserid: toUser,
                    responses: responseObjects,
                    complete: finalise
                }
            }
        ]);

        promises[0].done(function(response) {
          let messageStrings = [
                {
                    key: 'responsessaved',
                    component: 'mod_verbalfeedback'
                },
                {
                    key: 'errorresponsesavefailed',
                    component: 'mod_verbalfeedback'
                }
            ];

            Str.get_strings(messageStrings).done(function(messages) {
              let notificationData = {};
                if (response.result) {
                    notificationData.message = messages[0];
                    notificationData.type = "success";
                } else {
                    notificationData.message = messages[1];
                    notificationData.type = "error";
                }
                Notification.addNotification(notificationData);
            }).fail(Notification.exception);

            window.location = response.redirurl;
        }).fail(Notification.exception);
    }

    /**
     * Renders the confirmation dialogue to submit and finalise the responses.
     *
     * @param {string} title
     * @param {string} confirmationMessage
     * @param {number} verbalfeedbackId
     * @param {number} submissionId
     * @param {number} toUser
     * @param {Array} responses
     * @param {boolean} finalise
     */
    function showConfirmationDialogue(title, confirmationMessage, verbalfeedbackId, submissionId, toUser, responses, finalise) {
      let confirmButtonTextPromise = Str.get_string('finalise', 'mod_verbalfeedback');
        let confirmModalPromise = Modal.create({
            title: title,
            body: confirmationMessage,
            large: true,
        });
        $.when(confirmButtonTextPromise, confirmModalPromise).done(function(confirmButtonText, modal) {
            modal.setSaveButtonText(confirmButtonText);

            // Display the dialogue.
            modal.show();

            // On hide handler.
            modal.getRoot().on(ModalEvents.hidden, function() {
                // Empty modal contents when it's hidden.
                modal.setBody('');
            });

            modal.getRoot().on(ModalEvents.save, function() {
                submitResponses(verbalfeedbackId, submissionId, toUser, responses, finalise);
            });
        });

    }

    return questionnaire;
});
