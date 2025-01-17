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
 * Defines the editing form for the ordering question type.
 *
 * @package    qtype_ordering
 * @copyright  2013 Gordon Bateson (gordon.bateson@gmail.com)
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

// Prevent direct access to this script.
defined('MOODLE_INTERNAL') || die();

// Include required files.
require_once($CFG->dirroot.'/question/type/ordering/question.php');

/**
 * Ordering editing form definition
 *
 * @copyright  2013 Gordon Bateson (gordon.bateson@gmail.com)
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class qtype_ordering_edit_form extends question_edit_form {

    /** Rows count in answer field */
    const TEXTFIELD_ROWS = 2;

    /** Cols count in answer field */
    const TEXTFIELD_COLS = 60;

    /** Number of answers in question by default */
    const NUM_ITEMS_DEFAULT = 6;

    /** Minimum number of answers to show */
    const NUM_ITEMS_MIN = 3;

    /** Number of answers to add on demand */
    const NUM_ITEMS_ADD = 1;

    /**
     * qtype is plugin name without leading "qtype_"
     *
     * @return string
     */
    public function qtype(): string {
        return 'ordering';
    }

    /**
     * Plugin name is class name without trailing "_edit_form"
     *
     * @return string
     */
    public function plugin_name(): string {
        return 'qtype_ordering';
    }

    /**
     * Add question-type specific form fields.
     *
     * @param MoodleQuickForm $mform the form being built.
     */
    public function definition_inner($mform): void {

        // Cache this plugins name.
        $plugin = 'qtype_ordering';

        // Field for layouttype.
        $name = 'layouttype';
        $label = get_string($name, $plugin);
        $options = qtype_ordering_question::get_layout_types();
        $mform->addElement('select', $name, $label, $options);
        $mform->addHelpButton($name, $name, $plugin);
        $mform->setDefault($name, $this->get_default_value($name, qtype_ordering_question::LAYOUT_VERTICAL));

        // Field for selecttype.
        $name = 'selecttype';
        $label = get_string($name, $plugin);
        $options = qtype_ordering_question::get_select_types();
        $mform->addElement('select', $name, $label, $options);
        $mform->addHelpButton($name, $name, $plugin);
        $mform->setDefault($name, $this->get_default_value($name, qtype_ordering_question::SELECT_ALL));

        // Field for selectcount.
        $name = 'selectcount';
        $label = get_string($name, $plugin);

        $mform->addElement('text', $name, $label, ['size' => 2]);
        $mform->setDefault($name, qtype_ordering_question::MIN_SUBSET_ITEMS);
        $mform->setType($name, PARAM_INT);
        // Hide the field if 'Item selection type' is set to select all items.
        $mform->hideIf($name, 'selecttype', 'eq', qtype_ordering_question::SELECT_ALL);
        $mform->addHelpButton($name, $name, $plugin);
        $mform->addRule($name, null, 'numeric', null, 'client');

        // Field for gradingtype.
        $name = 'gradingtype';
        $label = get_string($name, $plugin);
        $options = qtype_ordering_question::get_grading_types();
        $mform->addElement('select', $name, $label, $options);
        $mform->addHelpButton($name, $name, $plugin);
        $mform->setDefault($name, $this->get_default_value($name, qtype_ordering_question::GRADING_ABSOLUTE_POSITION));

        // Field for showgrading.
        $name = 'showgrading';
        $label = get_string($name, $plugin);
        $options = [0 => get_string('hide'), 1 => get_string('show')];
        $mform->addElement('select', $name, $label, $options);
        $mform->addHelpButton($name, $name, $plugin);
        $mform->setDefault($name, $this->get_default_value($name, 1));

        $name = 'numberingstyle';
        $label = get_string($name, $plugin);
        $options = qtype_ordering_question::get_numbering_styles();
        $mform->addElement('select', $name, $label, $options);
        $mform->addHelpButton($name, $name, $plugin);
        $mform->setDefault($name, $this->get_default_value($name, qtype_ordering_question::NUMBERING_STYLE_DEFAULT));

        $elements = [];
        $options = [];

        $mform->addElement('header', 'answersheader', get_string('draggableitems', $plugin));
        $mform->setExpanded('answersheader', true);

        $name = 'answer';
        $elements[] = $mform->createElement('editor', $name, get_string('draggableitemno', $plugin),
            $this->get_editor_attributes(), $this->get_editor_options());
        $elements[] = $mform->createElement('submit', $name . 'removeeditor', get_string('removeeditor', $plugin),
            ['onclick' => 'skipClientValidation = true;']);
        $options[$name] = ['type' => PARAM_RAW];

        $this->add_repeat_elements($mform, $name, $elements, $options);

        // Adjust HTML editor and removal buttons.
        $this->adjust_html_editors($mform, $name);

        // Adding feedback fields (=Combined feedback).
        $this->add_combined_feedback_fields(true);

        // Adding interactive settings (=Multiple tries).
        $this->add_interactive_settings(false, true);
    }

    /**
     * Returns answer repeats count
     *
     * @param stdClass $question
     * @return int
     */
    protected function get_answer_repeats(stdClass $question): int {
        if (isset($question->id)) {
            $repeats = count($question->options->answers);
        } else {
            $repeats = self::NUM_ITEMS_DEFAULT;
        }
        if ($repeats < self::NUM_ITEMS_MIN) {
            $repeats = self::NUM_ITEMS_MIN;
        }
        return $repeats;
    }

    /**
     * Returns editor attributes
     *
     * @return array
     */
    protected function get_editor_attributes(): array {
        return [
            'rows' => self::TEXTFIELD_ROWS,
            'cols' => self::TEXTFIELD_COLS,
        ];
    }

    /**
     * Returns editor options
     *
     * @return array
     */
    protected function get_editor_options(): array {
        return [
            'context' => $this->context,
            'maxfiles' => EDITOR_UNLIMITED_FILES,
            'noclean' => true,
        ];
    }

    /**
     * Resets editor format to specified
     *
     * @param MoodleQuickForm_editor $editor
     * @param int|string $format
     * @return int
     */
    protected function reset_editor_format(MoodleQuickForm_editor $editor, int|string $format = FORMAT_MOODLE): int {
        $value = $editor->getValue();
        $value['format'] = $format;
        $editor->setValue($value);
        return $format;
    }

    /**
     * Adjust HTML editor and removal buttons.
     *
     * @param MoodleQuickForm $mform
     * @param string $name
     */
    protected function adjust_html_editors(MoodleQuickForm $mform, string $name): void {

        // Cache the number of formats supported
        // by the preferred editor for each format.
        $count = [];

        if (isset($this->question->options->answers)) {
            $ids = array_keys($this->question->options->answers);
        } else {
            $ids = [];
        }

        $defaultanswerformat = get_config('qtype_ordering', 'defaultanswerformat');

        $repeats = 'count'.$name.'s'; // E.g. countanswers.
        if ($mform->elementExists($repeats)) {
            // Use mform element to get number of repeats.
            $repeats = $mform->getElement($repeats)->getValue();
        } else {
            // Determine number of repeats by object sniffing.
            $repeats = 0;
            while ($mform->elementExists($name."[$repeats]")) {
                $repeats++;
            }
        }

        for ($i = 0; $i < $repeats; $i++) {
            $editor = $mform->getElement($name."[$i]");
            $id = $ids[$i] ?? 0;

            // The old/new name of the button to remove the HTML editor
            // old : the name of the button when added by repeat_elements
            // new : the simplified name of the button to satisfy "no_submit_button_pressed()" in lib/formslib.php.
            $oldname = $name.'removeeditor['.$i.']';
            $newname = $name.'removeeditor_'.$i;

            // Remove HTML editor, if necessary.
            if (optional_param($newname, 0, PARAM_RAW)) {
                $format = $this->reset_editor_format($editor);
                $_POST['answer'][$i]['format'] = $format; // Overwrite incoming data.
            } else if ($id) {
                $format = $this->question->options->answers[$id]->answerformat;
            } else {
                $format = $this->reset_editor_format($editor, $defaultanswerformat);
            }

            // Check we have a submit button - it should always be there !!
            if ($mform->elementExists($oldname)) {
                if (!isset($count[$format])) {
                    $editor = editors_get_preferred_editor($format);
                    $count[$format] = $editor->get_supported_formats();
                    $count[$format] = count($count[$format]);
                }

                if ($count[$format] > 1) {
                    $mform->removeElement($oldname);
                } else {
                    $submit = $mform->getElement($oldname);
                    $submit->setName($newname);
                }
                $mform->registerNoSubmitButton($newname);
            }
        }
    }

    /**
     * Create the form elements required by one hint.
     *
     * @param string $withclearwrong Whether this quesiton type uses the 'Clear wrong' option on hints.
     * @param string $withshownumpartscorrect Whether this quesiton type uses the 'Show num parts correct' option on hints.
     * @return array Form field elements for one hint.
     */
    protected function get_hint_fields($withclearwrong = false, $withshownumpartscorrect = false): array {
        $mform = $this->_form;

        $repeated = [];
        $repeated[] = $mform->createElement('editor', 'hint', get_string('hintn', 'question'),
            ['rows' => 5], $this->editoroptions);
        $repeatedoptions['hint']['type'] = PARAM_RAW;

        $optionelements = [];

        if ($withshownumpartscorrect) {
            $optionelements[] = $mform->createElement('advcheckbox', 'hintshownumcorrect', '',
                get_string('shownumpartscorrect', 'question'));
        }

        $optionelements[] = $mform->createElement('advcheckbox', 'hintoptions', '',
            get_string('highlightresponse', 'qtype_ordering'));

        if (count($optionelements)) {
            $repeated[] = $mform->createElement('group', 'hintoptions',
                get_string('hintnoptions', 'question'), $optionelements, null, false);
        }

        return [$repeated, $repeatedoptions];
    }

    /**
     * Perform any preprocessing needed on the data passed to {@see set_data()}
     * before it is used to initialise the form.
     * @param object $question the data being passed to the form.
     * @return stdClass $question the modified data.
     */
    public function data_preprocessing($question): stdClass {

        $question = parent::data_preprocessing($question);
        $question = $this->data_preprocessing_answers($question, true);

        // Preprocess feedback.
        $question = $this->data_preprocessing_combined_feedback($question, true);

        $question = $this->data_preprocessing_hints($question, false, true);

        // Preprocess answers and fractions.
        $question->answer = [];
        $question->fraction = [];

        if (empty($question->options->answers)) {
            $answerids = [];
        } else {
            $answerids = array_keys($question->options->answers);
        }

        $defaultanswerformat = get_config('qtype_ordering', 'defaultanswerformat');
        $repeats = $this->get_answer_repeats($question);
        for ($i = 0; $i < $repeats; $i++) {

            if ($answerid = array_shift($answerids)) {
                $answer = $question->options->answers[$answerid];
            } else {
                $answer = (object) ['answer' => '', 'answerformat' => $defaultanswerformat];
                $answerid = 0;
            }

            if (empty($question->id)) {
                $question->answer[$i] = $answer->answer;
            } else {
                $itemid = file_get_submitted_draft_itemid("answer[$i]");
                $format = $answer->answerformat;
                $text = file_prepare_draft_area($itemid, $this->context->id, 'question', 'answer',
                    $answerid, $this->editoroptions, $answer->answer);
                $question->answer[$i] = [
                    'text' => $text,
                    'format' => $format,
                    'itemid' => $itemid,
                ];
            }
            $question->fraction[$i] = ($i + 1);
        }

        // Defining default values.
        $names = [
            'layouttype' => qtype_ordering_question::LAYOUT_VERTICAL,
            'selecttype' => qtype_ordering_question::SELECT_ALL,
            'selectcount' => qtype_ordering_question::MIN_SUBSET_ITEMS,
            'gradingtype' => qtype_ordering_question::GRADING_ABSOLUTE_POSITION,
            'showgrading' => 1,  // 1 means SHOW.
            'numberingstyle' => qtype_ordering_question::NUMBERING_STYLE_DEFAULT,
        ];
        foreach ($names as $name => $default) {
            $question->$name = $question->options->$name ?? $this->get_default_value($name, $default);
        }

        return $question;
    }

    /**
     * Perform the necessary preprocessing for the hint fields.
     *
     * @param object $question The data being passed to the form.
     * @param bool $withclearwrong Clear wrong hints.
     * @param bool $withshownumpartscorrect Show number correct.
     * @return stdClass The modified data.
     */
    protected function data_preprocessing_hints($question, $withclearwrong = false, $withshownumpartscorrect = false): stdClass {
        if (empty($question->hints)) {
            return $question;
        }
        parent::data_preprocessing_hints($question, $withclearwrong, $withshownumpartscorrect);

        $question->hintoptions = [];
        foreach ($question->hints as $hint) {
            $question->hintoptions[] = $hint->options;
        }

        return $question;
    }

    /**
     * Form validation
     *
     * @param array $data array of ("fieldname"=>value) of submitted data
     * @param array $files array of uploaded files "element_name"=>tmp_file_path
     * @return array of "element_name"=>"error_description" if there are errors,
     *         or an empty array if everything is OK (true allowed for backwards compatibility too).
     */
    public function validation($data, $files): array {
        $errors = [];
        $plugin = 'qtype_ordering';

        $minsubsetitems = qtype_ordering_question::MIN_SUBSET_ITEMS;
        // Make sure the entered size of the subset is no less than the defined minimum.
        if ($data['selecttype'] != qtype_ordering_question::SELECT_ALL && $data['selectcount'] < $minsubsetitems) {
            $errors['selectcount'] = get_string('notenoughsubsetitems', $plugin, $minsubsetitems);
        }

        // Identify duplicates and report as an error.
        $answers = [];
        $answercount = 0;
        foreach ($data['answer'] as $answer) {
            if (is_array($answer)) {
                $answer = $answer['text'];
            }
            if ($answer = trim($answer)) {
                if (in_array($answer, $answers)) {
                    $i = array_search($answer, $answers);
                    $item = get_string('draggableitemno', $plugin);
                    $item = str_replace('{no}', $i + 1, $item);
                    $item = html_writer::link("#id_answer_$i", $item);
                    $a = (object) ['text' => $answer, 'item' => $item];
                    $errors["answer[$answercount]"] = get_string('duplicatesnotallowed', $plugin, $a);
                } else {
                    $answers[] = $answer;
                }
                $answercount++;
            }
        }

        // If there are no answers provided, show error message under first 2 answer boxes
        // If only 1 answer provided, show error message under second answer box.
        if ($answercount < 2) {
            $errors['answer[1]'] = get_string('notenoughanswers', $plugin, 2);

            if ($answercount == 0) {
                $errors['answer[0]'] = get_string('notenoughanswers', $plugin, 2);
            }
        }

        // If adding a new ordering question, update defaults.
        if (empty($errors) && empty($data['id'])) {
            $fields = [
                'layouttype', 'selecttype', 'selectcount',
                'gradingtype', 'showgrading', 'numberingstyle',
            ];
            foreach ($fields as $field) {
                if (array_key_exists($field, $data)) {
                    question_bank::get_qtype($this->qtype())->set_default_value($field, $data[$field]);
                }
            }
        }

        return $errors;
    }

    /**
     * Given a preference item name, returns the full
     * preference name of that item for this plugin
     *
     * @param string $name Item name
     * @return string full preference name
     */
    protected function get_my_preference_name(string $name): string {
        return $this->plugin_name()."_$name";
    }

    /**
     * Get array of countable item types
     *
     * @param string $type
     * @param int $max
     * @return array (type => description)
     */
    protected function get_addcount_options(string $type, int $max = 10): array {

        // Cache plugin name.
        $plugin = $this->plugin_name();

        // Generate options.
        $options = [];
        for ($i = 1; $i <= $max; $i++) {
            if ($i == 1) {
                $options[$i] = get_string('addsingle'.$type, $plugin);
            } else {
                $options[$i] = get_string('addmultiple'.$type.'s', $plugin, $i);
            }
        }
        return $options;
    }

    /**
     * Add repeated elements with a button allowing a selectable number of new elements
     *
     * @param MoodleQuickForm $mform the Moodle form object
     * @param string $type
     * @param array $elements
     * @param array $options
     * @return void, but will update $mform
     */
    protected function add_repeat_elements(MoodleQuickForm $mform, string $type, array $elements, array $options): void {

        // Cache plugin name.
        $plugin = $this->plugin_name();

        // Cache element names.
        $types = $type.'s';
        $addtypes = 'add'.$types;
        $counttypes = 'count'.$types;
        $addtypescount = $addtypes.'count';
        $addtypesgroup = $addtypes.'group';

        $repeats = $this->get_answer_repeats($this->question);

        $count = optional_param($addtypescount, self::NUM_ITEMS_ADD, PARAM_INT);

        $label = ($count == 1 ? 'addsingle'.$type : 'addmultiple'.$types);
        $label = get_string($label, $plugin, $count);

        $this->repeat_elements($elements, $repeats, $options, $counttypes, $addtypes, $count, $label, true);

        // Remove the original "Add xxx" button ...
        $mform->removeElement($addtypes);

        // ... and replace it with "Add" button + select group.
        $options = $this->get_addcount_options($type);
        $mform->addGroup([
            $mform->createElement('submit', $addtypes, get_string('add')),
            $mform->createElement('select', $addtypescount, '', $options),
        ], $addtypesgroup, '', ' ', false);

        // Set default value and type of select element.
        $mform->setDefault($addtypescount, $count);
        $mform->setType($addtypescount, PARAM_INT);
    }
}
