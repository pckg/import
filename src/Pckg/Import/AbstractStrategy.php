<?php namespace Pckg\Import;

use Illuminate\Database\Eloquent\Model;
use Laraplus\Data\Cached;

abstract class AbstractStrategy implements Strategy
{
    use Cached;

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
    public function update(Model $record, $data = [])
    {
        $record->forceFill($data)->save();

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
        $model->forceFill($data)->save();

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
        return $this->getModel()->newQueryWithoutScopes()->where($this->identifier, $data[$this->identifier])->first();
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
        return $this->cache($value, function () use ($value) {
            if (!($record = $this->getRecordByIdentifier($value))) {
                $record = $this->getModel()->newQueryWithoutScopes()->forceCreate($this->autoPrepare($value));
            }

            return $record->{$this->primary};
        });
    }

    /**
     * @param $value
     * @return Model|null
     */
    public function getRecordByIdentifier($value)
    {
        return $this->getModel()->newQueryWithoutScopes()->where($this->identifier, $value)->first();
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

}