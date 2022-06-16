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

    public function detect_whether_a_sudoku_is_solvable_or_not()
    {
        $solvableSudoku = $this->buildSolvableSudoku();
        $unsolvableSudoku = $this->buildUnsolvableSudoku();

        $this->assertTrue($this->solver->isSolvable($solvableSudoku));
        $this->assertFalse($this->solver->isSolvable($unsolvableSudoku));
    }
    /**
     * @test
     */

    public function return_null_if_not_solvable()
    {
        $unsolvableSudoku = $this->buildUnsolvableSudoku();
        $this->assertNull($this->solver->getSolutionFor($unsolvableSudoku));
    }

    /**
     * @return void
     * @test
     */
    public function return_correct_solution_if_solvable(): void
    {
        $sudoku = $this->buildSolvableSudoku();
        $solvedSudoku = $this->solver->getSolutionFor($sudoku);

        $this->assertTrue($solvedSudoku->isSolved());
    }

    /**
     * @return Sudoku
     */
    private function buildUnsolvableSudoku(): Sudoku
    {
        return new Sudoku(
            [
                [1, 0, 3, 4],
                [3, 4, 1, 2],
                [0, 3, 2, 1],
                [4, 1, 0, 3],
            ]);
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
}
