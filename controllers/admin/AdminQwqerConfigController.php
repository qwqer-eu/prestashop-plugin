<?php

require_once(_PS_MODULE_DIR_.'/qwqer/include.php');

class AdminQwqerConfigController extends ModuleAdminController
{
    public function postProcess()
    {
        if (Tools::getValue('action') == 'delivery_cover_download') {
            $qwqerClient = new QwqerClient();
            $deliveryOrderCover = $qwqerClient->getDeliveryOrderCover(Tools::getValue('delivery_order_id'));

            header("Content-type:application/pdf");
            header(sprintf('Content-Disposition:attachment;filename="delivery_order_cover_of_%s.pdf"', Tools::getValue('delivery_order_id')));

            echo $deliveryOrderCover;
        }

        exit;
    }
}
