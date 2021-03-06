<?php


namespace App\SwiatPrzesylek\Objects;

use App\SwiatPrzesylek\ApiHelper;
use Exception;

/**
 * Class AddressObjCreator
 * @package App\SwiatPrzesylek\Objects
 */
class AddressObjCreator extends AbstractObjCreator
{
    /**
     * @var string
     */
    protected $name;

    /**
     * @var string
     */
    protected $company;

    /**
     * @var string
     */
    protected $address_line_1;

    /**
     * @var string
     */
    protected $address_line_2;

    /**
     * @var string
     */
    protected $city;

    /**
     * @var string
     */
    protected $zip_code;

    /**
     * @var string
     */
    protected $country;

    /**
     * @var string
     */
    protected $tel;

    /**
     * @var string
     */
    protected $email;

    /**
     * AddressObjCreator constructor.
     * @param string $type
     * @param array $addressArray
     * @throws Exception
     */
    public function __construct(string $type, array $addressArray)
    {
        switch ($type) {
            case 'sender':
                $key_prefix = 'sender_';
                break;
            case 'receiver':
                $key_prefix = 'receiver_';
                break;
            default:
                throw new Exception('Allowed values for argument $type are: sender, receiver');
        }

        foreach (get_object_vars($this) as $name => $value) {
            $key = $key_prefix . $name;
            if (isset($addressArray[$key])) {
                $this->$name = $addressArray[$key];
            }
        }

        if (isset($addressArray[$key_prefix . 'address_line'])) {
            $this->address_line_1 = $addressArray[$key_prefix . 'address_line'];
        }

        if (key_exists($this->country, ApiHelper::$countryIso31661Alfa2Map)) {
            $this->country = ApiHelper::$countryIso31661Alfa2Map[$this->country];
        }
    }
}
