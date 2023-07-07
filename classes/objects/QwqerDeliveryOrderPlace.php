<?php

class QwqerDeliveryOrderPlace
{
    /**
     * @var int
     */
//    public $delivery_order_id = null;
//
//    /**
//     * @var string
//     */
//    public $type = null;
//
//    /**
//     * @var string|null
//     */
//    public $payment_type = null;
//
//    /**
//     * @var float
//     */
//    public $payment_amount = null;

    /**
     * Format array<[float Lat, float Lng]>
     *
     * @var array
     */
    public $coordinates = array();

    /**
     * @var string
     */
    public $address = null;

    /**
     * @var string|null
     */
    public $name = null;

    /**
     * @var string|null
     */
//    public $email = null;

    /**
     * @var string|null
     */
    public $phone = null;

//    /**
//     * @var string|null
//     */
//    public $entrance = null;
//
//    /**
//     * @var string|null
//     */
//    public $apartment = null;
//
//    /**
//     * @var string|null
//     */
//    public $floor = null;
//
//    /**
//     * @var string|null
//     */
//    public $comment = null;

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
     * @return string
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * @param string $type
     */
    public function setType($type)
    {
        $this->type = $type;
    }

    /**
     * @return string|null
     */
    public function getPaymentType()
    {
        return $this->payment_type;
    }

    /**
     * @param string|null $payment_type
     */
    public function setPaymentType($payment_type)
    {
        $this->payment_type = $payment_type;
    }

    /**
     * @return float
     */
    public function getPaymentAmount()
    {
        return $this->payment_amount;
    }

    /**
     * @param float $payment_amount
     */
    public function setPaymentAmount($payment_amount)
    {
        $this->payment_amount = $payment_amount;
    }

    /**
     * @return array
     */
    public function getCoordinates()
    {
        return $this->coordinates;
    }

    /**
     * @param array $coordinates
     */
    public function setCoordinates($coordinates)
    {
        $this->coordinates = $coordinates;
    }

    /**
     * @return string
     */
    public function getAddress()
    {
        return $this->address;
    }

    /**
     * @param string $address
     */
    public function setAddress($address)
    {
        $this->address = $address;
    }

    /**
     * @return string|null
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param string|null $name
     */
    public function setName($name)
    {
        $this->name = $name;
    }

    /**
     * @return string|null
     */
    public function getEmail()
    {
        return $this->email;
    }

    /**
     * @param string|null $email
     */
    public function setEmail($email)
    {
        $this->email = $email;
    }

    /**
     * @return string|null
     */
    public function getPhone()
    {
        return $this->phone;
    }

    /**
     * @param string|null $phone
     */
    public function setPhone($phone)
    {
        $this->phone = $phone;
    }

    /**
     * @return string|null
     */
    public function getEntrance()
    {
        return $this->entrance;
    }

    /**
     * @param string|null $entrance
     */
    public function setEntrance($entrance)
    {
        $this->entrance = $entrance;
    }

    /**
     * @return string|null
     */
    public function getApartment()
    {
        return $this->apartment;
    }

    /**
     * @param string|null $apartment
     */
    public function setApartment($apartment)
    {
        $this->apartment = $apartment;
    }

    /**
     * @return string|null
     */
    public function getFloor()
    {
        return $this->floor;
    }

    /**
     * @param string|null $floor
     */
    public function setFloor($floor)
    {
        $this->floor = $floor;
    }

    /**
     * @return string|null
     */
    public function getComment()
    {
        return $this->comment;
    }

    /**
     * @param string|null $comment
     */
    public function setComment($comment)
    {
        $this->comment = $comment;
    }
}
