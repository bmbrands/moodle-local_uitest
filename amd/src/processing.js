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
 * Overlays a container with a loader that can appear and disappear
 *
 * @module     tool_wp/loader
 * @package    tool_wp
 * @copyright  2020 Moodle Pty Ltd <support@moodle.com>
 * @author     2020 Bas Brands
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @license    Moodle Workplace License, distribution is restricted, contact support@moodle.com
 */
define(
[
    'jquery',
    'core/templates',
    'core/pubsub',
],
function(
    $,
    Templates,
    PubSub
) {

    /**
     * Event listener for showing the loader
     *
     * @param {object} root The root container for the loader.
     */
    var registerEventListeners = function(root) {

        root.addClass('position-relative');

        Templates.render('local_uitest/processing_indicator', {})
        .done(
            function(html) {
                PubSub.subscribe('wp-loader-loading', function() {
                    if (!root.hasClass('hasloader')) {
                        root.append(html).addClass('hasloader').delay(150).queue(function(){
                            $(this).addClass('showloader').dequeue();
                        });
                    }
                });

                PubSub.subscribe('wp-loader-stoploading', function() {
                    if (root.hasClass('hasloader')) {
                        root.removeClass('hasloader').removeClass('showloader');
                        root.find('.tool-wp-processing').remove();
                    }
                });
            }
        );
    };

    /**
     * Intialise the loader and wait for loading events.
     *
     * @param {object} root The root container for the loader.
     */
    var init = function(root) {
        root = $(root);
        registerEventListeners(root);
    };

    return {
        init: init
    };

});