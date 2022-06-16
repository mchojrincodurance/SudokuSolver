<?php

declare(strict_types=1);

namespace SudokuTest;

use Sudoku\Solution;
use PHPUnit\Framework\TestCase;
use Sudoku\Sudoku;

class SolutionShould extends TestCase
{
    const ROWS = 4;

    /**
     * @test
     */

    public function recognize_invalid_solutions()
    {
        $sudoku = $this->buildSolvableSudoku();

        $invalidSolution = $this->buildIncompleteSolutionFor($sudoku);
        $this->assertFalse($invalidSolution->isSolutionFor($sudoku));

        $invalidSolution = $this->buildWrongSolutionFor($sudoku);
        $this->assertFalse($invalidSolution->isSolutionFor($sudoku));
    }

    /**
     * @test
     * @return void
     */
    public function recognize_whether_it_is_valid(): void
    {
        $validSolution = $this->buildValidSolution();
        $invalidSolution = $this->buildInvalidSolution();

        $this->assertTrue($validSolution->isValid());
        $this->assertFalse($invalidSolution->isValid());
    }

    /**
     * @return void
     * @test
     */
    public function recognize_whether_it_is_incomplete(): void
    {
        $incompleteSolution = $this->buildIncompleteSolution();
        $this->assertFalse($incompleteSolution->isComplete());

        $completeSolution = $this->buildCompleteSolution();
        $this->assertTrue($completeSolution->isComplete());
    }

    /**
     * @return void
     * @test
     */
    public function recognize_whether_it_matches_a_sudoku(): void
    {
        $sudoku = $this->buildSolvableSudoku();

        $matchingSolution = $this->buildMatchingSolution($sudoku);
        $unmatchingSolution = $this->buildUnMatchingSolution($sudoku);

        $this->assertTrue($matchingSolution->matches($sudoku));
        $this->assertFalse($unmatchingSolution->matches($sudoku));
    }

    private function buildSolvableSudoku(): Sudoku
    {
        return new Sudoku(
            [
                [0, 2, 0, 4],
                [3, 0, 1, 2],
                [2, 3, 0, 1],
                [4, 0, 2, 0],
            ]);
    }

    /**
     * @param Sudoku $sudoku
     * @return Solution
     */
    private function buildIncompleteSolutionFor(Sudoku $sudoku): Solution
    {
        $solution = new Solution();
        for ($row = 0; $row < $sudoku->getRowCount(); $row++) {
            for ($col = 0; $col < $sudoku->getRowCount(); $col++) {
                $solution->setValueForSquare($row, $col, $sudoku->getValueForSquare($row, $col));
            }
        }

        return $solution;
    }

    private function buildWrongSolutionFor(Sudoku $sudoku)
    {
        $solution = new Solution();
        for ($row = 0; $row < $sudoku->getRowCount(); $row++) {
            for ($col = 0; $col < $sudoku->getRowCount(); $col++) {
                if ($sudoku->getValueForSquare($row, $col) === 0) {
                    $solution->setValueForSquare($row, $col, $this->getForbiddenValueFor($sudoku, $row, $col));
                } else {
                    $solution->setValueForSquare($row, $col, $sudoku->getValueForSquare($row, $col));
                }
            }
        }

        return $solution;
    }

    private function getForbiddenValueFor(Sudoku $sudoku, int $row, int $col): int
    {
        for ($i = 0; $i < $sudoku->getRowCount(); $i++) {
            if (($value = $sudoku->getValueForSquare($i, $col)) !== 0) {

                return $value;
            }
        }

        for ($j = 0; $j < $sudoku->getRowCount(); $j++ ) {
            if (($value = $sudoku->getValueForSquare($row, $j)) !== 0) {

                return $value;
            }
        }
    }

    private function buildMatchingSolution(Sudoku $sudoku): Solution
    {
        $solution = new Solution();

        for ($row = 0; $row < $sudoku->getRowCount(); $row++ ) {
            for ($col = 0; $col < $sudoku->getRowCount(); $col++ ) {
                $solution->setValueForSquare( $row, $col, $sudoku->getValueForSquare($row, $col) );
            }
        }

        return $solution;
    }

    private function buildUnMatchingSolution(Sudoku $sudoku)
    {
        $solution = new Solution();

        for ($row = 0; $row < $sudoku->getRowCount(); $row++ ) {
            for ($col = 0; $col < $sudoku->getRowCount(); $col++ ) {
                if (!$sudoku->isEmptySquare($row, $col)) {
                    $solution->setValueForSquare( $row, $col, $this->getComplementOf($sudoku->getValueForSquare($row, $col), $sudoku));
                }
            }
        }

        return $solution;
    }

    private function getComplementOf(int $value, Sudoku $sudoku): int
    {
        return $sudoku->getRowCount() - $value + 1;
    }

    private function buildIncompleteSolution(): Solution
    {
        $solution = new Solution();

        for ($row = 0; $row < self::ROWS; $row++ ) {
            for ($col = 0; $col < self::ROWS; $col++ ) {
                $solution->setValueForSquare($row, $col, 0);
            }
        }

        return $solution;
    }

    private function buildCompleteSolution(): Solution
    {
        $solution = new Solution();

        for ($row = 0; $row < self::ROWS; $row++ ) {
            for ($col = 0; $col < self::ROWS; $col++ ) {
                $solution->setValueForSquare($row, $col, 1);
            }
        }

        return $solution;
    }

    private function buildValidSolution(): Solution
    {
        return new Solution(
            [
                [ 1, 2, 3, 4 ],
                [ 3, 4, 1, 2 ],
                [ 2, 3, 4, 1 ],
                [ 4, 1, 2, 3 ],
            ]
        );
    }

    private function buildInvalidSolution(): Solution
    {
        return new Solution(
            [
                [ 1, 4, 3, 4 ],
                [ 3, 2, 1, 2 ],
                [ 2, 3, 4, 1 ],
                [ 4, 1, 2, 3 ],
            ]
        );
    }
}
