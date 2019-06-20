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
 * SASS TESTER
 *
 * @package    local_sasstest
 * @copyright  Bas Brands <bas@moodle.com>
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
require_once(__DIR__ . '/../../config.php');

require_login(1);

// For the messaging template
$template = optional_param('template', 'messaging', PARAM_TEXT);
$parsed = optional_param('parsed', 1, PARAM_INT);

$preset = file_get_contents($CFG->dirroot . '/local/uitest/sass/variables.scss');

if (!empty($_POST)) {
	$preset .= "\n" . $_POST['variable'] . ': ' .  $_POST['value'] . ";\n";
}

$scss = $CFG->dirroot . '/local/uitest/sass/' . $template . '.scss';

$compiler = new core_scss();
$compiler->prepend_raw_scss($preset);
$compiler->set_file($scss);
$css = $compiler->to_css();

$renderable = (object)[];
$renderable->inputcss = $preset . "\n" . file_get_contents($scss);
$renderable->outputcss = $css;
if (!empty($_POST)) {
	if (isset($_POST['variable'])) {
		$renderable->variable = $_POST['variable'];
	}
	if (isset($_POST['value'])) {
		$renderable->value = $_POST['value'];
	}
}

if ($parsed) {
	$parser = new core_cssparser($css);
	$csstree = $parser->parse();
}
if ($parsed) {
	$renderable->parsed = $csstree;
}

$url = new moodle_url('/local/uitest/index.php');
$url->param('template', $template);

$renderable->baseurl = $url;

$PAGE->set_url($url);
$PAGE->set_pagelayout('incourse');
$PAGE->set_title('Sass Testing');
$PAGE->set_heading('Sass Testing');
$PAGE->navbar->add('Sass Testing');

$templates = [
	'messaging' => 'messaging',
	'navbar' => 'navbar',
	'sass' => 'sass',
	'example' => 'example',
	'toast' => 'toast'
];
echo $OUTPUT->header();

$s = new single_select(new moodle_url('/local/uitest/index.php'), 'template', $templates, $template, null);
$s->label = 'templates';
$s->class = 'templates';

$renderable->chooser = $OUTPUT->render($s);

echo $OUTPUT->render_from_template('local_uitest/' .$template, $renderable);

echo $OUTPUT->footer();