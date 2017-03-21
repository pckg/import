<?php namespace Pckg\Import;

use Exception;
use Illuminate\Database\Eloquent\Model;
use Maatwebsite\Excel\Readers\LaravelExcelReader;

interface Strategy
{

    /**
     * Validate file.
     *
     * @return mixed
     * @throws Exception
     */
    public function validate(LaravelExcelReader $reader);

    /**
     * Definer rules per row for Laravel validator.
     *
     * @return array
     */
    public function rules();

    /**
     * Called before import.
     * May be used as cleanup method.
     *
     * @return $this
     */
    public function beforeImport();

    /**
     * Transform numeric-indexed array into associative array.
     *
     * @param array $row
     *
     * @return array
     */
    public function map(array $row);

    /**
     * Transform foreign values into foreign keys.
     *
     * @param array $row
     *
     * @return array
     */
    public function transform(array $row);

    /**
     * Update existent record or create new one.
     *
     * @param array $row
     *
     * @return Model
     */
    public function import(array $row);

    /**
     * Get possible existing row from database.
     *
     * @param array $data
     *
     * @return Model|null
     */
    public function getExistingRecord(array $data);

    /**
     * Return strategy Model.
     *
     * @return Model
     */
    public function getModel();

}