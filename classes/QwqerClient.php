<?php

use GuzzleHttp\Client as HttpClient;
use GuzzleHttp\Exception\RequestException;
use libphonenumber\PhoneNumberFormat;
use libphonenumber\PhoneNumberUtil;

class QwqerClient
{
    protected $apiKey;

    protected $apiUrl = 'https://api.qwqer.lv/v1/plugins/presta-shop/';

    protected $tradingPointId;

    /**
     * @var HttpClient
     */
    protected $client;

    const REAL_TYPE_SCHEDULED_DELIVERY = 'ScheduledDelivery';
    const REAL_TYPE_EXPRESS_DELIVERY = 'ExpressDelivery';
    const REAL_TYPE_OMNIVA_DELIVERY = 'OmnivaParcelTerminal';

    /**
     *
     */
    public function __construct()
    {
        $this->apiKey = Configuration::get('QWQER_API_KEY');
        $this->tradingPointId = Configuration::get('QWQER_TRADING_POINT_ID');

        $this->client = new HttpClient();
    }

    /**
     * @throws Exception
     */
    protected function request($method, $path, array $options = [])
    {
        $result = null;

        try {
            $request = $this->client->createRequest(
                $method,
                sprintf('%s/%s', rtrim( $this->apiUrl, '/'), ltrim($path, '/')),
                array(
                    'headers' => array(
                        'Authorization' => sprintf('Bearer %s', $this->apiKey),
                        'Accept' => 'application/json',
                        'Content-Type' => 'application/json',
                        'Accept-Encoding' => 'gzip, deflate, br',
                        'Cache-Control' => 'no-cache',
                    ),
                    'query' => $options,
                ));
            $response = $this->client->send($request);

            if ($response->getStatusCode() == 200) {
                $result = json_decode($response->getBody()->getContents(), true);
            }
        } catch (RequestException $e) {
            PrestaShopLogger::addLog(
                sprintf(
                    'Request to qwqer api, has response error: %s, response body: %s',
                    $e->getMessage(),
                    $e->getResponse()->getBody()->getContents()
                )
            );

            throw new Exception($e->getMessage());
        }

        return $result;
    }

    protected function changeTradingPointId($path)
    {
        return str_replace('{trading-point-id}', $this->tradingPointId, $path);
    }

    public function getParams(Address $destinationAddress, $realType = self::REAL_TYPE_SCHEDULED_DELIVERY)
    {
        $params = array();

        $phoneNumberUtil = PhoneNumberUtil::getInstance();

        $deliveryOrder = new QwqerDeliveryOrder();
        $deliveryOrder->setRealType($realType);
        if ($deliveryOrder->getRealType() == self::REAL_TYPE_OMNIVA_DELIVERY) {
            //the field should not be added to request in others request types
            $deliveryOrder->parcel_size = 'L';
        }
        $deliveryOrder->setCategory(Configuration::get('QWQER_ORDER_CATEGORY'));
        $params = array_merge($params, (array)$deliveryOrder);

        $deliveryOrderPlace = new QwqerDeliveryOrderPlace();

        $store = new Store(Configuration::get('QWQER_STORE_ID'), Context::getContext()->language->id);
        $deliveryOrderPlace->setName($store->name);
        $country = new Country($store->id_country, Context::getContext()->language->id);
        $phone = $store->phone;
        if (!$this->hasCountryCode($phone)) {
            $phoneNumberObject = $phoneNumberUtil->parse($phone, $country->iso_code);
            $phone = $phoneNumberUtil->format($phoneNumberObject, PhoneNumberFormat::E164);
        }
        $deliveryOrderPlace->setPhone($phone);
        $state = new State($store->id_state, Context::getContext()->language->id);
        $address = $this->getRightStructuredAddress($store->address1, $store->address2, $state->name, $store->city, $store->postcode, $country->name);
        $deliveryOrderPlace->setAddress($address);
        $deliveryOrderPlace->setCoordinates(array(
            $store->latitude,
            $store->longitude
        ));
        $params['origin'] = (array)$deliveryOrderPlace;

        if ($deliveryOrder->getRealType() == self::REAL_TYPE_OMNIVA_DELIVERY) {
            $parcelMachines = $this->getParcelMachines();
            $parcelKey = 0;
            if (Context::getContext()->cookie->selected_parcel_machine_id) {
                foreach ($parcelMachines as $key => $parcelMachine) {
                    if (Context::getContext()->cookie->selected_parcel_machine_id == $parcelMachine['id']) {
                        $parcelKey = $key;
                    }
                }
            }
            if (count($parcelMachines) > 0) {
                $firstParcelMachine = $parcelMachines[$parcelKey];
                $destinationDeliveryOrderPlace = new QwqerDeliveryOrderPlace();
                $destinationDeliveryOrderPlace->setName($firstParcelMachine['name']);
                $destinationDeliveryOrderPlace->setPhone($phone);
                $destinationDeliveryOrderPlace->setAddress($firstParcelMachine['name']);
                $destinationDeliveryOrderPlace->setCoordinates($firstParcelMachine['coordinates']);
                $params['destinations'] = array((array)$destinationDeliveryOrderPlace);
            }
        } else {
            $destinationDeliveryOrderPlace = new QwqerDeliveryOrderPlace();
            $destinationDeliveryOrderPlace->setName($destinationAddress->firstname . ' ' . $destinationAddress->lastname);
            $phone = $destinationAddress->phone;
            if (!$this->hasCountryCode($phone)) {
                $phoneNumberObject = $phoneNumberUtil->parse($phone, $country->iso_code);
                $phone = $phoneNumberUtil->format($phoneNumberObject, PhoneNumberFormat::E164);
            }
            $destinationDeliveryOrderPlace->setPhone($phone);
            $destinationCountry = new Country($destinationAddress->id_country, Context::getContext()->language->id);
            $destinationState = new State($destinationAddress->id_state, Context::getContext()->language->id);
            $address = $this->getRightStructuredAddress($destinationAddress->address1, $destinationAddress->address2, $destinationState->name, $destinationAddress->city, $destinationAddress->postcode, $destinationCountry->name);
            $destinationDeliveryOrderPlace->setAddress($address);
            $destinationCoordinates = $this->getCoordinates($address);

            $destinationDeliveryOrderPlace->setCoordinates($destinationCoordinates);
            $params['destinations'] = array((array)$destinationDeliveryOrderPlace);
        }

        return $params;
    }
    public function getShippingCost(Address $destinationAddress, $realType = self::REAL_TYPE_SCHEDULED_DELIVERY)
    {
        $params = $this->getParams($destinationAddress, $realType);

        $response = $this->request(
            'POST',
            $this->changeTradingPointId(
                '/clients/auth/trading-points/{trading-point-id}/delivery-orders/get-price'
            ),
            $params
        );

        return isset($response['data']) ? (int)$response['data']['client_price'] / 100 : null;
    }

