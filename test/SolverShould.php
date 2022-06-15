<?php

declare(strict_types=1);

namespace SudokuTest;

use Sudoku\Solver;
use PHPUnit\Framework\TestCase;
use Sudoku\Sudoku;

class SolverShould extends TestCase
{
    /**
     * @test
     */

    public function return_null_if_not_solvable()
    {
        $solver = new Solver();
        $unsolvableSudoku = $this->buildUnsolvableSudoku();
        $this->assertNull($solver->getSolution($unsolvableSudoku));
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
}
