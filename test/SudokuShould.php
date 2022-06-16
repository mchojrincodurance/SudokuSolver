<?php

namespace SudokuTest;

use PHPUnit\Framework\TestCase;
use Sudoku\Exception\{InvalidValueForSquareException,
    NotSquareMatrixException,
    TooSmallMatrixException,
    SquareAlreadyFilledException
};
use Sudoku\Sudoku;

class SudokuShould extends TestCase
{
    const NON_EMPTY_SQUARE_ROW = 0;
    const NON_EMPTY_SQUARE_COL = 0;

    const EMPTY_SQUARE_ROW = 1;
    const EMPTY_SQUARE_COL = 1;

    const INVALID_VALUE_FOR_SQUARE = 5;

    /**
     * @return void
     * @test
     */
    public function disallow_initialization_with_non_square_matrix(): void
    {
        $this->expectException(NotSquareMatrixException::class);
        $sudoku = new Sudoku($this->buildNonSquareMatrix());
    }

    /**
     * @return void
     * @test
     */
    public function disallow_initialization_with_small_matrix(): void
    {
        $this->expectException(TooSmallMatrixException::class);
        $sudoku = new Sudoku($this->buildSmallMatrix());
    }

    /**
     * @return void
     * @test
     */
    public function disallow_writing_of_invalid_values_in_squares(): void
    {
        $this->expectException(InvalidValueForSquareException::class);
        $sudoku = new Sudoku($this->buildCorrectMatrix());
        $sudoku->setValueForSquare(self::EMPTY_SQUARE_ROW, self::EMPTY_SQUARE_COL, self::INVALID_VALUE_FOR_SQUARE);
    }

    /**
     * @test
     */

    public function allow_usage_of_correct_matrix()
    {
        $sudoku = new Sudoku($this->buildCorrectMatrix());

        $this->assertInstanceOf(Sudoku::class, $sudoku);
    }

    /**
     * @return void
     * @throws NotSquareMatrixException
     * @throws TooSmallMatrixException
     * @test
     */
    public function disallow_overwriting_of_squares(): void
    {
        $this->expectException(SquareAlreadyFilledException::class);
        $sudoku = new Sudoku($this->buildCorrectMatrix());
        $sudoku->setValueForSquare(self::NON_EMPTY_SQUARE_ROW, self::NON_EMPTY_SQUARE_COL, 1);
    }

    /**
     * @return void
     * @throws NotSquareMatrixException
     * @throws TooSmallMatrixException
     * @test
     */
    public function allow_writing_on_empty_squares(): void
    {
        $sudoku = new Sudoku($this->buildCorrectMatrix());
        $sudoku->setValueForSquare(self::EMPTY_SQUARE_ROW, self::EMPTY_SQUARE_COL, 1);
        $this->assertEquals(1, $sudoku->getValueForSquare(self::EMPTY_SQUARE_ROW, self::EMPTY_SQUARE_COL));
    }

    /**
     * @test
     */

    public function know_when_it_is_solved()
    {
        $solvedSudoku = new Sudoku($this->buildSolvedSudokuMatrix());
        $this->assertTrue($solvedSudoku->isSolved());
    }

    /**
     * @return array
     */
    private function buildCorrectMatrix(): array
    {
        return [
            [5, 0, 0, 0, 4, 6, 0, 0, 0],
            [4, 0, 0, 2, 3, 0, 0, 0, 9],
            [0, 0, 0, 0, 0, 9, 1, 0, 0],
            [0, 0, 4, 0, 0, 0, 0, 7, 5],
            [2, 0, 3, 4, 0, 0, 0, 9, 8],
            [9, 8, 0, 0, 0, 2, 0, 4, 0],
            [1, 4, 0, 3, 0, 8, 0, 0, 7],
            [0, 0, 0, 7, 0, 0, 0, 1, 2],
            [7, 0, 0, 1, 0, 0, 8, 0, 6],
        ];
    }

    /**
     * @return array
     */
    private function buildNonSquareMatrix(): array
    {
        return [
            [1, 2, 3],
            [4, 5, 6],
        ];
    }

    /**
     * @return \int[][]
     */
    private function buildSmallMatrix(): array
    {
        return [
            [1, 2, 3],
            [4, 5, 6],
            [1, 2, 3],
        ];
    }

    private function buildSolvedSudokuMatrix()
    {
        return [
            [ 1, 2, 3, 4],
            [ 3, 4, 2, 1],
            [ 2, 1, 4, 3],
            [ 4, 3, 1, 2],
        ];
    }
}
