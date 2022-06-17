<?php

namespace Sudoku;

use Sudoku\Exception\{InvalidValueForSquareException,
    NotSquareMatrixException,
    SquareAlreadyFilledException,
    TooSmallMatrixException
};
use JetBrains\PhpStorm\ArrayShape;

class Sudoku
{
    const EMPTY_SQUARE = 0;
    private array $matrix;


    /**
     * @param array $matrix
     * @throws NotSquareMatrixException
     * @throws TooSmallMatrixException|SquareAlreadyFilledException
     */
    public function __construct(array $matrix)
    {
        if (!$this->isSquareMatrix($matrix)) {

            throw new NotSquareMatrixException("The matrix is not square");
        }

        if (!$this->isBiggerThan4x4($matrix)) {

            throw new TooSmallMatrixException("The matrix is only " . count($matrix) . "x" . count($matrix));

        }

        $this->initMatrix($matrix);
    }

    /**
     * @return bool
     */
    public function isSolved(): bool
    {
        return $this->getEmptySquareCount() === 0;
    }

    /**
     * @return int
     */
    public function getRowCount(): int
    {
        return count($this->matrix);
    }

    /**
     * @param int $row
     * @param int $col
     * @return int
     */
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
     * @throws SquareAlreadyFilledException|InvalidValueForSquareException
     */
    public function setValueForSquare(int $row, int $col, int $value): self
    {
        if (!$this->isEmptySquare($row, $col)) {

            throw new SquareAlreadyFilledException();
        }

        if (!$this->isValueValidForSquare($row, $col, $value)) {

            throw new InvalidValueForSquareException("[$row, $col] can't contain $value");
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
            !$this->isValuePresentInQuadrant($this->getQuadrantForSquare($row, $col), $value);
    }

    /**
     * @param int $row
     * @param int $value
     * @return bool
     */
    public function isValuePresentInRow(int $row, int $value): bool
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

    /**
     * @param array $quadrant
     * @param int $value
     * @return bool
     */
    public function isValuePresentInQuadrant(array $quadrant, int $value): bool
    {
        for ($row = $quadrant['upperLeft']['row']; $row <= $quadrant['bottomRight']['row']; $row++) {
            for ($col = $quadrant['upperLeft']['col']; $col <= $quadrant['bottomRight']['col']; $col++) {
                if ($this->getValueForSquare($row, $col) === $value) {

                    return true;
                }
            }
        }

        return false;
    }

    /**
     * @param array $matrix
     * @return void
     * @throws SquareAlreadyFilledException
     */
    public function initMatrix(array $matrix): void
    {
        foreach ($matrix as $i => $row) {
            $this->matrix[$i] = [];
            foreach ($row as $j => $value) {
                $this->matrix[$i][$j] = self::EMPTY_SQUARE;
                if ($value !== self::EMPTY_SQUARE) {
                    $this->setValueForSquare($i, $j, $value);
                }
            }
        }
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

        return true;
    }

    /**
     * @return int
     */
    private function getEmptySquareCount(): int
    {
        $emptySquareCount = 0;

        for ($row = 0; $row < $this->getRowCount(); $row++) {
            for ($col = 0; $col < $this->getRowCount(); $col++) {
                $emptySquareCount += $this->isEmptySquare($row, $col);
            }
        }

        return $emptySquareCount;
    }
}