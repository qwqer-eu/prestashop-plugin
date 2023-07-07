<?php

class QwqerDeliveryOrder
{
    /**
     * @var int
     */
//    public $client_id = null;
//
//    /**
//     * @var int|null
//     */
//    public $courier_id = null;
//
//    /**
//     * @var int
//     */
//    public $delivery_area_id = null;
//
//    /**
//     * @var int
//     */
//    public $trading_point_id = null;
//
//    /**
//     * @var string
//     */
//    public $status = null;

    /**
     * @var string
     */
    public $type = 'Regular';

    /**
     * @var string
     */
    public $real_type = 'ScheduledDelivery';

    /**
     * @var string
     */
    public $category = null;

    /**
     * Format: Y-m-d H
     *
     * @var string|null
     */
//    public $pickup_datetime = null;
//
//    /**
//     * @var bool
//     */
//    public $is_round_trip = null;
//
//    /**
//     * @var float
//     */
//    public $client_price = null;
//
//    /**
//     * @var float
//     */
//    public $courier_price = null;

    /**
     * @param int $client_id
     */
    public function setClientId($client_id)
    {
        $this->client_id = $client_id;
    }

    /**
     * @param int|null $courier_id
     */
    public function setCourierId($courier_id)
    {
        $this->courier_id = $courier_id;
    }

    /**
     * @param int $delivery_area_id
     */
    public function setDeliveryAreaId($delivery_area_id)
    {
        $this->delivery_area_id = $delivery_area_id;
    }

    /**
     * @param int $trading_point_id
     */
    public function setTradingPointId($trading_point_id)
    {
        $this->trading_point_id = $trading_point_id;
    }

    /**
     * @param string $status
     */
    public function setStatus($status)
    {
        $this->status = $status;
    }

    /**
     * @param string $type
     */
    public function setType($type)
    {
        $this->type = $type;
    }

    /**
     * @return string
     */
    public function getRealType()
    {
        return $this->real_type;
    }

    /**
     * @param string $real_type
     */
    public function setRealType($real_type)
    {
        $this->real_type = $real_type;
    }

    /**
     * @param string $category
     */
    public function setCategory($category)
    {
        $this->category = $category;
    }

    /**
     * @param string|null $pickup_datetime
     */
    public function setPickupDatetime($pickup_datetime)
    {
        $this->pickup_datetime = $pickup_datetime;
    }

    /**
     * @param bool $is_round_trip
     */
    public function setIsRoundTrip($is_round_trip)
    {
        $this->is_round_trip = $is_round_trip;
    }

    /**
     * @param float $client_price
     */
    public function setClientPrice($client_price)
    {
        $this->client_price = $client_price;
    }

    /**
     * @param float $courier_price
     */
    public function setCourierPrice($courier_price)
    {
        $this->courier_price = $courier_price;
    }

    /**
     * @return int
     */
    public function getClientId()
    {
        return $this->client_id;
    }

    /**
     * @return int|null
     */
    public function getCourierId()
    {
        return $this->courier_id;
    }

    /**
     * @return int
     */
    public function getDeliveryAreaId()
    {
        return $this->delivery_area_id;
    }

    /**
     * @return int
     */
    public function getTradingPointId()
    {
        return $this->trading_point_id;
    }

    /**
     * @return string
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * @return string
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * @return string
     */
    public function getCategory()
    {
        return $this->category;
    }

    /**
     * @return string|null
     */
    public function getPickupDatetime()
    {
        return $this->pickup_datetime;
    }

    /**
     * @return bool
     */
    public function isIsRoundTrip()
    {
        return $this->is_round_trip;
    }

    /**
     * @return float
     */
    public function getClientPrice()
    {
        return $this->client_price;
    }

    /**
     * @return float
     */
    public function getCourierPrice()
    {
        return $this->courier_price;
    }
}
