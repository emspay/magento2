<?php

namespace EMS\Pay\Model;

/**
 * Class CurrencyFormat
 * @package EMS\Pay\Model
 */
class CurrencyFormat
{
    /**
     * @var array
     */
    protected $mapFieldArray = [
        'chargetotal'
    ];

    /**
     * Check currency format - change comma to dot
     *
     * @param $resultArray
     * @return mixed
     */
    public function convertCurrencyStringToFloat($resultArray)
    {
        if ($resultArray) {
            foreach ($resultArray as $key => &$field) {
                if (in_array($key, $this->mapFieldArray)) {
                    $field = (float)str_replace(",", ".", $field);
                }
            }
        }
        return $resultArray;
    }
}
