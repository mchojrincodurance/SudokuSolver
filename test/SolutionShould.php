<?php

namespace SudokuTest;

use Sudoku\Solution;
use PHPUnit\Framework\TestCase;
use Sudoku\Sudoku;

class SolutionShould extends TestCase
{
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

    /**
     * @test
     */

    public function recognize_whether_it_is_valid()
    {
        $validSolution = (new Solution())
            ->setValueForSquare(0, 0, 1)
            ->setValueForSquare(0, 1, 2)
            ->setValueForSquare(0, 2, 3)
            ->setValueForSquare(0, 3, 4)

            ->setValueForSquare(1, 0, 3)
            ->setValueForSquare(1, 1, 4)
            ->setValueForSquare(1, 2, 1)
            ->setValueForSquare(1, 3, 2)

            ->setValueForSquare(2, 0, 2)
            ->setValueForSquare(2, 1, 3)
            ->setValueForSquare(2, 2, 4)
            ->setValueForSquare(2, 3, 1)

            ->setValueForSquare(3, 0, 4)
            ->setValueForSquare(3, 1, 1)
            ->setValueForSquare(3, 2, 2)
            ->setValueForSquare(3, 3, 3)
            ;

        $invalidSolution = (new Solution())
            ->setValueForSquare(0, 0, 1)
            ->setValueForSquare(0, 1, 1)
            ->setValueForSquare(0, 2, 3)
            ->setValueForSquare(0, 3, 4)

            ->setValueForSquare(1, 0, 3)
            ->setValueForSquare(1, 1, 4)
            ->setValueForSquare(1, 2, 1)
            ->setValueForSquare(1, 3, 2)

            ->setValueForSquare(2, 0, 2)
            ->setValueForSquare(2, 1, 3)
            ->setValueForSquare(2, 2, 4)
            ->setValueForSquare(2, 3, 1)

            ->setValueForSquare(3, 0, 4)
            ->setValueForSquare(3, 1, 1)
            ->setValueForSquare(3, 2, 2)
            ->setValueForSquare(3, 3, 3)
        ;

        $incompleteSolution = (new Solution())
            ->setValueForSquare(0, 0, 1)
            ->setValueForSquare(0, 1, 0)
            ->setValueForSquare(0, 2, 3)
            ->setValueForSquare(0, 3, 4)

            ->setValueForSquare(1, 0, 3)
            ->setValueForSquare(1, 1, 4)
            ->setValueForSquare(1, 2, 1)
            ->setValueForSquare(1, 3, 0)

            ->setValueForSquare(2, 0, 2)
            ->setValueForSquare(2, 1, 3)
            ->setValueForSquare(2, 2, 4)
            ->setValueForSquare(2, 3, 1)

            ->setValueForSquare(3, 0, 4)
            ->setValueForSquare(3, 1, 1)
            ->setValueForSquare(3, 2, 2)
            ->setValueForSquare(3, 3, 3)
        ;

        $this->assertTrue($validSolution->isValid());
        $this->assertFalse($invalidSolution->isValid());
        $this->assertFalse($incompleteSolution->isValid());
    }

    /**
     * @return void
     * @test
     */
    public function recognize_whether_it_matches_a_sudoku()
    {
        $sudoku = $this->buildSolvableSudoku();

        $matchingSolution = (new Solution())
            ->setValueForSquare(0, 0, 1)
            ->setValueForSquare(0, 1, 2)
            ->setValueForSquare(0, 2, 3)
            ->setValueForSquare(0, 3, 4)

            ->setValueForSquare(1, 0, 3)
            ->setValueForSquare(1, 1, 4)
            ->setValueForSquare(1, 2, 1)
            ->setValueForSquare(1, 3, 2)

            ->setValueForSquare(2, 0, 2)
            ->setValueForSquare(2, 1, 3)
            ->setValueForSquare(2, 2, 4)
            ->setValueForSquare(2, 3, 1)

            ->setValueForSquare(3, 0, 4)
            ->setValueForSquare(3, 1, 1)
            ->setValueForSquare(3, 2, 2)
            ->setValueForSquare(3, 3, 3)
        ;

        $unmatchingSolution = (new Solution())
            ->setValueForSquare(0, 0, 2)
            ->setValueForSquare(0, 1, 1)
            ->setValueForSquare(0, 2, 3)
            ->setValueForSquare(0, 3, 4)

            ->setValueForSquare(1, 0, 3)
            ->setValueForSquare(1, 1, 4)
            ->setValueForSquare(1, 2, 1)
            ->setValueForSquare(1, 3, 2)

            ->setValueForSquare(2, 0, 2)
            ->setValueForSquare(2, 1, 3)
            ->setValueForSquare(2, 2, 4)
            ->setValueForSquare(2, 3, 1)

            ->setValueForSquare(3, 0, 4)
            ->setValueForSquare(3, 1, 1)
            ->setValueForSquare(3, 2, 2)
            ->setValueForSquare(3, 3, 3)
        ;

        $this->assertTrue($matchingSolution->matches($sudoku));
        $this->assertFalse($unmatchingSolution->matches($sudoku));
    }
}
