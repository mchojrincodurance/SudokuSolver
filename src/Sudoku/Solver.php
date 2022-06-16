<?php

declare(strict_types=1);

namespace Sudoku;

use JetBrains\PhpStorm\ArrayShape;

class Solver
{
    private Sudoku $sudoku;

    private function isValueRepeatedByRow(int $row, int $value): bool
    {
        return count(array_filter($this->getRowValues($row), fn($element) => $value == $element)) > 1;
    }

    private function isValueRepeatedByQuadrant(int $row, int $column, int $value): bool
    {
        $quadrantSize = sqrt($this->sudoku->getRowCount());
        $quadrantStartRow = floor($row / $quadrantSize) * $quadrantSize;
        $quadrantStartCol = floor($column / $quadrantSize) * $quadrantSize;

        $count = 0;
        for ($i = 0; $i < $quadrantSize; $i++) {
            for ($j = 0; $j < $quadrantSize; $j++) {
                $count += $this->sudoku->getValueForSquare($quadrantStartRow + $i, $quadrantStartCol + $j) === $value;
            }
        }

        return $count > 1;
    }

    public function isValid(Sudoku $sudoku): bool
    {
        for ($row = 0; $row < $sudoku->getRowCount(); $row++) {
            for ($col = 0; $col < $sudoku->getRowCount(); $col++) {
                if (!$sudoku->isEmptySquare($row, $col)) {
                    if ($this->isValueRepeatedByRow($row, $sudoku->getValueForSquare($row, $col), $this->sudoku)) {

                        return false;
                    }

                    if ($this->isValueRepeatedByColumn($col, $sudoku->getValueForSquare($row, $col), $this->sudoku)) {

                        return false;
                    }

                    if ($this->isValueRepeatedByQuadrant($row, $col, $sudoku->getValueForSquare($row, $col), $this->sudoku)) {

                        return false;
                    }
                }
            }
        }

        return true;
    }

    private function fillASquare(Sudoku $sudoku): void
    {
        $emptySquares = $this->getEmptySquares($sudoku);

        foreach ($emptySquares as $emptySquare) {
            if (count($possibleValues = $this->getPossibleValuesForSquare($sudoku, ...$emptySquare)) === 1) {
                $sudoku->setValueForSquare($emptySquare['row'], $emptySquare['col'], current($possibleValues));

                return;
            }
        }

        $firstEmptySquare = current($emptySquares);
        $sudoku->setValueForSquare(
            $firstEmptySquare['row'],
            $firstEmptySquare['col'],
            current($this->getPossibleValuesForSquare($sudoku, ...$firstEmptySquare))
        );
    }

    /**
     * @param array $quadrant
     * @param Sudoku $this->sudoku
     * @return array
     */
    private function getValuesInQuadrant(Sudoku $sudoku, array $quadrant): array
    {
        $values = [];

        for ($i = $quadrant['upperLeft']['row']; $i <= $quadrant['bottomRight']['row']; $i++) {
            for ($j = $quadrant['upperLeft']['col']; $j <= $quadrant['bottomRight']['col']; $j++) {
                if (!$sudoku->isEmptySquare($i, $j)) {
                    $values[] = $sudoku->getValueForSquare($i, $j);
                }
            }
        }

        return $values;
    }

    private function isValueRepeatedByColumn(Sudoku $sudoku, int $col, int $value): bool
    {
        return count(array_filter($this->getValuesInColumn($sudoku, $col), fn($element) => $value == $element)) > 1;
    }

    /**
     * @param array $forbiddenValues
     * @param array $possibleValues
     * @return array
     */
    private function removeForbiddenValues(array $forbiddenValues, array $possibleValues): array
    {
        foreach ($forbiddenValues as $forbiddenValue) {
            if (($k = array_search($forbiddenValue, $possibleValues)) !== false) {
                unset($possibleValues[$k]);
            }
        }

        return $possibleValues;
    }

