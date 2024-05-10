<?php

class QwqerDeliveryProduct extends ObjectModel
{

    /**
     * @var int $id_qwqer_delivery_products
     */
    public $id_qwqer_delivery_products;

    /**
     * @var int $id_shop_default
     */
    public $id_shop_default;

    /**
     * @var int $id_product
     */
    public $id_product;

    /**
     * @var bool $is_available
     */
    public $is_available;

    /**
     * @var int $id_delivery_method
     */
    public $id_delivery_method;


    /**
     * @var string $date_add
     */
    public $date_add;

    /**
     * @var string $date_upd
     */
    public $date_upd;


    public static $definition = [
        'table' => 'qwqer_delivery_products',
        'primary' => 'id_qwqer_delivery_products',
        'multishop' => true,
        'fields' => [
            'date_add' => array('type' => self::TYPE_DATE, 'validate' => 'isDate'),
            'date_upd' => array('type' => self::TYPE_DATE, 'validate' => 'isDate'),
            'id_shop_default' => array('type' => self::TYPE_INT, 'validate' => 'isInt'),

            'id_product' => ['type' => self::TYPE_INT, 'shop' => true, 'validate' => 'isUnsignedInt', 'required' => true],
            'is_available' => ['type' => self::TYPE_INT, 'shop' => true, 'validate' => 'isUnsignedInt', 'required' => true],
            'id_delivery_method' => ['type' => self::TYPE_INT, 'shop' => true, 'validate' => 'isUnsignedInt'],
        ],
    ];

    /**
     * @param $id
     * @param $id_lang
     * @param $id_shop
     * @throws PrestaShopDatabaseException
     * @throws PrestaShopException
     */
    public function __construct($id = null, $id_lang = null, $id_shop = null)
    {
        Shop::addTableAssociation(self::$definition['table'], array('type' => 'shop'));
        parent::__construct($id, $id_lang, $id_shop);
    }

    /**
     * @param $auto_date
     * @param $null_values
     * @return bool|int|string
     * @throws PrestaShopDatabaseException
     * @throws PrestaShopException
     */
    public function add($auto_date = true, $null_values = false)
    {
        $context = Context::getContext();
        $this->id_shop_default = $this->id_shop_default ? $this->id_shop_default : $context->shop->id;

        return parent::add($auto_date, $null_values);
    }

    public static function getByProductId($productId)
    {
        $shop_id = \Context::getContext()->shop->id;

        $cache_id = 'QwqerDeliveryProduct::getByProductId_' . (int) $productId;
        if (!Cache::isStored($cache_id)) {
            $id = \Db::getInstance()->getValue(
                ' SELECT t.' . self::$definition['primary'] . ' FROM '
                . _DB_PREFIX_ . static::$definition['table'] . ' AS t'
                . ' LEFT JOIN ' . _DB_PREFIX_ . self::$definition['table'] . '_shop AS ts '
                . ' ON (ts.' . self::$definition['primary'] . ' = t.' . self::$definition['primary'] . ' '
                . ' AND ts.`id_shop` = ' . (int)$shop_id . ')'
                . ' WHERE ts.id_product = ' . (int)$productId
            );

            Cache::store($cache_id, $id);
        }

        $id = Cache::retrieve($cache_id);
        $deliveryProduct = new self($id, null, $shop_id);

        return \Validate::isLoadedObject($deliveryProduct) ? $deliveryProduct : false;
    }
}
