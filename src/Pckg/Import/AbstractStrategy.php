<?php namespace Pckg\Import;

use Illuminate\Database\Eloquent\Model;

abstract class AbstractStrategy implements Strategy
{

    /**
     * Key returned as foreign key reference.
     */
    protected $primary = 'id';

    /**
     * Descriptive code used as import identifier.
     */
    protected $identifier = 'code';

    /**
     * Skip first row when set to true.
     */
    protected $header = false;

    protected $objectManager;

    /**
     * @return $this
     */
    public function beforeImport()
    {
        return $this;
    }

    /**
     * Maps and transforms imported array to associative array.
     * Selects record from database by $identifier and creates/updates it.
     *
     * @param array $row
     * @return Model
     */
    public function import(array $row)
    {
        $data = $this->transform($this->map($row));

        if ($record = $this->getExistingRecord($data)) {
            $this->update($record, $data);

        } else {
            $record = $this->insert($data);

        }

        return $record;
    }

    /**
     * @return $this
     */
    public function afterImport()
    {
        return $this;
    }

    /**
     * Update and return record.
     *
     * @param Model $record
     * @param array $data
     * @return Model
     */
    public function update($record, $data = [])
    {
        $this->getModel()->update($data, $this->identifier . ' = ?', [$data[$this->identifier]]);

        return $record;
    }

    /**
     * Insert new record.
     *
     * @param array $data
     * @return static
     */
    public function insert($data = [])
    {
        $model = $this->getModel();
        $model->insert($data);

        $this->afterInsert($model);

        return $model;
    }

    /**
     * @param $model
     * @return $this
     */
    public function afterInsert($model)
    {
        return $this;
    }

    /**
     * Return existing record by identifier.
     *
     * @param array $data
     * @return mixed
     */
    public function getExistingRecord(array $data)
    {
        return $this->getModel()->select()->where($this->identifier . ' = ?')->prepare([$data[$this->identifier]])->getRow();
    }

    /**
     * Return primary key of related record.
     * Creates one if it doesn't exist yet.
     *
     * @param $value
     * @return int
     */
    public function getPrimaryKey($value)
    {
        if (!($record = $this->getRecordByIdentifier($value))) {
            $record = $this->getModel()->insert($this->autoPrepare($value));
        }

        return $record[$this->primary];
    }

    /**
     * @param $value
     * @return Model|null
     */
    public function getRecordByIdentifier($value)
    {
        return $this->getModel()->select()->where($this->identifier . ' = ?')->prepare([$value])->getRow();
    }

    /**
     * Return values for auto-created records.
     *
     * @param $value
     * @return array
     */
    public function autoPrepare($value)
    {
        return [
            $this->identifier => $value,
        ];
    }

    /**
     * Import services uses it to determine if we should skip first row.
     *
     * @return bool
     */
    public function hasHeader()
    {
        return $this->header;
    }

    public function setObjectManager($objectManager)
    {
        $this->objectManager = $objectManager;

        return $this;
    }

}