<?php

/**
 * Syncs prod stock
 *
 * @category  Unityreports
 * @package   Intelivemetrics_Unityreports
 * @copyright Copyright (c) 2014 Intelive Metrics Srl
 * @author    Eduard Gabriel Dumitrescu (balaur@gmail.com)
 */
class Intelivemetrics_Unityreports_Model_Cron_Stock extends Intelivemetrics_Unityreports_Model_Cron {

    /**
     * Called with magento cron
     * @return boolean
     * @assert () == true
     */
    public function runSync() {
        $helper = Mage::helper('unityreports');
        $helper->debug('*******NEW STOCK SYNC*******');
        try {
            //Check app status before syncing
            if (!$this->_appIsOk()) {
                $helper->debug('Endpoint is not receiving');
                return false;
            }

            //get a soap client
            $client = $this->_getClient();

            //get token
            $responseToken = json_decode($client->getToken(
                            $helper->getApiKey(), $helper->getApiSecret(), $helper->getLicenseKey()
            ));
            if ($responseToken->code != 'OK') {
                $helper->debug('Cannot get a valid Token.' . $responseToken->msg);
                return false;
            }

            //send data
            $data = $this->_getData();
            $blob = Intelivemetrics_Unityreports_Model_Utils::prepareDataForSending($data);
            $helper->debug('STRING length' . mb_strlen($blob));
            $client->post(
                    $responseToken->msg, array(
                'type' => 'STOCK',
                'data' => $blob,
                'license' => $helper->getLicenseKey()
                    )
            );
            $helper->debug('Ok - sent ' . count($data['stock']) . ' stock counters');

            return true;
        } catch (Exception $e) {
            $helper->debug($e, Zend_Log::ERR);
            $helper->debug('FILE: ' . __FILE__ . 'LINE: ' . __LINE__);
            return false;
        }
    }

    /**
     * Get all stock data > 0
     * @return array associativo contenente i dati
     */
    protected function _getData() {
        $helper = Mage::helper('unityreports');
        $db = Mage::getSingleton('unityreports/utils')->getDb();
        $table = Mage::getSingleton('unityreports/utils')->getTableName('cataloginventory/stock_item');
        $sql = "SELECT product_id as id, SUM(qty) as s FROM {$table} GROUP BY product_id HAVING s>0";

        try {
            $result = $db->query($sql);

            if (!$result) {
                $helper->debug("Cannot query: {$sql}");
                return FALSE;
            }

            return array(
                'date' => date('Y-m-d'),
                'stock' => $result->fetchAll(PDO::FETCH_ASSOC)
            );
        } catch (Exception $ex) {
            $helper->debug($ex, Zend_Log::ERR);
            $helper->debug('FILE: ' . __FILE__ . 'LINE: ' . __LINE__);
            return null;
        }
    }

}
