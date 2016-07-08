<?php namespace Pckg\Import\Strategy;

use Illuminate\Support\Facades\DB;

trait Helper
{

    /**
     * @param      $value
     * @param int  $true
     * @param null $false
     *
     * @return int|null
     */
    public function toBool($value, $true = 1, $false = null)
    {
        $value = strtolower($value);

        return $value == '1' || $value == 'yes' || $value == 'oui'
            ? $true
            : $false;
    }

    /**
     * @param $url
     *
     * @return mixed
     */
    public function savePicture($url)
    {
        return $url;
    }

    /**
     * @param $x
     * @param $y
     *
     * @return mixed
     */
    public function toPoint($x, $y)
    {
        $x = str_replace([','], ['.'], $x);
        $y = str_replace([','], ['.'], $y);

        return DB::raw('POINT(' . number_format((float)$x, 8) . ',' . number_format((float)$y, 8) . ')');
    }

}