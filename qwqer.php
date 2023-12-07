<?php
/**
* 2007-2023 PrestaShop
*
* NOTICE OF LICENSE
*
* This source file is subject to the Academic Free License (AFL 3.0)
* that is bundled with this package in the file LICENSE.txt.
* It is also available through the world-wide-web at this URL:
* http://opensource.org/licenses/afl-3.0.php
* If you did not receive a copy of the license and are unable to
* obtain it through the world-wide-web, please send an email
* to license@prestashop.com so we can send you a copy immediately.
*
* DISCLAIMER
*
* Do not edit or add to this file if you wish to upgrade PrestaShop to newer
* versions in the future. If you wish to customize PrestaShop for your
* needs please refer to http://www.prestashop.com for more information.
*
*  @author    PrestaShop SA <contact@prestashop.com>
*  @copyright 2007-2023 PrestaShop SA
*  @license   http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
*  International Registered Trademark & Property of PrestaShop SA
*/

if (!defined('_PS_VERSION_')) {
    exit;
}

require_once dirname(__FILE__) . '/include.php';

class Qwqer extends CarrierModule
{
    protected $config_form = false;

    public $id_carrier;

    public function __construct()
    {
        $this->name = 'qwqer';
        $this->tab = 'shipping_logistics';
        $this->version = '2.0.0';
        $this->author = 'SoftBuild';
        $this->need_instance = 0;

        /**
         * Set $this->bootstrap to true if your module is compliant with bootstrap (PrestaShop 1.6)
         */
        $this->bootstrap = true;

        parent::__construct();

        $this->displayName = $this->l('Qwqer Delivery Service');
        $this->description = $this->l('Qwqer Delivery Service Qwqer Delivery Service Qwqer Delivery Service Qwqer Delivery Service Qwqer Delivery Service Qwqer Delivery Service Qwqer Delivery Service Qwqer Delivery Service ');

        $this->ps_versions_compliancy = array('min' => '1.7', 'max' => _PS_VERSION_);
    }

