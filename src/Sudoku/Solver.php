<?php

declare(strict_types=1);

namespace Sudoku;

class Solver
{
    private Sudoku $solution;

    /**
     * @return void
     * @throws Exception\SquareAlreadyFilledException
     */
    private function fillASquare(): void
    {
        $emptySquares = $this->getEmptySquares();

        foreach ($emptySquares as $emptySquare) {
            if (count($possibleValues = $this->getPossibleValuesForSquare(...$emptySquare)) === 1) {
                $this->solution->setValueForSquare($emptySquare['row'], $emptySquare['col'], current($possibleValues));

                return;
            }
        }

        $firstEmptySquare = current($emptySquares);
        $this->solution->setValueForSquare(
            $firstEmptySquare['row'],
            $firstEmptySquare['col'],
            current($this->getPossibleValuesForSquare(...$firstEmptySquare))
        );
    }

    /**
     * @param array $quadrant
     * @return array
     */
    private function getValuesInQuadrant(array $quadrant): array
    {
        $values = [];

        for ($i = $quadrant['upperLeft']['row']; $i <= $quadrant['bottomRight']['row']; $i++) {
            for ($j = $quadrant['upperLeft']['col']; $j <= $quadrant['bottomRight']['col']; $j++) {
                if (!$this->solution->isEmptySquare($i, $j)) {
                    $values[] = $this->solution->getValueForSquare($i, $j);
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
     * @param int $row
     * @param int $col
     * @return array
     */
    private function getForbiddenValuesByQuadrant(int $row, int $col): array
    {
        return $this->getValuesInQuadrant($this->solution->getQuadrantForSquare($row, $col));
    }

    /**
     * @param int $col
     * @return array
     */
    private function getForbiddenValuesByColumn(int $col): array
    {
        return array_filter($this->getValuesInColumn($col));
    }

    /**
     * @param int $row
     * @return array
     */
    private function getForbiddenValuesByRow(int $row): array
    {
        return array_filter($this->getValuesInRow($row));
    }

    /**
     * @param Sudoku $sudoku
     * @return array
     */
    private function getEmptySquares(): array
    {
        $emptySquares = [];

        for ($row = 0; $row < $this->solution->getRowCount(); $row++) {
            for ($col = 0; $col < $this->solution->getRowCount(); $col++) {
                if ($this->solution->isEmptySquare($row, $col)) {
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
     * @param int $row
     * @param int $col
     * @return array
     */
    private function getPossibleValuesForSquare(int $row, int $col): array
    {
        $forbiddenValues = $this->getForbiddenValuesByRow($row);
        $forbiddenValues = array_merge($forbiddenValues, $this->getForbiddenValuesByColumn($col));
        $forbiddenValues = array_merge($forbiddenValues, $this->getForbiddenValuesByQuadrant($row, $col));

        return $this->removeForbiddenValues(array_unique($forbiddenValues), $this->buildPossibleValues());
    }

    /**
     * @param int $row
     * @param int $col
     * @return bool
     */
    private function isSquareFillable(int $row, int $col): bool
    {
        return !empty($this->getPossibleValuesForSquare($row, $col));
    }

    /**
     * @return bool
     */
    public function canSolutionBeBuilt(): bool
    {
        return $this->areEmptySquaresFillable();
    }

    /**
     * @param Sudoku $sudoku
     * @return Sudoku|null
     */
    public function getSolutionFor(Sudoku $sudoku): ?Sudoku
    {
        $this->solution = clone $sudoku;

        if (!$this->canSolutionBeBuilt()) {

            return null;
        }

        return $this->buildSolution();
    }

    /**
     * @return Sudoku
     */
    private function buildSolution(): Sudoku
    {
        while (!$this->solution->isSolved()) {
            $this->fillASquare();
        }

        return $this->solution;
    }

    /**
     * @param Sudoku $sudoku
     * @return bool
     */
    private function areEmptySquaresFillable(): bool
    {
        foreach ($this->getEmptySquares() as $emptySquare) {
            if (!$this->isSquareFillable(...$emptySquare)) {

                return false;
            }
        }

        return true;
    }

    /**
     * @return array
     */
    public function buildPossibleValues(): array
    {
        return range(1, $this->solution->getRowCount());
    }

    /**
     * @param int $row
     * @return array
     */
    private function getValuesInRow(int $row): array
    {
        $values = [];

        for($col = 0; $col < $this->solution->getRowCount(); $col++) {
            $values[] = $this->solution->getValueForSquare($row, $col);
        }

        return $values;
    }

    /**
     * @param int $col
     * @return array
     */
    private function getValuesInColumn(int $col): array
    {
        $values = [];

        for($row = 0; $row < $this->solution->getRowCount(); $row++) {
            $values[] = $this->solution->getValueForSquare($row, $col);
        }

        return $values;
    }
}