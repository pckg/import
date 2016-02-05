<?php namespace Pckg\Import\Strategy;

trait DefaultWhenNonExistent
{
    /**
     * @param $value
     * @return mixed
     */
    public function getPrimaryKey($value)
    {
        return $this->cache($value, function () use ($value) {
            if (!($record = $this->getModel()->where($this->identifier, $value)->first())) {
                return $this->primaryWhenNonExistent;
            }

            return $record->{$this->primary};
        });
    }

}