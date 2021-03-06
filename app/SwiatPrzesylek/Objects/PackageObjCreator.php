<?php


namespace App\SwiatPrzesylek\Objects;


use Exception;

/**
 * Class PackageObjCreator
 * @package App\SwiatPrzesylek\Objects
 */
class PackageObjCreator extends AbstractObjCreator
{
    /**
     * @var string
     */
    protected $weight;

    /**
     * @var string
     */
    protected $size_l;

    /**
     * @var string
     */
    protected $size_w;

    /**
     * @var string
     */
    protected $size_d;

    /**
     * @var string
     */
    protected $value;

    /**
     * @var string
     */
    protected $value_currency;

    /**
     * @var string
     */
    protected $content;

    /**
     * @var string
     */
    protected $ref;

    /**
     * @var string
     */
    protected $note1;

    /**
     * @var string
     */
    protected $note2;

    /**
     * @var string
     */
    protected $is_documents;

    /**
     * AddressObjCreator constructor.
     * @param array $dimensionsArray
     * @throws Exception
     */
    public function __construct(array $dimensionsArray)
    {
        foreach (get_object_vars($this) as $name => $value) {
            if (isset($dimensionsArray[$name])) {
                $this->$name = $dimensionsArray[$name];
            }
        }
    }
}
