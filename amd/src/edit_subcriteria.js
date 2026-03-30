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
 * Javscript for copying and removing subcriteria form fields.
 *
 * @module     mod_verbalfeedback/edit_subcriteria
 * @copyright  2026 Stephan Robotta <stephan.robotta@bfh.ch>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
export const editSubcriteria = () => {
    const getContainer = e => {
        while (e) {
            if (e.classList.contains('subrating-actions')) {
                break;
            }
            e = e.parentElement;
        }
        return e;
    };

    const getNextSequence = () => {
        let el = [...document.querySelectorAll('h4.subrating-header')].pop();
        while (el) {
            if (el.id && el.id.indexOf('fitem_id_header') === 0) {
                const m = el.id.match(/_(\d+)$/);
                if (m) {
                    return ++m[1];
                }
            }
            el = el.parentNode;
        }
        return "999";
    };

    const renumberHeadline = () => {
        document.querySelectorAll('h4.subrating-header').forEach(
            (el, idx) => {
                el.innerHTML = el.innerHTML.replace(/\d+\s*$/, `${++idx}`);
            }
        );
    };

    const delSubrating = event => {
        let container = getContainer(event.target);
        if (!container) {
            return;
        }
        let t = container;
        while (container) {
            t = container.previousSibling;
            container.parentElement.removeChild(container);
            if (container.tagName === 'HR') {
                break;
            }
            container = t;
        }
        renumberHeadline();
    };

    const cpSubrating = event => {
        let container = getContainer(event.target);
        if (!container) {
            return;
        }
        const no = getNextSequence();
        const btnplus = document.getElementById('fitem_id_subrating_add_fields');
        const nodes = [];
        let n;
        while (container) {
            n = container.cloneNode(true);
            fixAttrs(n, no);
            nodes.unshift(n);
            if (container.tagName === 'HR') {
                break;
            }
            container = container.previousSibling;
        }
        for (let i = 0; i < nodes.length; i++) {
            btnplus.parentNode.insertBefore(nodes[i], btnplus);
        }
        renumberHeadline();
        removeEvents();
        addEvents();
        const isr = document.querySelector('input[name="subrating_repeats"]');
        if (isr) {
            isr.value = parseInt(isr.value) + 1;
        }
        // Focus first new element.
        while (n) {
            if (n.id && n.id.indexOf('fitem_id_subrating_') === 0) {
                n.querySelector('input').focus();
                return;
            }
            n = n.nextSibling;
        }
    };

    const fixAttrs = function(n, no) {
        if (n.hasAttribute('id')) {
            n.id = n.id.replace(/_\d+$/g, `_${no}`).replace(/_\d+_/g, `_${no}_`);
        }
        n.querySelectorAll('[id]').forEach(el => {
            el.id = el.id.replace(/_\d+$/g, `_${no}`).replace(/_\d+_/g, `_${no}_`);
        });
        n.querySelectorAll('[for]').forEach(el => {
            el.setAttribute('for', el.getAttribute('for').replace(/_\d+$/g, `_${no}`).replace(/_\d+_/g, `_${no}_`));
        });
        n.querySelectorAll('[name]').forEach(el => {
            el.name = el.name.replace(/\[\d+\]$/g, `[${no}]`).replace(/_\d+/g, `_${no}`);
        });

    };

    const addEvents = () => {
        document.querySelectorAll('button.copy-subrating').forEach(
            e => e.addEventListener('click', cpSubrating)
        );
        document.querySelectorAll('button.delete-subrating').forEach(
            e => e.addEventListener('click', delSubrating)
        );
    };

    const removeEvents = () => {
        document.querySelectorAll('button.copy-subrating').forEach(
            e => e.removeEventListener('click', cpSubrating)
        );
        document.querySelectorAll('button.delete-subrating').forEach(
            e => e.removeEventListener('click', delSubrating)
        );
    };
    addEvents();
};