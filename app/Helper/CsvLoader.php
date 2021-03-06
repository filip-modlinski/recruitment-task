<?php


namespace App\Helper;


/**
 * Class CsvLoader
 * @package App\Helper
 */
class CsvLoader
{
    /**
     * @param string $path
     * @return array
     */
    public static function getCsvAsArray(string $path): array
    {
        $csvArray = array_map('str_getcsv', file($path, FILE_SKIP_EMPTY_LINES));
        $headers = array_shift($csvArray);

        foreach ($csvArray as &$row) {
            $row = array_combine($headers, $row);
        }

        return $csvArray;
    }
}
