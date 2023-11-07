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

use qtype_ordering_question;
use qtype_ordering_renderer;
use templatable;
use renderable;
use question_attempt;

/**
 * Renderable class for the displaying the grade detail of the response.
 *
 * @package    qtype_ordering
 * @copyright  2023 Ilya Tregubov <ilya@moodle.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class specific_grade_detail_feedback implements templatable, renderable {

    /** @var qtype_ordering_renderer $renderer The question attempt object. */
    protected $renderer;

    /** @var question_attempt $questionattempt The question attempt object. */
    protected $questionattempt;

    /**
     * The class constructor.
     *
     * @param question_attempt $questionattempt The question attempt.
     */
    public function __construct($renderer, question_attempt $questionattempt) {
        $this->renderer = $renderer;
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

        // Decide if we should show grade explanation for "partial" or "wrong" states.
        // This should detect "^graded(partial|wrong)$" and possibly others.
        $show = false;
        if ($step = $this->questionattempt->get_last_step()) {
            $show = preg_match('/(partial|wrong)$/', $step->get_state());
        }
        $data['show'] = $show;
        if (!$show) {
            return $data;
        }

        $plugin = 'qtype_ordering';

        // Show grading details if they are required.
        if ($question->options->showgrading) {
            // Fetch grading type.
            $gradingtype = $question->options->gradingtype;
            $gradingtype = qtype_ordering_question::get_grading_types($gradingtype);

            // Format grading type, e.g. Grading type: Relative to next item, excluding last item.
            if ($gradingtype) {
                $data['gradingtype'] = get_string('gradingtype', $plugin) . ': ' . $gradingtype;
            }

            // Fetch grade details and score details.
            if ($currentresponse = $question->currentresponse) {

                $totalscore = 0;
                $totalmaxscore = 0;

                $data['orderinglayoutclass'] = $question->get_ordering_layoutclass();

                // Format scoredetails, e.g. 1 /2 = 50%, for each item.
                foreach ($currentresponse as $position => $answerid) {
                    if (array_key_exists($answerid, $question->answers)) {
                        $score = $this->renderer->get_ordering_item_score($question, $position, $answerid);
                        list($score, $maxscore, $fraction, $percent, $class, $img) = $score;
                        if (!isset($maxscore)) {
                            $score = get_string('noscore', $plugin);
                        } else {
                            $totalscore += $score;
                            $totalmaxscore += $maxscore;
                        }
                        $data['scoredetails'][] = [
                            'score' => $score,
                            'maxscore' => $maxscore,
                            'percent' => $percent,
                        ];
                    }
                }

                if ($totalmaxscore == 0) {
                    unset($data['scoredetails']); // All or nothing.
                } else {
                    // Format gradedetails, e.g. 4/6 = 67%.
                    if ($totalscore == 0) {
                        $data['gradedetails'] = 0;
                    } else {
                        $data['gradedetails'] = round(100 * $totalscore / $totalmaxscore, 0);
                    }
                    $data['totalscore'] = $totalscore;
                    $data['totalmaxscore'] = $totalmaxscore;
                }
            }
        }
        return $data;
    }
}
