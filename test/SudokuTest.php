<?php

namespace SudokuTest;

use PHPUnit\Framework\TestCase;
use Sudoku\Exception\{NotSquareMatrixException, TooSmallMatrixException};
use Sudoku\Sudoku;

class SudokuTest extends TestCase
{
    /**
     * @test
     */

    public function throw_exception_if_matrix_is_not_square()
    {
        $this->expectException(NotSquareMatrixException::class);
        $sudoku = new Sudoku([
            [1, 2, 3],
            [4, 5, 6],
        ]);
    }

    /**
     * @return void
     * @test
     */
    public function throw_exception_if_matrix_is_smaller_than_4x4()
    {
        $this->expectException(TooSmallMatrixException::class);
        $sudoku = new Sudoku([
            [1, 2, 3],
            [4, 5, 6],
            [1, 2, 3],
        ]);
    }

    /**
     * @test
     */

    public function not_throw_exception_if_matrix_is_square_and_bigger_than_4x4()
    {
        $sudoku = new Sudoku([
            [1, 2, 3, 4],
            [4, 5, 6, 7],
            [4, 5, 8, 9],
            [4, 5, 8, 9],
        ]);

        $this->assertInstanceOf(Sudoku::class, $sudoku);
    }
}
