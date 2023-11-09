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

namespace qtype_ordering\output;

use templatable;
use renderable;
use question_attempt;

/**
 * Renderable class for the description of the correct response to a given question attempt.
 *
 * @package    qtype_ordering
 * @copyright  2023 Mihail Geshoski <mihail@moodle.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class correct_response implements templatable, renderable {

    /** @var question_attempt $questionattempt The question attempt object. */
    protected $questionattempt;

    /**
     * The class constructor.
     *
     * @param question_attempt $context The context object.
     */
    public function __construct(question_attempt $questionattempt) {
        $this->questionattempt = $questionattempt;
    }

    /**
     * Export the data for the mustache template.
     *
     * @param \renderer_base $output renderer to be used to render the action bar elements.
     * @return array
     */
    public function export_for_template(\renderer_base $output): array {

        $data = [];
        $question = $this->questionattempt->get_question();
        $correctresponse = $question->correctresponse;
        $data['hascorrectresponse'] = !empty($correctresponse);
        // Early return if a correct response does not exist.
        if (!$data['hascorrectresponse']) {
            return $data;
        }

        $step = $this->questionattempt->get_last_step();
        // The correct response should be displayed only for partially correct or incorrect answers.
        $data['showcorrect'] = $step->get_state() == 'gradedpartial' || $step->get_state() == 'gradedwrong';
        // Early return if the correct response should not be displayed.
        if (!$data['showcorrect']) {
            return $data;
        }

        $data['orderinglayoutclass'] = $question->get_ordering_layoutclass();
        $data['correctanswers'] = [];

        foreach ($correctresponse as $position => $answerid) {
            $answer = $question->answers[$answerid];
            $answertext = $question->format_text($answer->answer, $answer->answerformat,
                $this->questionattempt, 'question', 'answer', $answerid);

            $data['correctanswers'][] = [
                'answertext' => $answertext
            ];
        }

        return $data;
    }
}