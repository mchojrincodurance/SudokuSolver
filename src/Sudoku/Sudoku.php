<?php

namespace Sudoku;

use Sudoku\Exception\{InvalidValueForSquareException,
    NotSquareMatrixException,
    SquareAlreadyFilledException,
    TooSmallMatrixException};
use JetBrains\PhpStorm\ArrayShape;

class Sudoku
{
    const EMPTY_SQUARE = 0;
    private array $matrix;

    /**
     * @param array $matrix
     * @throws NotSquareMatrixException
     * @throws TooSmallMatrixException
     */
    public function __construct(array $matrix)
    {
        if (!$this->isSquareMatrix($matrix)) {

            throw new NotSquareMatrixException();
        }

        if (!$this->isBiggerThan4x4($matrix)) {

            throw new TooSmallMatrixException();

        }

        foreach ($matrix as $i => $row) {
            $this->matrix[$i] = [];
            foreach ($row as $j => $value) {
                $this->matrix[$i][$j] = 0;
                if ($value) {
                    $this->setValueForSquare($i, $j, $value);
                }
            }
        }
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

    public function getRowCount(): int
    {
        return count($this->matrix);
    }

    public function getValueForSquare(int $row, int $col): int
    {
        if (!array_key_exists($row, $this->matrix) || !array_key_exists($col, $this->matrix[$row])) {

            return self::EMPTY_SQUARE;
        }

        return $this->matrix[$row][$col];
    }

    /**
     * @param int $row
     * @param int $col
     * @return bool
     */
    public function isEmptySquare(int $row, int $col): bool
    {
        return $this->matrix[$row][$col] === self::EMPTY_SQUARE;
    }

    /**
     * @param int $row
     * @param int $col
     * @param int $value
     * @return $this
     * @throws SquareAlreadyFilledException
     */
    public function setValueForSquare(int $row, int $col, int $value): self
    {
        if (!$this->isEmptySquare($row, $col)) {

            throw new SquareAlreadyFilledException();
        }
        
        if (!$this->isValueValidForSquare($row, $col, $value)) {
            
            throw new InvalidValueForSquareException();
        }
        $this->matrix[$row][$col] = $value;

        return $this;
    }

    /**
     * @param int $row
     * @param int $col
     * @param int $value
     * @return bool
     */
    public function isValueValidForSquare(int $row, int $col, int $value): bool
    {
        return !$this->isValuePresentInRow($row, $value) &&
            !$this->isValuePresentInCol($col, $value) &&
            !$this->isValuePresentInQuadrant($this->getQuadrantForSquare($row, $col), $value)
            ;
    }

    /**
     * @param int $row
     * @param int $value
     * @return void
     */
    public function isValuePresentInRow(int $row, int $value) : bool
    {
        return count(array_filter($this->matrix[$row], fn($element) => $value === $element)) > 0;
    }

    /**
     * @param int $col
     * @param int $value
     * @return bool
     */
    public function isValuePresentInCol(int $col, int $value): bool
    {
        return count(array_filter(array_column($this->matrix, $col), fn($element) => $value === $element)) > 0;
    }

    public function isValuePresentInQuadrant(array $quadrant, int $value) : bool
    {
        for($row = $quadrant['upperLeft']['row']; $row <= $quadrant['bottomRight']['row']; $row++ ) {
            for ($col = $quadrant['upperLeft']['col']; $col <= $quadrant['bottomRight']['col']; $col++ ) {
                if ($this->getValueForSquare($row, $col) === $value) {

                    return true;
                }
            }
        }

        return false;
    }

    #[ArrayShape(['upperLeft' => "float[]|int[]", 'bottomRight' => "float[]|int[]"])] public function getQuadrantForSquare(int $row, int $col): array
    {
        $quadrantSize = (int)sqrt($this->getRowCount());

        return [
            'upperLeft' => [
                'row' => (int)floor($row / $quadrantSize) * $quadrantSize,
                'col' => (int)floor($col / $quadrantSize) * $quadrantSize,
            ],
            'bottomRight' => [
                'row' => (int)floor($row / $quadrantSize) * $quadrantSize + $quadrantSize - 1,
                'col' => (int)floor($col / $quadrantSize) * $quadrantSize + $quadrantSize - 1,
            ]
        ];
    }
}