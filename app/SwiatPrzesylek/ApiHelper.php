<?php


namespace App\SwiatPrzesylek;


/**
 * Class ApiHelper
 * @package App\SwiatPrzesylek
 */
class ApiHelper
{
    /*
     * This constant is added to translate data in country column from address.csv file.
     * Data in country column is formatted as full english uppercase country name.
     * Świat Przesyłek API requires this parameter in format ISO 3166-1 alfa-2.
     * This constant should be extended to work with other countries.
     */
    public static $countryIso31661Alfa2Map = [
        'GERMANY' => 'DE',
        'POLAND' => 'PL',
        'PORTUGAL' => 'PT',
        'UNITED STATES OF AMERICA' => 'US',
    ];

    /**
     * @var string[]
     */
    public static $statusIdNameMap = [
        1 => 'NEW ORDER',
        2 => 'SHIPMENTS INJECTED',
        10 => 'RECEIVED IN HUB',
        20 => 'IN TRANSPORTATION',
        30 => 'IN DELIVERY',
        40 => 'DELIVERED',
        50 => 'PROBLEM',
        60 => 'RETURN',
        90 => 'CANCELLED',
    ];

    /**
     * "For time zone use country's capital time zone. There are 3 possible receiver countries: DE, PT, US.
     * But nice if script could work for any country."
     *
     * The easiest and fastest solution is to define default time zone for countries manually in this map.
     * The more complex solution would be to use some external API (e.g. Google) to get time zone basing on country
     * and city name or zip-code.
     *
     * @var string[]
     */
    public static $countryTimeZoneMap = [
        'DE' => 'Europe/Berlin',
        'PL' => 'Europe/Warsaw',
        'PT' => 'Europe/Lisbon',
        'US' => 'America/New_York',
    ];

    /**
     * @var string[]
     */
    public static $dayTime = [
        'start' => '08:00:00',
        'end' => '20:00:00',
    ];
}
