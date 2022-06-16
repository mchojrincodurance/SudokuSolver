<?php

declare(strict_types=1);

namespace Sudoku;

class Solver
{
    /**
     * @param Sudoku $sudoku
     * @return void
     * @throws Exception\SquareAlreadyFilledException
     */
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
     * @param Sudoku $sudoku
     * @param array $quadrant
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

    /**
     * @param Sudoku $sudoku
     * @param int $col
     * @param int $value
     * @return bool
     */
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

    /**
     * @param Sudoku $sudoku
     * @param int $row
     * @param int $col
     * @return array
     */
    private function getForbiddenValuesByQuadrant(Sudoku $sudoku, int $row, int $col): array
    {
        return $this->getValuesInQuadrant($sudoku, $sudoku->getQuadrantForSquare($row, $col));
    }

    /**
     * @param Sudoku $sudoku
     * @param int $col
     * @return array
     */
    private function getForbiddenValuesByColumn(Sudoku $sudoku, int $col): array
    {
        return array_filter($this->getValuesInColumn($sudoku, $col));
    }

    /**
     * @param Sudoku $sudoku
     * @param int $row
     * @return array
     */
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

    /**
     * @param Sudoku $sudoku
     * @param int $row
     * @param int $col
     * @return array
     */
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

    /**
     * @param Sudoku $sudoku
     * @return Sudoku|null
     */
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
        $sudoku1 = clone $sudoku;

        while (!$sudoku1->isSolved()) {
            $this->fillASquare($sudoku1);
        }

        return $sudoku1;
    }

    /**
     * @param Sudoku $sudoku
     * @return bool
     */
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
}