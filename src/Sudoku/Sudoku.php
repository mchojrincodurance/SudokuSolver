<?php

namespace Sudoku;

use Sudoku\Exception\{NotSquareMatrixException, TooSmallMatrixException};

class Sudoku
{
    private array $matrix;

    /**
     * @param array $matrix
     * @throws NotSquareMatrixException
     */
    public function __construct(array $matrix)
    {
        if (!$this->isSquareMatrix($matrix)) {

            throw new NotSquareMatrixException();
        }

        if (!$this->isBiggerThan4x4($matrix)) {

            throw new TooSmallMatrixException();

        }
        $this->matrix = $matrix;
    }

    private function isSquareMatrix(array $matrix): bool
    {
        $rows = count($matrix);

        foreach ($matrix as $row) {
            if ($rows !== count($row)) {

                return false;
            }
        }

        return true;
    }

    private function isBiggerThan4x4(array $matrix): bool
    {
        if (count($matrix) < 4) {

            return false;
        }

        if (count($matrix[0]) < 4) {

            return false;
        }
        return true;
    }
}