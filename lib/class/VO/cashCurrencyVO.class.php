<?php

/**
 * Class cashCurrencyVO
 */
class cashCurrencyVO implements JsonSerializable
{
    use cashCreateFromArrayTrait;

    /**
     * @var string
     */
    private $code;

    /**
     * @var string
     */
    private $sign;

    /**
     * @var string
     */
    private $iso4217;

    /**
     * @var string
     */
    private $sign_html;

    /**
     * @var string
     */
    private $title;

    /**
     * @var string
     */
    private $name;

    /**
     * @var string
     */
    private $frac_name;

    /**
     * @return string
     */
    public function getCode()
    {
        return $this->code;
    }

    /**
     * @return string
     */
    public function getSign()
    {
        return $this->sign;
    }

    /**
     * @return string
     */
    public function getIso4217()
    {
        return $this->iso4217;
    }

    /**
     * @return string
     */
    public function getSignHtml()
    {
        return $this->sign_html;
    }

    /**
     * @return string
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @var cashCurrencyVO[]
     */
    private static $cache = [];

    /**
     * @return string
     */
    public function getFracName()
    {
        return $this->frac_name;
    }

    /**
     * cashCurrencyVO constructor.
     *
     * @param array $waCurrencyInfo
     */
    public function __construct(array $waCurrencyInfo)
    {
        $this->initializeWithArray($waCurrencyInfo);
    }

    /**
     * @param string $code
     *
     * @return cashCurrencyVO
     */
    public static function fromWaCurrency($code)
    {
        if (isset(self::$cache[$code])) {
            return self::$cache[$code];
        }

        $info = waCurrency::getInfo($code);
        if ($code === 'RUB') {
            $info['sign'] = '₽';
        }
        $currency = new static($info);
        self::$cache[$code] = $currency;

        return $currency;
    }

    /**
     * @return array
     */
    #[ReturnTypeWillChange]
    public function jsonSerialize()
    {
        return [
            'code' => $this->code,
            'sign' => $this->sign,
        ];
    }
}