    /**
     * Don't forget to create update methods if needed:
     * http://doc.prestashop.com/display/PS16/Enabling+the+Auto-Update
     */
    public function install()
    {
        Configuration::updateValue('QWQER_LIVE_MODE', false);
        Configuration::updateValue('QWQER_TITLE', $this->displayName . ' - Scheduled Delivery');
        Configuration::updateValue('QWQER_EXPRESS_DELIVERY_TITLE', $this->displayName . ' - Express Delivery');
        Configuration::updateValue('QWQER_OMNIVA_DELIVERY_TITLE', $this->displayName . ' - Omniva Parcel Terminal');
        Configuration::updateValue('QWQER_REFERENCE_ID', 0);
        Configuration::updateValue('QWQER_EXPRESS_DELIVERY_REFERENCE_ID', 0);
        Configuration::updateValue('QWQER_OMNIVA_DELIVERY_REFERENCE_ID', 0);
        Configuration::updateValue('QWQER_TRADING_POINT_ID', 2);
        Configuration::updateValue('QWQER_ORDER_CATEGORY', 'Other');
        Configuration::updateValue('QWQER_DEFAULT_SHIPPING_COST', 0);

        $store = Store::getStores($this->context->language->id);
        Configuration::updateValue('QWQER_STORE_ID', isset($store[0]) ? $store[0]['id_store'] : 0);

        $res = parent::install() &&
            $this->registerHook('actionValidateOrder') &&
            $this->registerHook('displayCarrierExtraContent') &&
            $this->registerHook('actionValidateStepComplete');

        if ($res) {
            $qwqerCarrier = new Carrier();
            $qwqerCarrier->active = true;
            $qwqerCarrier->name = $this->displayName;
            $qwqerCarrier->is_module = true;
            $qwqerCarrier->need_range = true;
            $qwqerCarrier->shipping_external = true;
            $qwqerCarrier->external_module_name = $this->name;
            foreach (Language::getIDs() as $id) {
                $qwqerCarrier->delay[$id] = '-';
            }
            $qwqerCarrier->save();

            Configuration::updateValue('QWQER_REFERENCE_ID', $qwqerCarrier->id);

            foreach (Zone::getZones() as $zone) {
                $qwqerCarrier->addZone($zone['id_zone']);
            }

            $groups_ids = array();
            $groups = Group::getGroups(Context::getContext()->language->id);

            foreach ($groups as $group) {
                $groups_ids[] = $group['id_group'];
            }
            $qwqerCarrier->setGroups($groups_ids);

            $rangeWeight = new RangeWeight();
            $rangeWeight->id_carrier = $qwqerCarrier->id;
            $rangeWeight->delimiter1 = 0;
            $rangeWeight->delimiter2 = 30;
            $rangeWeight->save();

            $qwqerExpressCarrier = new Carrier();
            $qwqerExpressCarrier->active = true;
            $qwqerExpressCarrier->name = $this->displayName . ' - Express Delivery';
            $qwqerExpressCarrier->is_module = true;
            $qwqerExpressCarrier->need_range = true;
            $qwqerExpressCarrier->shipping_external = true;
            $qwqerExpressCarrier->external_module_name = $this->name;
            foreach (Language::getIDs() as $id) {
                $qwqerExpressCarrier->delay[$id] = '-';
            }
            $qwqerExpressCarrier->save();

            Configuration::updateValue('QWQER_EXPRESS_DELIVERY_REFERENCE_ID', $qwqerExpressCarrier->id);

            foreach (Zone::getZones() as $zone) {
                $qwqerExpressCarrier->addZone($zone['id_zone']);
            }

            $groups_ids = array();
            $groups = Group::getGroups(Context::getContext()->language->id);

            foreach ($groups as $group) {
                $groups_ids[] = $group['id_group'];
            }
            $qwqerExpressCarrier->setGroups($groups_ids);

            $rangeWeight = new RangeWeight();
            $rangeWeight->id_carrier = $qwqerExpressCarrier->id;
            $rangeWeight->delimiter1 = 0;
            $rangeWeight->delimiter2 = 30;
            $rangeWeight->save();

            $qwqerOmnivaCarrier = new Carrier();
            $qwqerOmnivaCarrier->active = true;
            $qwqerOmnivaCarrier->name = $this->displayName . ' - Omniva Parcel Terminal';
            $qwqerOmnivaCarrier->is_module = true;
            $qwqerOmnivaCarrier->need_range = true;
            $qwqerOmnivaCarrier->shipping_external = true;
            $qwqerOmnivaCarrier->external_module_name = $this->name;
            foreach (Language::getIDs() as $id) {
                $qwqerOmnivaCarrier->delay[$id] = '-';
            }
            $qwqerOmnivaCarrier->save();

            Configuration::updateValue('QWQER_OMNIVA_DELIVERY_REFERENCE_ID', $qwqerOmnivaCarrier->id);

            foreach (Zone::getZones() as $zone) {
                $qwqerOmnivaCarrier->addZone($zone['id_zone']);
            }

            $groups_ids = array();
            $groups = Group::getGroups(Context::getContext()->language->id);

            foreach ($groups as $group) {
                $groups_ids[] = $group['id_group'];
            }
            $qwqerOmnivaCarrier->setGroups($groups_ids);

            $rangeWeight = new RangeWeight();
            $rangeWeight->id_carrier = $qwqerOmnivaCarrier->id;
            $rangeWeight->delimiter1 = 0;
            $rangeWeight->delimiter2 = 30;
            $rangeWeight->save();

            $addressRequiredFiled = new CustomerAddress();
            $addressRequiredFiled->addFieldsRequiredDatabase(array(
                'phone',
                'postcode'
            ));

            $storeRequiredFiled = new Store();
            $storeRequiredFiled->addFieldsRequiredDatabase(array(
                'phone',
                'postcode'
            ));
        }

        return $res;
    }

