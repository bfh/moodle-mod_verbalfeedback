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
 * @module     mod_verbalfeedback/edit_items
 * @class      edit_items
 * @copyright  2020 Kevin Tippenhauer <kevin.tippenhauer@bfh.ch>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
define([
    'jquery',
    'core/templates',
    'core/notification',
    'core/ajax',
    'core/str',
    'core/yui'
], function($, Templates, Notification, Ajax) {

    /**
     * List of action selectors.
     *
     * @type {{CATEGORY_PERCENTAGE_CHANGED: string}, {ITEM_MULTIPLIER_CHANGED: string}}
     */
    let ACTIONS = {
        CATEGORY_PERCENTAGE_CHANGED: '[data-action="change-category-percentage"]',
        ITEM_MULTIPLIER_CHANGED: '[data-action="change-item-multiplier"]',
    };

    let editItems = function() {
        this.registerEvents();
    };

    let updateSum = function() {
        let sum = 0.00;
        $('.category-percentage').each(function() {
            sum += parseFloat($(this).val());
        });

        if(sum != 1) {
            $('.category-percentage').addClass('is-invalid');
            $('#percentage-total').addClass('text-danger');
            $('#percentage-total-value').text(parseFloat(sum * 100).toFixed(2));
        } else {
            $('.category-percentage').removeClass('is-invalid');
            $('#percentage-total').removeClass('text-danger');
            $('#percentage-total-value').text(parseFloat(sum * 100).toFixed(2));
        }
    };

    editItems.callCategoryAction = function(action, elem) {
        updateSum();

        let promises = Ajax.call([
            {
                methodname: action,
                args: {
                    categoryid: elem.data('categoryid'),
                    percentage: elem.val()
                }
            }
        ]);
        promises[0].done(function(response) {
            if (response.success) {
              elem.next().stop(true, true).show().fadeOut(1000);
            } else {
              let warnings = response.warnings.join($('<br/>'));
              throw new Error(warnings);
            }
        }).fail(Notification.exception);
    };

    editItems.callItemAction = function(action, elem) {
        let promises = Ajax.call([
            {
                methodname: action,
                args: {
                    itemid: elem.data('itemid'),
                    multiplier: elem.val()
                }
            }
        ]);
        promises[0].done(function(response) {
          if (response.success) {
            elem.next().stop(true, true).show().fadeOut(1000);
          } else {
            let warnings = response.warnings.join($('<br />'));
            throw new Error(warnings);
          }
        }).fail(Notification.exception);
    };

    editItems.prototype.registerEvents = function() {

        $(ACTIONS.CATEGORY_PERCENTAGE_CHANGED).change(function(e) {
            e.preventDefault();
            editItems.callCategoryAction('mod_verbalfeedback_update_category_percentage', $(this));

        });

        $(ACTIONS.ITEM_MULTIPLIER_CHANGED).change(function(e) {
            e.preventDefault();
            if(this.value > 5) {
              this.value = 5.00;
            }
            this.value = parseFloat(this.value).toFixed(2);
            editItems.callItemAction('mod_verbalfeedback_update_item_multiplier', $(this));
        });
    };

    editItems.prototype.updatePercentageSum = updateSum;
    return editItems;
});
