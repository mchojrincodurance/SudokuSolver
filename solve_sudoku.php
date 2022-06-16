#!/usr/bin/env php
<?php
require __DIR__.'/vendor/autoload.php';

use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\SingleCommandApplication;

(new SingleCommandApplication())
    ->setName('SolveSudokuCommand')
    ->setVersion('1.0.0')
    ->addArgument('input-file', InputArgument::REQUIRED, 'The path to the file containing the Sudoku matrix definition')
    ->setCode(function (InputInterface $input, OutputInterface $output) {
        $io = new \Symfony\Component\Console\Style\SymfonyStyle($input, $output);

        $inputFileName = $input->getArgument('input-file');
        $output->writeln('Reading from '. $inputFileName);
        if (!is_readable($inputFileName)) {
            $io->error("Can't read from $inputFileName");

            return -1;
        }

        $contents = file($inputFileName);
        $table = $io->createTable();

        $dataMatrix = [];
        foreach ($contents as $k => $line) {
            $line = trim($line);
            if (substr($line, -1) === ',') {
                $line = substr($line, 0, -1);
            }
            $dataMatrix[$k] = array_map("intval", str_getcsv($line));
            $table->addRow(str_getcsv(str_replace('0', '*', $line)));
        }
        try {
            $sudoku = new Sudoku\Sudoku($dataMatrix);
            $io->section('Initial status:');
            $table->render();

            $io->section("Solution:");
            $solver = new \Sudoku\Solver();
            if ($solution = $solver->getSolutionFor($sudoku)) {
                $solutionTable = $io->createTable();
                for ($row = 0; $row < $solution->getRowCount(); $row++ ) {
                    $rowContents = [];
                    for ($col = 0; $col < $solution->getRowCount(); $col++ ) {
                        $rowContents[] = $solution->getValueForSquare($row, $col);
                    }
                    $solutionTable->addRow($rowContents);
                }

                $solutionTable->render();
            } else {
                $io->info("This Sudoku is not solvable... try another one?");
            }
        } catch (\Sudoku\Exception\InvalidValueForSquareException $exception) {
            $io->error("Input is wrong: ".$exception->getMessage());
        } catch (\Sudoku\Exception\TooSmallMatrixException $exception) {
            $io->error("Input is wrong: ".$exception->getMessage());
        } catch (\Sudoku\Exception\NotSquareMatrixException $exception) {
            $io->error("Input is wrong: ".$exception->getMessage());
        }
    })
    ->run();