    public function uninstall()
    {
        Configuration::deleteByName('QWQER_LIVE_MODE');
        Configuration::deleteByName('QWQER_TITLE');
        Configuration::deleteByName('QWQER_EXPRESS_DELIVERY_TITLE');
        Configuration::deleteByName('QWQER_OMNIVA_DELIVERY_TITLE');
        Configuration::deleteByName('QWQER_API_KEY');
        Configuration::deleteByName('QWQER_TRADING_POINT_ID');
        Configuration::deleteByName('QWQER_STORE_ID');
        Configuration::deleteByName('QWQER_ORDER_CATEGORY');
        Configuration::deleteByName('QWQER_DEFAULT_SHIPPING_COST');

        $qwqerCarrier = Carrier::getCarrierByReference(Configuration::get('QWQER_REFERENCE_ID'));
        Configuration::deleteByName('QWQER_REFERENCE_ID');
        $qwqerExpressCarrier = Carrier::getCarrierByReference(Configuration::get('QWQER_EXPRESS_DELIVERY_REFERENCE_ID'));
        Configuration::deleteByName('QWQER_EXPRESS_DELIVERY_REFERENCE_ID');
        $qwqerOmnivaCarrier = Carrier::getCarrierByReference(Configuration::get('QWQER_OMNIVA_DELIVERY_REFERENCE_ID'));
        Configuration::deleteByName('QWQER_OMNIVA_DELIVERY_REFERENCE_ID');

        $addressRequiredFiled = new CustomerAddress();
        $objectName = $addressRequiredFiled->getObjectName();
        Db::getInstance()->execute(
            'DELETE FROM ' . _DB_PREFIX_ . 'required_field'
            . " WHERE object_name = '" . Db::getInstance()->escape($objectName) . "'");
        $storeRequiredFiled = new Store();
        $objectName = $storeRequiredFiled->getObjectName();
        Db::getInstance()->execute(
            'DELETE FROM ' . _DB_PREFIX_ . 'required_field'
            . " WHERE object_name = '" . Db::getInstance()->escape($objectName) . "'");

        return parent::uninstall()
            && $qwqerCarrier->delete()
            && $qwqerExpressCarrier->delete()
            && $qwqerOmnivaCarrier->delete();
    }

    /**
     * Load the configuration form
     */
    public function getContent()
    {
        /**
         * If values have been submitted in the form, process.
         */
        if (((bool)Tools::isSubmit('submitQwqerModule')) == true) {
            $this->postProcess();
        }

        $this->context->smarty->assign('module_dir', $this->_path);

        $output = $this->context->smarty->fetch($this->local_path.'views/templates/admin/configure.tpl');

        return /*$output.*/$this->renderForm();
    }

    /**
     * Create the form that will be displayed in the configuration of your module.
     */
    protected function renderForm()
    {
        $helper = new HelperForm();

        $helper->show_toolbar = false;
        $helper->table = $this->table;
        $helper->module = $this;
        $helper->default_form_language = $this->context->language->id;
        $helper->allow_employee_form_lang = Configuration::get('PS_BO_ALLOW_EMPLOYEE_FORM_LANG', 0);

        $helper->identifier = $this->identifier;
        $helper->submit_action = 'submitQwqerModule';
        $helper->currentIndex = $this->context->link->getAdminLink('AdminModules', false)
            .'&configure='.$this->name.'&tab_module='.$this->tab.'&module_name='.$this->name;
        $helper->token = Tools::getAdminTokenLite('AdminModules');

        $helper->tpl_vars = array(
            'fields_value' => $this->getConfigFormValues(), /* Add values for your inputs */
            'languages' => $this->context->controller->getLanguages(),
            'id_language' => $this->context->language->id,
        );

        return $helper->generateForm(array($this->getConfigForm()));
    }

