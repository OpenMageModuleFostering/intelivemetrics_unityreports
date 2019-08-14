<?php

/**
 * Base class for all sync models
 *
 * @category  Unityreports
 * @package   Intelivemetrics_Unityreports
 * @copyright Copyright (c) 2014 Intelive Metrics Srl
 * @author    Eduard Gabriel Dumitrescu (balaur@gmail.com)
 */
class Intelivemetrics_Unityreports_Model_Sync {

    const MAX_SENTS = 3; //quante volte il connettore prova ad inviare i dati
    const NOTHING_TO_SYNC = 1;

    /**
     * Gets a SOAP client
     * @return \Zend_Soap_Client
     */
    protected function _getClient() {
        return Mage::getSingleton('unityreports/utils')->getSoapClient();
    }

    public function _getDb() {
        return Mage::getSingleton('unityreports/utils')->getDb();
    }
    
    protected function _appIsOk($requireActiveSync = true) {
        return Mage::helper('unityreports')->appIsOk($requireActiveSync);
    }

    public function getMaxSents() {
        return self::MAX_SENTS;
    }

    protected function _getEntityType() {
        $class = get_called_class();
        $caller = new $class;
        return $caller::ENTITY_TYPE;
    }

    public function runSync() {
        $helper = Mage::helper('unityreports');
        if (!$helper->isActive()) {
            $helper->debug('Sync is deactivated');
            return false;
        }

        try {
            $client = $this->_getClient();

            //add some tracing
            if (function_exists('newrelic_add_custom_parameter')) {
                newrelic_add_custom_parameter('sync_type', $this->_getEntityType());
                newrelic_add_custom_parameter('sync_max', Intelivemetrics_Unityreports_Model_Utils::getMaxItemsPerSync());
            }

            //get data
            $data = $this->_getData(Intelivemetrics_Unityreports_Model_Utils::getMaxItemsPerSync());
            if (is_null($data)) {
                return self::NOTHING_TO_SYNC;
            }
            
            //get token
            $response = json_decode($client->getToken(
                            $helper->getApiKey(), $helper->getApiSecret(), $helper->getLicenseKey()
            ));
            if ($response->code != 'OK') {
                $helper->debug('Cannot get a valid Token.' . $response->msg);
                return false;
            }
            $token = $response->msg;

            //send data
            $blob = Intelivemetrics_Unityreports_Model_Utils::prepareDataForSending($data);
            $response = json_decode($client->post(
                            $token, array(
                        'type' => 'SYNC',
                        'data' => $blob,
                        'license' => $helper->getLicenseKey(),
                        'entity' => $this->_getEntityType()
                            )
            ));
            $helper->debug('Sending '.count($data) .' '.$this->_getEntityType());

            //mark sent items
            if ($response->code == 'OK') {
                $this->markSentItems($data);
            }

            return true;
        } catch (Exception $e) {
            $helper->debug($e, Zend_Log::ERR);
            return false;
        }
    }

}

?>
