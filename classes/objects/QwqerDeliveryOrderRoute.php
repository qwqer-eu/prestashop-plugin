<?php

class QwqerDeliveryOrderRoute
{
    /**
     * @var int
     */
    public $delivery_order_id;

    /**
     * @var int
     */
    public $origin_delivery_order_place_id;

    /**
     * @var int
     */
    public $destination_delivery_order_place_id;

    /**
     * @var string
     */
    public $status;

    /**
     * @var int
     */
    public $distance;

    /**
     * Format: H:i:s
     *
     * @var string
     */
    public $duration;

    /**
     * @return int
     */
    public function getDeliveryOrderId()
    {
        return $this->delivery_order_id;
    }

    /**
     * @param int $delivery_order_id
     */
    public function setDeliveryOrderId($delivery_order_id)
    {
        $this->delivery_order_id = $delivery_order_id;
    }

    /**
     * @return int
     */
    public function getOriginDeliveryOrderPlaceId()
    {
        return $this->origin_delivery_order_place_id;
    }

    /**
     * @param int $origin_delivery_order_place_id
     */
    public function setOriginDeliveryOrderPlaceId($origin_delivery_order_place_id)
    {
        $this->origin_delivery_order_place_id = $origin_delivery_order_place_id;
    }

    /**
     * @return int
     */
    public function getDestinationDeliveryOrderPlaceId()
    {
        return $this->destination_delivery_order_place_id;
    }

    /**
     * @param int $destination_delivery_order_place_id
     */
    public function setDestinationDeliveryOrderPlaceId($destination_delivery_order_place_id)
    {
        $this->destination_delivery_order_place_id = $destination_delivery_order_place_id;
    }

    /**
     * @return string
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * @param string $status
     */
    public function setStatus($status)
    {
        $this->status = $status;
    }

    /**
     * @return int
     */
    public function getDistance()
    {
        return $this->distance;
    }

    /**
     * @param int $distance
     */
    public function setDistance($distance)
    {
        $this->distance = $distance;
    }

    /**
     * @return string
     */
    public function getDuration()
    {
        return $this->duration;
    }

    /**
     * @param string $duration
     */
    public function setDuration($duration)
    {
        $this->duration = $duration;
    }
}