    /**
     * Create the structure of your form.
     */
    protected function getConfigForm()
    {
        return array(
            'form' => array(
                'legend' => array(
                'title' => $this->l('Settings'),
                'icon' => 'icon-cogs',
                ),
                'input' => array(
                    array(
                        'type' => 'text',
                        'name' => 'QWQER_TITLE',
                        'label' => $this->l('Title'),
                    ),
                    array(
                        'type' => 'text',
                        'name' => 'QWQER_EXPRESS_DELIVERY_TITLE',
                        'label' => $this->l('Express Delivery Title'),
                    ),
                    array(
                        'type' => 'text',
                        'name' => 'QWQER_OMNIVA_DELIVERY_TITLE',
                        'label' => $this->l('Omniva Delivery Title'),
                    ),
                    /*array(
                        'type' => 'switch',
                        'label' => $this->l('Live mode'),
                        'name' => 'QWQER_LIVE_MODE',
                        'is_bool' => true,
                        'desc' => $this->l('Use this module in live mode'),
                        'values' => array(
                            array(
                                'id' => 'active_on',
                                'value' => true,
                                'label' => $this->l('Enabled')
                            ),
                            array(
                                'id' => 'active_off',
                                'value' => false,
                                'label' => $this->l('Disabled')
                            )
                        ),
                    ),*/
                    array(
                        'col' => 3,
                        'type' => 'password',
                        'desc' => $this->l('Api Key'),
                        'name' => 'QWQER_API_KEY',
                        'label' => $this->l('Api Key'),
                    ),
                    array(
                        'col' => 1,
                        'row' => 1,
                        'type' => 'text',
                        'desc' => $this->l('Trading point id'),
                        'name' => 'QWQER_TRADING_POINT_ID',
                        'label' => $this->l('Trading point id'),
                    ),
                    array(
                        'col' => 1,
                        'row' => 1,
                        'type' => 'text',
                        'desc' => $this->l('Default shipping cost'),
                        'name' => 'QWQER_DEFAULT_SHIPPING_COST',
                        'label' => $this->l('Shipping Cost'),
                    ),
                    array(
                        'col' => 5,
                        'type' => 'select',
                        'desc' => $this->l('Need for using store address, and coordinates'),
                        'name' => 'QWQER_STORE_ID',
                        'label' => $this->l('Store'),
                        'default_value' => (int)Configuration::get('QWQER_STORE_ID'),
                        'options' => array(
                            'query' => Store::getStores($this->context->language->id),
                            'id' => 'id_store',
                            'name' => 'name'
                        )
                    ),
                    array(
                        'col' => 5,
                        'type' => 'select',
                        'desc' => $this->l('Types of shipping products'),
                        'name' => 'QWQER_ORDER_CATEGORY',
                        'label' => $this->l('Order Category'),
                        'default_value' => Configuration::get('QWQER_ORDER_CATEGORY'),
                        'options' => array(
                            'query' => array(
                                array(
                                    'id' => 'Flowers',
                                    'name' => 'Flowers',
                                ),
                                array(
                                    'id' => 'Food',
                                    'name' => 'Food',
                                ),
                                array(
                                    'id' => 'Cake',
                                    'name' => 'Cake',
                                ),
                                array(
                                    'id' => 'Present',
                                    'name' => 'Present',
                                ),
                                array(
                                    'id' => 'Clothes',
                                    'name' => 'Clothes',
                                ),
                                array(
                                    'id' => 'Document',
                                    'name' => 'Document',
                                ),
                                array(
                                    'id' => 'Jewelry',
                                    'name' => 'Jewelry',
                                ),
                                array(
                                    'id' => 'Other',
                                    'name' => 'Other',
                                ),
                            ),
                            'id' => 'id',
                            'name' => 'name'
                        )
                    ),
                ),
                'submit' => array(
                    'title' => $this->l('Save'),
                ),
            ),
        );
    }

