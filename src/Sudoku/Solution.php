<?php

namespace Sudoku;

class Solution
{
    private $matrix;

    public function __construct()
    {
        $this->matrix = [];
    }

    /**
     * @param Sudoku $sudoku
     * @return bool
     */
    public function isSolutionFor(Sudoku $sudoku): bool
    {
        if (!$this->matches($sudoku)) {

            return false;
        }

        if (!$this->isValid()) {

            return false;
        }

        return true;
    }

    public function setValueForSquare(int $row, int $col, int $value): self
    {
        $this->matrix[$row][$col] = $value;

        return $this;
    }

    public function isComplete(): bool
    {
        foreach ($this->matrix as $row) {
            foreach ($row as $square) {
                if (0 === $square) {

                    return false;
                }
            }
        }

        return true;
    }

    public function matches(Sudoku $sudoku): bool
    {
        if ($this->dimensionMatches($sudoku)) {

            return false;
        }

        for ($i = 0; $i < $this->getRowCount(); $i++) {
            for ($j = 0; $j < $this->getRowCount(); $j++) {
                if (!$sudoku->isEmptySquare($i, $j) && !$this->valueMatches($sudoku, $i, $j)) {

                    return false;
                }
            }
        }

        return true;
    }

    /**
     * @param Sudoku $sudoku
     * @return bool
     */
    private function dimensionMatches(Sudoku $sudoku): bool
    {
        return $sudoku->getRowCount() !== $this->getRowCount();
    }

    public function getRowCount(): int
    {
        return count($this->matrix);
    }

    /**
     * @param Sudoku $sudoku
     * @param int $i
     * @param int $j
     * @return bool
     */
    private function valueMatches(Sudoku $sudoku, int $i, int $j): bool
    {
        return $this->matrix[$i][$j] === $sudoku->getValueForSquare($i, $j);
    }

    public function isValid(): bool
    {
        if (!$this->isComplete()) {

            return false;
        }

        for ($i = 0; $i < $this->getRowCount(); $i++) {
            for ($j = 0; $j < $this->getRowCount(); $j++) {
                if ($this->isValueRepeatedByRow($i, $this->matrix[$i][$j])) {

                    return false;
                }

                if ($this->isValueRepeatedByColumn($j, $this->matrix[$i][$j])) {

                    return false;
                }

                if ($this->isValueRepeatedByQuadrant($i, $j, $this->matrix[$i][$j])) {

                    return false;
                }
            }
        }

        return true;
    }

    private function isValueRepeatedByRow(int $row, int $value): bool
    {
        return count(array_filter($this->matrix[$row], fn($element) => $value == $element)) > 1;
    }

    private function isValueRepeatedByColumn(int $col, int $value): bool
    {
        return count(array_filter(array_column($this->matrix, $col), fn($element) => $value == $element)) > 1;
    }

    private function isValueRepeatedByQuadrant(int $row, int $column, int $value): bool
    {
        $quadrantSize = sqrt($this->getRowCount());
        $quadrantStartRow = floor($row / $quadrantSize) * $quadrantSize;
        $quadrantStartCol = floor($column / $quadrantSize) * $quadrantSize;

        $count = 0;
        for( $i = 0; $i < $quadrantSize; $i++ ) {
            for ($j = 0; $j < $quadrantSize; $j++ ) {
                $count += $this->matrix[$quadrantStartRow + $i][$quadrantStartCol + $j] === $value;
            }
        }

        return $count > 1;
    }
}