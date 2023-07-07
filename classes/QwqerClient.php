<?php

use GuzzleHttp\Client as HttpClient;
use GuzzleHttp\Exception\RequestException;
use libphonenumber\PhoneNumberFormat;
use libphonenumber\PhoneNumberUtil;

class QwqerClient
{
    protected $apiKey;

    protected $apiUrl = 'https://qwqer.hostcream.eu/api/v1/';

    protected $tradingPointId;

    /**
     * @var HttpClient
     */
    protected $client;

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

    public function getParams(Address $destinationAddress)
    {
        $params = array();

        $phoneNumberUtil = PhoneNumberUtil::getInstance();

        $deliveryOrder = new QwqerDeliveryOrder();
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

        return $params;
    }
    public function getShippingCost(Address $destinationAddress)
    {
        $params = $this->getParams($destinationAddress);

        $response = $this->request(
            'POST',
            $this->changeTradingPointId(
                '/clients/auth/trading-points/{trading-point-id}/delivery-orders/get-price'
            ),
            $params
        );

        return isset($response['data']) ? (int)$response['data']['client_price'] / 100 : null;
    }

    public function createDeliveryOrder(Address $destinationAddress)
    {
        $params = $this->getParams($destinationAddress);

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
}
