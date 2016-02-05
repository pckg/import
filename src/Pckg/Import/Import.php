<?php namespace Pckg\Import;

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
        $this->strategy->beforeImport();

        $count = 0;
        $this->file->each(function (CellCollection $row) use (&$count) {
            if ($count > 0 || !$this->strategy->hasHeader()) {
                $this->strategy->import($row->all());
            }
            $count++;
        });

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
        return $this->setFile($file)
            ->setStrategy($strategy)
            ->import();
    }

}