    #[ArrayShape([
        'upperLeft' => "int[]",
        'bottomRight' => "int[]"
    ])]
    private function getQuadrantFor(Sudoku $sudoku, int $row, int $col): array
    {
        $quadrantSize = (int)sqrt($sudoku->getRowCount());

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

    private function getForbiddenValuesByQuadrant(Sudoku $sudoku, int $row, int $col): array
    {
        return $this->getValuesInQuadrant($sudoku, $sudoku->getQuadrantForSquare($row, $col));
    }

    private function getForbiddenValuesByColumn(Sudoku $sudoku, int $col): array
    {
        return array_filter($this->getValuesInColumn($sudoku, $col));
    }

    private function getForbiddenValuesByRow(Sudoku $sudoku, int $row): array
    {
        return array_filter($this->getValuesInRow($sudoku, $row));
    }

    /**
     * @param Sudoku $sudoku
     * @return array
     */
    private function getEmptySquares(Sudoku $sudoku): array
    {
        $emptySquares = [];

        for ($row = 0; $row < $sudoku->getRowCount(); $row++) {
            for ($col = 0; $col < $sudoku->getRowCount(); $col++) {
                if ($sudoku->isEmptySquare($row, $col)) {
                    $emptySquares[] = [
                        'row' => $row,
                        'col' => $col,
                    ];
                }
            }
        }

        return $emptySquares;
    }

    private function getPossibleValuesForSquare(Sudoku $sudoku, int $row, int $col): array
    {
        $forbiddenValues = $this->getForbiddenValuesByRow($sudoku, $row);
        $forbiddenValues = array_merge($forbiddenValues, $this->getForbiddenValuesByColumn($sudoku, $col));
        $forbiddenValues = array_merge($forbiddenValues, $this->getForbiddenValuesByQuadrant($sudoku, $row, $col));

        return $this->removeForbiddenValues(array_unique($forbiddenValues), $this->buildPossibleValues($sudoku));
    }

    /**
     * @param Sudoku $sudoku
     * @param int $row
     * @param int $col
     * @return bool
     */
    private function isSquareFillable(Sudoku $sudoku, int $row, int $col): bool
    {
        return !empty($this->getPossibleValuesForSquare($sudoku, $row, $col));
    }

    /**
     * @param Sudoku $sudoku
     * @return bool
     */
    public function isSolvable(Sudoku $sudoku): bool
    {
        return $this->emptySquaresAreFillable($sudoku);
    }

    public function getSolutionFor(Sudoku $sudoku): ?Sudoku
    {
        if (!$this->isSolvable($sudoku)) {

            return null;
        }

        return $this->buildSolutionFor($sudoku);
    }

    /**
     * @param Sudoku $sudoku
     * @return Sudoku
     */
    private function buildSolutionFor(Sudoku $sudoku): Sudoku
    {
        $this->sudoku = clone $sudoku;

        while (!$this->sudoku->isSolved()) {
            $this->fillASquare($this->sudoku);
        }

        return $this->sudoku;
    }

    private function emptySquaresAreFillable(Sudoku $sudoku): bool
    {
        foreach ($this->getEmptySquares($sudoku) as $emptySquare) {
            if (!$this->isSquareFillable($sudoku, ...$emptySquare)) {

                return false;
            }
        }

        return true;
    }

    /**
     * @param Sudoku $sudoku
     * @return array
     */
    public function buildPossibleValues(Sudoku $sudoku): array
    {
        return range(1, $sudoku->getRowCount());
    }

    public function isComplete(Sudoku $sudoku): bool
    {
        for ($row = 0; $row < $sudoku->getRowCount(); $row++) {
            for ($col = 0; $col < $sudoku->getRowCount(); $col++) {
                if ($sudoku->isEmptySquare($row, $col)) {

                    return false;
                }
            }
        }

        return true;
    }

    /**
     * @param Sudoku $sudoku
     * @param int $row
     * @return array
     */
    private function getValuesInRow(Sudoku $sudoku, int $row): array
    {
        $values = [];

        for($col = 0; $col < $sudoku->getRowCount(); $col++) {
            $values[] = $sudoku->getValueForSquare($row, $col);
        }

        return $values;
    }

    /**
     * @param Sudoku $sudoku
     * @param int $col
     * @return array
     */
    private function getValuesInColumn(Sudoku $sudoku, int $col): array
    {
        $values = [];

        for($row = 0; $row < $sudoku->getRowCount(); $row++) {
            $values[] = $sudoku->getValueForSquare($row, $col);
        }

        return $values;
    }

    /**
     * @param Sudoku $sudoku
     * @param int $row
     * @param int $value
     * @return bool
     */
    public function numberRepeatsInRow(Sudoku $sudoku, int $row, int $value): bool
    {
        return count(array_filter($this->getValuesInRow($sudoku, $row), fn($element) => $element === $value)) > 1;
    }
}