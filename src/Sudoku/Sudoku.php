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

    public function getRowCount(): int
    {
        return count($this->matrix);
    }

    public function getValueForSquare(int $row, int $col): int
    {
        return $this->matrix[$row][$col];
    }

    public function isEmptySquare(int $i, int $j): bool
    {
        return $this->matrix[$i][$j] === 0;
    }

    /**
     * @return bool
     */
    public function isSolvable(): bool
    {
        return !$this->hasRepeatedNumbersPerRow() &&
            !$this->hasRepeatedNumbersPerColumn() &&
            !$this->hasRepeatedNumbersPerQuadrant() &&
            $this->emptySquaresAreFillable();
    }

    private function hasRepeatedNumbersPerRow(): bool
    {
        for ($i = 0; $i < $this->getRowCount(); $i++) {
            if ($this->hasRepeatedNumbersInRow($i)) {

                return true;
            }
        }

        return false;
    }


    private function hasRepeatedNumbersPerColumn(): bool
    {
        for ($i = 0; $i < $this->getRowCount(); $i++) {
            if ($this->hasRepeatedNumbersInColumn($i)) {

                return true;
            }
        }

        return false;
    }

    private function hasRepeatedNumbersPerQuadrant(): bool
    {
        foreach ($this->buildQuadrants() as $quadrant) {
            if ($this->hasRepeatedNumbersInQuadrant($quadrant)) {

                return true;
            }
        }

        return false;
    }

    private function hasRepeatedNumbersInRow(int $row): bool
    {
        for ($j = 0; $j < $this->getRowCount(); $j++) {
            if ($this->isEmptySquare($row, $j)) {

                continue;
            }
            $value = $this->matrix[$row][$j];

            if (count(array_filter($this->matrix[$row], fn($element) => $element === $value)) > 1) {

                return true;
            }
        }

        return false;
    }

    private function hasRepeatedNumbersInColumn(int $column): bool
    {
        for ($j = 0; $j < $this->getRowCount(); $j++) {
            if ($this->isEmptySquare($j, $column)) {

                continue;
            }
            $value = $this->matrix[$j][$column];

            if (count(array_filter(array_column($this->matrix, $column), fn($element) => $element === $value)) > 1) {

                return true;
            }
        }

        return false;
    }

    private function buildQuadrants(): array
    {
        $quadrantSize = sqrt($this->getRowCount());
        $quadrantQuantity = $this->getRowCount();

        $quadrants = [];
        for ($i = 0; $i < $quadrantQuantity / $quadrantSize; $i++) {
            for ($j = 0; $j < $quadrantQuantity / $quadrantSize; $j++) {
                $quadrants[] = [
                    'upperLeft' => [ $i * $quadrantSize, $j * $quadrantSize],
                    'bottomRight' => [ ($i + 1) * $quadrantSize - 1, ($j + 1) * $quadrantSize - 1 ],
                ];
            }
        }

        return $quadrants;
    }

    private function hasRepeatedNumbersInQuadrant(array $quadrant): bool
    {
        for($i = $quadrant['upperLeft'][0]; $i <= $quadrant['bottomRight'][0]; $i++ ) {
            for($j = $quadrant['upperLeft'][1]; $j <= $quadrant['bottomRight'][1]; $j++) {
                $value = $this->getValueForSquare($i, $j);

                if (!$this->isEmptySquare($i, $j) && $this->howManyOfValueAreInQuadrant($quadrant, $value) > 1) {

                    return true;
                }
            }
        }

        return false;
    }

    private function howManyOfValueAreInQuadrant(array $quadrant, int $value): int
    {
        $count = 0;

        for($i = $quadrant['upperLeft'][0]; $i <= $quadrant['bottomRight'][0]; $i++ ) {
            for($j = $quadrant['upperLeft'][1]; $j <= $quadrant['bottomRight'][1]; $j++) {
                $count += $this->getValueForSquare($i, $j) === $value;

            }
        }

        return $count;
    }

    private function emptySquaresAreFillable() : bool
    {
        foreach( $this->getEmptySquares() as $emptySquare ) {
            if (!$this->isSquareFillable(...$emptySquare)) {

                return false;
            }
        }

        return true;
    }

    private function isSquareFillable(int $row, int $col): bool
    {
        return !empty($this->getPossibleValuesFor($row, $col));
    }

    private function getPossibleValuesFor(int $row, int $col) : array
    {
        $possibleValues = range( 1, $this->getRowCount() );

        $forbiddenValues = $this->getForbiddenValuesByRow($row);
        $forbiddenValues = array_merge($forbiddenValues, $this->getForbiddenValuesByColumn($col));
        $forbiddenValues = array_merge($forbiddenValues, $this->getForbiddenValuesByQuadrant($row, $col));

        $forbiddenValues = array_unique($forbiddenValues);

        foreach($forbiddenValues as $forbiddenValue) {
            if (($k = array_search($forbiddenValue, $possibleValues)) !== false) {
                unset($possibleValues[$k]);
            }
        }

        return $possibleValues;
    }

    private function getEmptySquares() : array
    {
        $emptySquares = [];

        for( $i = 0; $i < $this->getRowCount(); $i++ ) {
            for($j = 0; $j < $this->getRowCount(); $j++ ) {
                if ($this->isEmptySquare($i, $j)) {
                    $emptySquares[] = [$i, $j];
                }
            }
        }

        return $emptySquares;
    }

    private function getForbiddenValuesByRow(int $row) : array
    {
        return array_filter(array_values($this->matrix[$row]));
    }

    private function getForbiddenValuesByColumn(int $col) : array
    {
        return array_filter(array_values(array_column($this->matrix, $col)));
    }

    private function getForbiddenValuesByQuadrant(int $row, int $col) : array
    {
        $values = [];

        $quadrant = $this->getQuadrantFor($row, $col);

        for ($i = $quadrant['upperLeft']['row']; $i <= $quadrant['bottomRight']['row']; $i++) {
            for ($j = $quadrant['upperLeft']['col']; $j <= $quadrant['bottomRight']['col']; $j++) {
                if (!$this->isEmptySquare($i, $j)) {
                    $values[] = $this->getValueForSquare($i, $j);
                }
            }
        }

        return $values;
    }

    private function getQuadrantFor(int $row, int $col): array
    {
        $quadrantSize = sqrt($this->getRowCount());

        return [
            'upperLeft' => [
                'row' => floor($row / $quadrantSize) * $quadrantSize,
                'col' => floor($col / $quadrantSize) * $quadrantSize,
            ],
            'bottomRight' => [
                'row' => floor($row / $quadrantSize)  * $quadrantSize + $quadrantSize - 1,
                'col' => floor($col / $quadrantSize)  * $quadrantSize + $quadrantSize - 1,
            ]
        ];
    }
}