    /**
     * Set values for the inputs.
     */
    protected function getConfigFormValues()
    {
        return array(
            'QWQER_LIVE_MODE' => Configuration::get('QWQER_LIVE_MODE'),
            'QWQER_API_KEY' => Configuration::get('QWQER_API_KEY'),
            'QWQER_TITLE' => Configuration::get('QWQER_TITLE'),
            'QWQER_EXPRESS_DELIVERY_TITLE' => Configuration::get('QWQER_EXPRESS_DELIVERY_TITLE'),
            'QWQER_OMNIVA_DELIVERY_TITLE' => Configuration::get('QWQER_OMNIVA_DELIVERY_TITLE'),
            'QWQER_TRADING_POINT_ID' => Configuration::get('QWQER_TRADING_POINT_ID'),
            'QWQER_STORE_ID' => Configuration::get('QWQER_STORE_ID'),
            'QWQER_ORDER_CATEGORY' => Configuration::get('QWQER_ORDER_CATEGORY'),
            'QWQER_DEFAULT_SHIPPING_COST' => Configuration::get('QWQER_DEFAULT_SHIPPING_COST'),
        );
    }

    /**
     * @param $force_all
     * @return bool
     * @throws PrestaShopException
     */
    public function disable($force_all = false)
    {
        if (Configuration::get('QWQER_REFERENCE_ID')) {
            $qwqerCarrier = Carrier::getCarrierByReference(Configuration::get('QWQER_REFERENCE_ID'));
            $qwqerCarrier->active = false;
            $qwqerCarrier->save();
        }

        if (Configuration::get('QWQER_EXPRESS_DELIVERY_REFERENCE_ID')) {
            $qwqerCarrier = Carrier::getCarrierByReference(Configuration::get('QWQER_EXPRESS_DELIVERY_REFERENCE_ID'));
            $qwqerCarrier->active = false;
            $qwqerCarrier->save();
        }

        if (Configuration::get('QWQER_OMNIVA_DELIVERY_REFERENCE_ID')) {
            $qwqerCarrier = Carrier::getCarrierByReference(Configuration::get('QWQER_OMNIVA_DELIVERY_REFERENCE_ID'));
            $qwqerCarrier->active = false;
            $qwqerCarrier->save();
        }

        return parent::disable($force_all);
    }

    /**
     * @param $force_all
     * @return bool
     * @throws PrestaShopException
     */
    public function enable($force_all = false)
    {
        if (Configuration::get('QWQER_REFERENCE_ID')) {
            $qwqerCarrier = Carrier::getCarrierByReference(Configuration::get('QWQER_REFERENCE_ID'));
            $qwqerCarrier->active = true;
            $qwqerCarrier->save();
        }

        if (Configuration::get('QWQER_EXPRESS_DELIVERY_REFERENCE_ID')) {
            $qwqerCarrier = Carrier::getCarrierByReference(Configuration::get('QWQER_EXPRESS_DELIVERY_REFERENCE_ID'));
            $qwqerCarrier->active = true;
            $qwqerCarrier->save();
        }

        if (Configuration::get('QWQER_OMNIVA_DELIVERY_REFERENCE_ID')) {
            $qwqerCarrier = Carrier::getCarrierByReference(Configuration::get('QWQER_OMNIVA_DELIVERY_REFERENCE_ID'));
            $qwqerCarrier->active = true;
            $qwqerCarrier->save();
        }

        return parent::enable($force_all);
    }


    /**
     * Save form data.
     */
    protected function postProcess()
    {
        $form_values = $this->getConfigFormValues();

        foreach (array_keys($form_values) as $key) {
            Configuration::updateValue($key, Tools::getValue($key));
        }

        $qwqerCarrier = Carrier::getCarrierByReference(Configuration::get('QWQER_REFERENCE_ID'));
        $qwqerCarrier->name = Configuration::get('QWQER_TITLE');
        $qwqerCarrier->save();

        $qwqerCarrier = Carrier::getCarrierByReference(Configuration::get('QWQER_EXPRESS_DELIVERY_REFERENCE_ID'));
        $qwqerCarrier->name = Configuration::get('QWQER_EXPRESS_DELIVERY_TITLE');
        $qwqerCarrier->save();

        $qwqerCarrier = Carrier::getCarrierByReference(Configuration::get('QWQER_OMNIVA_DELIVERY_REFERENCE_ID'));
        $qwqerCarrier->name = Configuration::get('QWQER_OMNIVA_DELIVERY_TITLE');
        $qwqerCarrier->save();
    }

