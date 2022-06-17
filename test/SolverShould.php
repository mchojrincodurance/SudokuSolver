<?php

declare(strict_types=1);

namespace SudokuTest;

use Sudoku\Solver;
use PHPUnit\Framework\TestCase;
use Sudoku\Sudoku;
use Sudoku\SolvedSudoku;

class SolverShould extends TestCase
{
    private Solver $solver;

    public function setUp(): void
    {
        $this->solver = new Solver();
    }

    /**
     * @test
     */

    public function not_alter_original_sudoku_while_solving_it()
    {
        $matrix = $this->buildSolvableSudokuMatrix();
        $sudoku = new Sudoku($matrix);
        $this->solver->getSolutionFor($sudoku);

        $this->assertTrue($this->sudokuEqualsMatrix($sudoku, $matrix));
    }
    /**
     * @test
     */

    public function detect_whether_a_sudoku_is_solvable_or_not()
    {
        $solvableSudoku = new Sudoku($this->buildSolvableSudokuMatrix());
        $unsolvableSudoku = new Sudoku($this->buildUnsolvableSudokuMatrix());

        $this->assertTrue($this->solver->isSolvable($solvableSudoku));
        $this->assertFalse($this->solver->isSolvable($unsolvableSudoku));
    }
    /**
     * @test
     */

    public function return_null_if_not_solvable()
    {
        $unsolvableSudoku = new Sudoku($this->buildUnsolvableSudokuMatrix());
        $this->assertNull($this->solver->getSolutionFor($unsolvableSudoku));
    }

    /**
     * @return void
     * @test
     */
    public function return_correct_solution_if_solvable(): void
    {
        $sudoku = new Sudoku($this->buildSolvableSudokuMatrix());
        $solvedSudoku = $this->solver->getSolutionFor($sudoku);

        $this->assertTrue($solvedSudoku->isSolved());
    }

    /**
     * @return array
     */
    private function buildUnsolvableSudokuMatrix(): array
    {
        return
            [
                [1, 0, 3, 4],
                [3, 4, 1, 2],
                [0, 3, 2, 1],
                [4, 1, 0, 3],
            ];
    }

    /**
     * @return \int[][]
     */
    private function buildSolvableSudokuMatrix(): array
    {
        return
            [
                [0, 2, 0, 4],
                [3, 0, 1, 2],
                [2, 3, 0, 1],
                [4, 0, 2, 0],
            ];
    }

    private function sudokuEqualsMatrix(Sudoku $sudoku, array $matrix): bool
    {
        for ($row = 0; $row < $sudoku->getRowCount(); $row++ ) {
            for ($col = 0; $col < $sudoku->getRowCount(); $col++ ) {
                if ($sudoku->getValueForSquare($row, $col) !== $matrix[$row][$col]) {

                    return false;
                }
            }
        }

        return true;
    }
}
