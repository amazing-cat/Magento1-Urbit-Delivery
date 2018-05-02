<?php

/**
 * Class Vaimo_UrbIt_PostcodeController
 */
class Vaimo_UrbIt_PostcodeController extends Mage_Core_Controller_Front_Action
{
    /**
     * @throws Varien_Exception
     */
    public function validateAction()
    {
        $postcode = $this->getRequest()->getPost('postcode');

        $postcodeCheckEnabled = Mage::getStoreConfig('carriers/' . Vaimo_UrbIt_Model_System_Config_Source_Environment::CARRIER_CODE . '/enable_postcode_check');

        if ($postcodeCheckEnabled) {
            $api = Mage::getModel("vaimo_urbit/urbit_api", new Vaimo_UrbIt_Model_Urbit_Api_Client());

            $response = $api->validatePostcode($postcode);

            $this->getResponse()->setHeader('Content-type', 'application/json');
            $this->getResponse()->setBody(json_encode($response));
        }
    }

    public function checksettingsAction()
    {
        $postcodeCheckEnabled = Mage::getStoreConfig('carriers/' . Vaimo_UrbIt_Model_System_Config_Source_Environment::CARRIER_CODE . '/enable_postcode_check');

        $this->getResponse()->setHeader('Content-type', 'application/json');
        $this->getResponse()->setBody(json_encode($postcodeCheckEnabled));
    }

    /**
     * Check if postcode is eligible
     *
     * @param $zipCode
     * @return bool
     */
    protected function _checkZipCode($zipCode)
    {
        $postcodes = Mage::getStoreConfig('carriers/' . Vaimo_UrbIt_Model_System_Config_Source_Environment::CARRIER_CODE . '/eligible_postcodes');
        $postcodes = preg_split('/,\s*/', $postcodes);
        if (is_array($postcodes)) {
            foreach ($postcodes as $postcode) {
                $result = fnmatch($postcode, $zipCode);
                if ($result) {
                    return true;
                }
            }
        }

        return false;
    }

    /**
     * validate delivery address by Urb-it API (GET request)
     */
    protected function validateDeliveryAddressAction()
    {
        $street = $this->getRequest()->getParam("street", false);
        $postcode = $this->getRequest()->getParam("postcode", false);
        $city = $this->getRequest()->getParam("city", false);

        $api = Mage::getModel("vaimo_urbit/urbit_api", new Vaimo_UrbIt_Model_Urbit_Api_Client());

        $responseObj = $api->validateDeliveryAddress($street, $postcode, $city);

        $result = array(
            'ajaxCheckValidateDelivery' => $responseObj->hasError() ? 'false' : 'true',
            'error_code'                => $responseObj->getHttpCode(),
            'error_message'             => $responseObj->getErrorMessage(),
        );

        $this->getResponse()->setHeader('Content-type', 'application/json');
        $this->getResponse()->setBody(json_encode($result));
    }
}
