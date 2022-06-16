<?php

namespace Sudoku;

use JetBrains\PhpStorm\ArrayShape;
use Sudoku\Exception\{NotSquareMatrixException, TooSmallMatrixException};

class Sudoku
{
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

    public function isEmptySquare(int $row, int $col): bool
    {
        return $this->matrix[$row][$col] === 0;
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
        for ($col = 0; $col < $this->getRowCount(); $col++) {
            if ($this->isEmptySquare($row, $col)) {

                continue;
            }
            $value = $this->matrix[$row][$col];

            if (count(array_filter($this->matrix[$row], fn($element) => $element === $value)) > 1) {

                return true;
            }
        }

        return false;
    }

    private function hasRepeatedNumbersInColumn(int $col): bool
    {
        for ($row = 0; $row < $this->getRowCount(); $row++) {
            if ($this->isEmptySquare($row, $col)) {

                continue;
            }
            $value = $this->matrix[$row][$col];

            if (count(array_filter(array_column($this->matrix, $col), fn($element) => $element === $value)) > 1) {

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
                $quadrants[] = $this->getQuadrantFor($i, $j);
            }
        }

        return $quadrants;
    }

    private function hasRepeatedNumbersInQuadrant(array $quadrant): bool
    {
        for ($i = $quadrant['upperLeft']['row']; $i <= $quadrant['bottomRight']['row']; $i++) {
            for ($j = $quadrant['upperLeft']['col']; $j <= $quadrant['bottomRight']['col']; $j++) {
                $value = $this->getValueForSquare($i, $j);

                if (!$this->isEmptySquare($i, $j) && $this->howManyOfValueAreInQuadrant($value, $quadrant) > 1) {

                    return true;
                }
            }
        }

        return false;
    }

    private function howManyOfValueAreInQuadrant(int $value, array $quadrant): int
    {
        $count = 0;

        for ($i = $quadrant['upperLeft']['row']; $i <= $quadrant['bottomRight']['row']; $i++) {
            for ($j = $quadrant['upperLeft']['col']; $j <= $quadrant['bottomRight']['col']; $j++) {
                $count += $this->getValueForSquare($i, $j) === $value;

            }
        }

        return $count;
    }

    private function emptySquaresAreFillable(): bool
    {
        foreach ($this->getEmptySquares() as $emptySquare) {
            if (!$this->isSquareFillable(...$emptySquare)) {

                return false;
            }
        }

        return true;
    }

    private function isSquareFillable(int $row, int $col): bool
    {
        return !empty($this->getPossibleValuesForSquare($row, $col));
    }

    private function getPossibleValuesForSquare(int $row, int $col): array
    {
        $forbiddenValues = $this->getForbiddenValuesByRow($row);
        $forbiddenValues = array_merge($forbiddenValues, $this->getForbiddenValuesByColumn($col));
        $forbiddenValues = array_merge($forbiddenValues, $this->getForbiddenValuesByQuadrant($row, $col));

        return $this->removeForbiddenValues(array_unique($forbiddenValues), $this->buildPossibleValues());
    }

    private function getEmptySquares(): array
    {
        $emptySquares = [];

        for ($i = 0; $i < $this->getRowCount(); $i++) {
            for ($j = 0; $j < $this->getRowCount(); $j++) {
                if ($this->isEmptySquare($i, $j)) {
                    $emptySquares[] = [$i, $j];
                }
            }
        }

        return $emptySquares;
    }

    private function getForbiddenValuesByRow(int $row): array
    {
        return array_filter(array_values($this->matrix[$row]));
    }

    private function getForbiddenValuesByColumn(int $col): array
    {
        return array_filter(array_values(array_column($this->matrix, $col)));
    }

    private function getForbiddenValuesByQuadrant(int $row, int $col): array
    {
        return $this->getValuesInQuadrant($this->getQuadrantFor($row, $col));
    }

    #[ArrayShape([
        'upperLeft' => "int[]",
        'bottomRight' => "int[]"
    ])]
    private function getQuadrantFor(int $row, int $col): array
    {
        $quadrantSize = sqrt($this->getRowCount());

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

    /**
     * @param array $quadrant
     * @return array
     */
    public function getValuesInQuadrant(array $quadrant): array
    {
        $values = [];

        for ($i = $quadrant['upperLeft']['row']; $i <= $quadrant['bottomRight']['row']; $i++) {
            for ($j = $quadrant['upperLeft']['col']; $j <= $quadrant['bottomRight']['col']; $j++) {
                if (!$this->isEmptySquare($i, $j)) {
                    $values[] = $this->getValueForSquare($i, $j);
                }
            }
        }

        return $values;
    }

    /**
     * @param array $forbiddenValues
     * @param array $possibleValues
     * @return array
     */
    public function removeForbiddenValues(array $forbiddenValues, array $possibleValues): array
    {
        foreach ($forbiddenValues as $forbiddenValue) {
            if (($k = array_search($forbiddenValue, $possibleValues)) !== false) {
                unset($possibleValues[$k]);
            }
        }

        return $possibleValues;
    }

    /**
     * @return array
     */
    public function buildPossibleValues(): array
    {
        return range(1, $this->getRowCount());
    }
}