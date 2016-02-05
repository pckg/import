<?php namespace Pckg\Import\Strategy;

trait WithoutMapperAndTransformator
{
    /**
     * @param array $row
     * @return array
     */
    public function map(array $row)
    {
        return [];
    }

    /**
     * @param array $row
     * @return array
     */
    public function transform(array $row)
    {
        return [];
    }

}