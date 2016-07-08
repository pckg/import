<?php namespace Pckg\Import\Strategy;

trait MtmImport
{

    /**
     * This shouldn't be called at all.
     *
     * @return Model
     */
    public function getModel()
    {
        return null;
    }

    /**
     * Empty all MTM records.
     *
     * @return $this
     */
    public function beforeImport()
    {
        $this->getRelation()->detach();

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
        $data = $this->transform($this->map($row));

        return $this->insert($data);
    }

    /**
     * Insert new record.
     * First parameter passed in array is foreign key of relation.
     * All other parameters are pivot parameters.
     *
     * @param array $data
     *
     * @return static
     */
    public function insert($data = [])
    {
        return $this->getRelation()->attach(array_shift($data), $data);
    }

}