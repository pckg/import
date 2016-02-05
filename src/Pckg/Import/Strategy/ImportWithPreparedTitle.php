<?php namespace Pckg\Import\Strategy;

trait ImportWithPreparedTitle
{
    /**
     * @param $value
     * @return array
     */
    public function autoPrepare($value)
    {
        return array_merge(parent::autoPrepare($value), [
            isset($this->title) ? $this->title : 'title' => $value,
        ]);
    }

}