    /**
     * @param $params
     * @param $shipping_cost
     * @return false|int
     */
    public function getOrderShippingCost($params, $shipping_cost)
    {
        return $this->getOrderShippingCostExternal($params);
    }

    /**
     * @param Cart $params
     * @return int
     */
    public function getOrderShippingCostExternal($params)
    {
        $cacheKey = 'Qwqer::getOrderShippingCostExternal_' . $params->id . '_' . $params->id_address_delivery
            . '_' . $this->id_carrier;
        if (!Cache::isStored($cacheKey)) {
            try {
                $qwqerClient = new QwqerClient();
                $shippingAddress = new Address($params->id_address_delivery);
                $realType = QwqerClient::REAL_TYPE_SCHEDULED_DELIVERY;
                $qwqerExpressCarrier = Carrier::getCarrierByReference(Configuration::get('QWQER_EXPRESS_DELIVERY_REFERENCE_ID'));
                if ($this->id_carrier == $qwqerExpressCarrier->id) {
                    $realType = QwqerClient::REAL_TYPE_EXPRESS_DELIVERY;
                }
                $qwqerOmnivaCarrier = Carrier::getCarrierByReference(Configuration::get('QWQER_OMNIVA_DELIVERY_REFERENCE_ID'));
                if ($this->id_carrier == $qwqerOmnivaCarrier->id) {
                    $realType = QwqerClient::REAL_TYPE_OMNIVA_DELIVERY;
                }
                $shippingCost = $qwqerClient->getShippingCost($shippingAddress, $realType);
                if ($shippingCost != null) {
                    Cache::store($cacheKey, $shippingCost);
                } else {
                    Cache::store($cacheKey, Configuration::get('QWQER_DEFAULT_SHIPPING_COST'));
                }
            } catch (Exception $e) {
                return false;
            }
        }

        return Cache::retrieve($cacheKey);
    }

    /**
     * @param $params
     * @return void
     * @throws PrestaShopDatabaseException
     * @throws PrestaShopException
     */
    public function hookActionValidateOrder($params)
    {
        /**
         * @var Order $order
         */
        $order = $params['order'];
        $qwqerCarrier = Carrier::getCarrierByReference(Configuration::get('QWQER_REFERENCE_ID'));
        $this->setDeliveryTrackingNumber($qwqerCarrier->id, $order, QwqerClient::REAL_TYPE_SCHEDULED_DELIVERY);
        $qwqerExpressCarrier = Carrier::getCarrierByReference(Configuration::get('QWQER_EXPRESS_DELIVERY_REFERENCE_ID'));
        $this->setDeliveryTrackingNumber($qwqerExpressCarrier->id, $order, QwqerClient::REAL_TYPE_EXPRESS_DELIVERY);
        $qwqerExpressCarrier = Carrier::getCarrierByReference(Configuration::get('QWQER_OMNIVA_DELIVERY_REFERENCE_ID'));
        $this->setDeliveryTrackingNumber($qwqerExpressCarrier->id, $order, QwqerClient::REAL_TYPE_OMNIVA_DELIVERY);

        if ($order->id_carrier == $qwqerExpressCarrier->id) {
            $qwqerClient = new QwqerClient();
            $parcelMachines = $qwqerClient->getParcelMachines();

            foreach ($parcelMachines as $machine) {
                if ($machine['id'] == Context::getContext()->cookie->selected_parcel_machine_id) {

                    $currentDeliveryAddress = new Address($order->id_address_delivery);
                    $addSuffix = false;
                    if (mb_strlen($machine['name']) > Address::$definition['fields']['alias']['size']) {
                        $addSuffix = true;
                    }
                    $alias = mb_substr($machine['name'], 0, Address::$definition['fields']['alias']['size'] - 3) . ($addSuffix ? '...' : '');

                    $deliveryAddress = new Address($this->getAddressIdByAliasAndCustomerId($alias, $order->id_customer));
                    if (!Validate::isLoadedObject($deliveryAddress)) {
                        $country = new Country(Country::getByIso($machine['country']));
                        $deliveryAddress->id_country = Validate::isLoadedObject($country) ? $country->id : null;
                        $deliveryAddress->city = $machine['city'];
                        $deliveryAddress->alias = $alias;
                        $deliveryAddress->firstname = $currentDeliveryAddress->firstname;
                        $deliveryAddress->lastname = $currentDeliveryAddress->lastname;
                        $deliveryAddress->id_customer = $currentDeliveryAddress->id_customer;


                        $deliveryAddress->address1 = $machine['name'];
                        $deliveryAddress->postcode = $currentDeliveryAddress->postcode;
                        $deliveryAddress->phone = $currentDeliveryAddress->phone;
                        $deliveryAddress->phone_mobile = $currentDeliveryAddress->phone_mobile;

                        $deliveryAddress->save();
                    }

                    $order->id_address_delivery = $deliveryAddress->id;
                    $order->save();

                    break;
                }
            }
        }
    }