    public function createDeliveryOrder(Address $destinationAddress, $realType = self::REAL_TYPE_SCHEDULED_DELIVERY)
    {
        $params = $this->getParams($destinationAddress, $realType);

        $response = $this->request(
            'POST',
            $this->changeTradingPointId(
                '/clients/auth/trading-points/{trading-point-id}/delivery-orders'
            ),
            $params
        );

        return isset($response['data']) ? $response['data']['id'] : null;
    }

    /**
     * @param $address1
     * @param $address2
     * @param $stateName
     * @param $cityName
     * @param $postcode
     * @param $countName
     * @return string
     */
    public function getRightStructuredAddress($address1, $address2, $stateName, $cityName, $postcode, $countName)
    {
        return sprintf(
            '%s, %s, %s, %s, %s',
            $address1 . ' ' . $address2,
            $stateName,
            $cityName,
            $postcode,
            $countName
        );
    }

    /**
     * @param $phone
     * @return bool
     */
    public function hasCountryCode($phone)
    {
        if (substr($phone, 0, 1) == '+') {
            return true;
        }

        return false;
    }

    /**
     * @param $structuredAddress
     * @return mixed|null
     */
    public function getCoordinates($structuredAddress)
    {
        $response = $this->request('GET', sprintf('/places/geocode/%s', $structuredAddress));
        return isset($response['data']) ? $response['data']['coordinates'] : null;
    }

    /**
     * @return array|mixed
     * @throws Exception
     */
    public function getParcelMachines()
    {
        $response = $this->request('GET', '/parcel-machines');
        return isset($response['data']) ? $response['data']['omniva'] : [];
    }

    /**
     * @param $deliveryOrderId
     * @return false|string
     */
    public function getDeliveryOrderCover($deliveryOrderId)
    {
        return file_get_contents(sprintf('https://qwqer.lv/storage/delivery-order-covers/%s.pdf', $deliveryOrderId));
    }

    /**
     * Return information about merchant(work hours, etc.)
     *
     * @return array|null
     * @throws Exception
     */
    public function getMerchantInfo(): ?array
    {
        $response = $this->request(
            'GET',
            $this->changeTradingPointId(
                '/trading-points/{trading-point-id}?include=working_hours,merchant'
            )
        );

        return $response['data'] ?? null;
    }
}
