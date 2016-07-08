<?php namespace Pckg\Import;

use Exception;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Validator;
use Laraplus\Data\Cached;
use Maatwebsite\Excel\Collections\CellCollection;
use Maatwebsite\Excel\Readers\LaravelExcelReader;

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
     * Logger
     */
    protected $log;

    protected $autoprepare = true;

    public function validate(LaravelExcelReader $reader)
    {
        if (!$this->rules()) {
            return;
        }

        $count = 0;
        $reader->each(
            function(CellCollection $row) use (&$count) {
                if ($count > 0 || !$this->hasHeader()) {
                    $mapped = [];
                    try {
                        $mapped = $this->map($row->all());
                    } catch (Exception $e) {
                        $this->log->log('Validator mapping failed');
                        throw $e;
                    }
                    $validator = Validator::make($mapped, $this->rules(), []);

                    if ($validator->fails()) {
                        throw new Exception(
                            'Invalid row #' . $count . ': ' . implode(', ', $validator->messages()->all())
                        );
                    }
                }
                $count++;
            }
        );
    }

    public function rules()
    {
        return [];
    }

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
     *
     * @return Model
     */
    public function import(array $row)
    {
        $data = [];
        $mapped = [];
        try {
            $mapped = $this->map($row);
        } catch (\Exception $e) {
            $this->log->log('Mapping failed');
            throw $e;
        }

        try {
            $transformed = $this->transform($mapped);
            $data = $transformed;
        } catch (\Exception $e) {
            $this->log->log('Transformation failed');
            throw $e;
        }

        $record = $this->getExistingRecord($data);

        if ($record) {
            $this->tryUpdate($record, $data);

        } else {
            $record = $this->tryInsert($data);

        }

        return $record;
    }

    public function tryUpdate($record, $data)
    {
        try {
            if ($this->update($record, $data)) {
                $this->log->updated();

            } else {
                $this->log->updateFailed();

            }
        } catch (\Exception $e) {
            $this->log->updateFailed();
            $this->log->exception($e);
            throw $e;

        }
    }

    public function tryInsert($data)
    {
        try {
            if ($record = $this->insert($data)) {
                $this->log->inserted();

            } else {
                $this->log->insertFailed();

            }
        } catch (\Exception $e) {
            $this->log->insertFailed();
            $this->log->exception($e);
            throw $e;

        }
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
     *
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
     *
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
     *
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
     *
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
     *
     * @return int
     */
    public function getPrimaryKey($value)
    {
        return $this->cache(
            $value,
            function() use ($value) {
                if (!($record = $this->getRecordByIdentifier($value))) {
                    if (!$this->autoprepare) {
                        throw new Exception('Related record not found (' . $value . ')');
                    }

                    $record = $this->getModel()->forceCreate($this->autoPrepare($value));
                }

                return $record->{$this->primary};
            }
        );
    }

    /**
     * @param $value
     *
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
     *
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

    public function setLogger(Log $log)
    {
        $this->log = $log;

        return $this;
    }

}