    public function setDeliveryTrackingNumber($carrierId, $order, $realType = QwqerClient::REAL_TYPE_SCHEDULED_DELIVERY)
    {
        if ($order->id_carrier == $carrierId) {
            $qwqerClient = new QwqerClient();
            $shippingAddress = new Address($order->id_address_delivery);
            $shippingOrderId = $qwqerClient->createDeliveryOrder($shippingAddress, $realType);

            $shippings = $order->getShipping();
            foreach ($shippings as $shipping) {
                if ($shipping['id_carrier'] == $carrierId) {
                    $orderCarrier = new OrderCarrier($shipping['id_order_carrier']);
                    $orderCarrier->tracking_number = $shippingOrderId;
                    $orderCarrier->save();
                }
            }
        }
    }

    /**
     * @param $params
     * @return false|string|void
     * @throws SmartyException
     */
    public function hookDisplayCarrierExtraContent($params)
    {
        $carrier = $params['carrier'];
        $qwqerOmnivaCarrier = Carrier::getCarrierByReference(Configuration::get('QWQER_OMNIVA_DELIVERY_REFERENCE_ID'));

        if ($carrier['id'] == $qwqerOmnivaCarrier->id) {
            $qwqerClient = new QwqerClient();
            $parcelMachines = $qwqerClient->getParcelMachines();

            $this->context->smarty->assign([
                'parcel_machines' => $parcelMachines,
                'selected_parcel_machine_id' => Context::getContext()->cookie->selected_parcel_machine_id,
            ]);

            return $this->context->smarty->fetch($this->local_path.'views/templates/front/parcel_machines.tpl');
        }
    }

    public function hookActionValidateStepComplete($params)
    {
        $parcelMachineId = isset($params['request_params']['qwqer_parcel_machine'])
            ? $params['request_params']['qwqer_parcel_machine']
            : null;
        if (!empty($parcelMachineId)) {
            Context::getContext()->cookie->selected_parcel_machine_id = $parcelMachineId;
            Context::getContext()->cookie->write();
        }
    }

    public static function getAddressIdByAliasAndCustomerId($alias, $customerId)
    {
        $query = new DbQuery();
        $query->select('id_address');
        $query->from('address');
        $query->where('alias = "'.pSQL($alias) . '"');
        $query->where('deleted = 0');
        $query->where('id_customer = ' . (int)$customerId);

        return Db::getInstance(_PS_USE_SQL_SLAVE_)->getValue($query);
    }
}
