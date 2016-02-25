<?php namespace Pckg\Import;

use Exception;
use Pckg\Import\Strategy;
use Maatwebsite\Excel\Collections\CellCollection;
use Maatwebsite\Excel\Readers\LaravelExcelReader;

/**
 * Class Import
 * @package Cms\Import
 */
class Import
{
    protected $file;

    protected $strategy;

    public $log;

    public function __construct()
    {
        $this->log = new Log();
    }

    /**
     * @param LaravelExcelReader $reader
     * @return $this
     */
    public function setFile(LaravelExcelReader $reader)
    {
        $this->file = $reader;

        return $this;
    }

    /**
     * @return LaravelExcelReader
     */
    public function getFile()
    {
        return $this->file;
    }

    /**
     * @param Strategy|string $strategy
     * @return $this
     */
    public function setStrategy($strategy)
    {
        $this->strategy = is_string($strategy)
            ? new $strategy
            : $strategy;

        return $this;
    }

    /**
     * Loop throught all rows and import (create/update) them.
     *
     * @return $this
     */
    public function import()
    {
        $this->strategy->setLogger($this->log);

        $this->log->log('Validating rows');
        try {
            $this->strategy->validate($this->file);
        } catch (Exception $e) {
            $this->log->log('Invalid file/row, interrupting import');
            $this->log->exception($e);
            return $this;
        }

        $this->log->log('Executing before import script');
        $this->strategy->beforeImport();

        $count = 0;
        $this->log->log('Importing rows');
        $this->file->each(function (CellCollection $row) use (&$count) {
            if ($count > 0 || !$this->strategy->hasHeader()) {
                try {
                    $this->strategy->import($row->all());
                } catch (Exception $e) {
                    $this->log->log('Exception @ row #' . $count . ' (' . json_encode($row->all()) . ')');
                    $this->log->exception($e);
                }
            } else {
                $this->log->log('Skipping header');
            }
            $count++;
        });

        $this->log->log('Total: ' . ($this->strategy->hasHeader() ? $count - 1 : $count));

        $this->log->log('Executing after import script');
        $this->strategy->afterImport();

        return $this;
    }

    /**
     * Set file, strategy and run import.
     *
     * @param LaravelExcelReader $file
     * @param Strategy|string    $strategy
     * @return $this
     */
    public function prepareAndImport(LaravelExcelReader $file, $strategy)
    {
        try {
            $this->log->log('File: ' . $file->file)
                ->log('Strategy: ' . (is_object($strategy) ? get_class($strategy) : $strategy))
                ->start();

            $this->setFile($file)
                ->setStrategy($strategy)
                ->import();

            $this->log->stop();
        } catch (Exception $e) {
            $this->log->log($e, true);
        }

        return $this;
    }

}