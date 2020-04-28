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
 * Moodle UI hacking tool.
 *
 * @package    local_uitest
 * @copyright  Bas Brands <bas@moodle.com>
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
require_once(__DIR__ . '/../../config.php');

require_login(1);

// For the messaging template
$template = optional_param('template', 'example', PARAM_TEXT);
$layout = optional_param('layout', 'incourse', PARAM_RAW);

// Load the basic parts of Bootstrap
$bootstrap = file_get_contents($CFG->dirroot . '/local/uitest/sass/bootstrap.scss');

$scss = $CFG->dirroot . '/local/uitest/sass/' . $template . '.scss';

// Get all the available templates
foreach (glob($CFG->dirroot . '/local/uitest/templates/' . "*.mustache") as $filename) {
    $tmplname = str_replace('.mustache', '', basename($filename));

    // These are template for the uitest tools. They should not be selectable.
    if (in_array($tmplname, ['intro', 'injectcss', 'cssoutput', 'workshop_contacts'])) {
        continue;
    }
    $templates[$tmplname] = $tmplname;
}

$renderable = (object)[];

$css = '';

// Load custom scss for the template
if (file_exists($scss)) {
    $compiler = new core_scss();
    $compiler->prepend_raw_scss($bootstrap);
    $compiler->set_file($scss);
    $css = $compiler->to_css();
    $renderable->inputcss = file_get_contents($scss);
    $renderable->outputcss = $css;

    // Run CSS throught the css parser.
    $parser = new core_cssparser($css);
    $csstree = $parser->parse();
    $renderable->parsed = $csstree;
}

// Get data for the templates:
if (file_exists($CFG->dirroot . '/local/uitest/json/' . $template . '.json')) {
    $json = file_get_contents($CFG->dirroot . '/local/uitest/json/' . $template . '.json');
    $renderable->context = json_decode($json);
}

// Setup template selector
$s = new single_select(new moodle_url('/local/uitest/index.php'),
    'template', $templates, $template, null);
$s->label = 'Template';
$s->class = 'Template';

// Output page
$url = new moodle_url('/local/uitest/index.php');
$url->param('template', $template);
$PAGE->set_url($url);
$PAGE->set_pagelayout($layout);
$PAGE->set_title(get_string('pluginname', 'local_uitest'));
$PAGE->set_heading(get_string('pluginname', 'local_uitest'));
$PAGE->navbar->add(get_string('pluginname', 'local_uitest'));

echo $OUTPUT->header();

$renderable->chooser = $OUTPUT->render($s);

echo $OUTPUT->render_from_template('local_uitest/' .$template, $renderable);

echo $OUTPUT->footer();