<?php

class Intelivemetrics_Unityreports_Model_Sync_ProductVariation 
extends Intelivemetrics_Unityreports_Model_Sync_Product 
implements Intelivemetrics_Unityreports_Model_Sync_Interface {

    const ENTITY_TYPE = 'product_variation';

    /**
     * @assert () == true
     * @return boolean
     */
    public function runSync() {
        $helper = Mage::helper('unityreports');
        if (Mage::getStoreConfig(Intelivemetrics_Unityreports_Model_Config::XML_GENERAL_STATUS) === '0') {
            $helper->debug('Sync is deactivated');
            return false;
        }

        try {
            $client = $this->_getClient();
            
            //get data
            $products = $this->_getData(Intelivemetrics_Unityreports_Model_Utils::getMaxItemsPerSync());
            if (is_null($products)) {
                return self::NOTHING_TO_SYNC;
            }
            
            //get token
            $response = json_decode($client->getToken(
                            $helper->getApiKey(), $helper->getApiSecret(), $helper->getLicenseKey()
            ));
            if ($response->code != 'OK') {
                $helper->debug('Cannot get a valid Token.'.$response->msg);
                return false;
            }
            
            //send data
            //no multiple sents for this kind of data
            $blob = Intelivemetrics_Unityreports_Model_Utils::prepareDataForSending($products);
            $response = json_decode($client->post(
                    $response->msg, array(
                'type' => 'SYNC',
                'data' => $blob,
                'license' => $helper->getLicenseKey(),
                'entity' => self::ENTITY_TYPE
                    )
            ));
            
             //mark sent items
            if($response->code=='OK'){
                $this->markSentItems($products);
            }

            return true;
        } catch (Exception $e) {
            $helper->debug($e, Zend_Log::ERR);
            return false;
        }
    }

    /**
     * Segna gli oggetti inviati
     * @param array $products
     */
    public function markSentItems(array $products) {
        $table = Intelivemetrics_Unityreports_Model_Utils::getTableName('unityreports/products');
        $now = date('Y-m-d H:i:s');
        try {
            foreach ($products as $product) {
                $query = "UPDATE $table SET last_sent_at='{$now}' WHERE product_id={$product['id']};";
                $this->_getDb()->query($query);
            }
        } catch (Exception $ex) {
            Mage::helper('unityreports')->debug($ex->getMessage(),Zend_Log::ERR);
        }
    }

    /**
     * Salva oggetti sincronizzati
     * @param type $response
     */
    public function saveSyncedItems($response) {
        $helper = Mage::helper('unityreports');
        $table = Intelivemetrics_Unityreports_Model_Utils::getTableName('unityreports/products');
        try {
            $json = Zend_Json::decode($response);
            foreach ($json['msg'] as $productId) {
                $now = date('Y-m-d H:i:s');
                $query = "UPDATE $table SET synced_at='{$now}' WHERE product_id={$productId}";
                $this->_getDb()->query($query);
            }
            $counter = (int) count($json['msg']);
            $helper->debug("Sincronizzati $counter variazioni prodotti");
        } catch (Exception $ex) {
            Mage::helper('unityreports')->debug($ex->getMessage(),Zend_Log::ERR);
        }
    }

    /**
     * Esegue recupero dati degli prodotti
     * 
     * @param int $max_records numero massimo di records (indicativo)
     * @return array associativo contenente i dati
     */
    protected function _getData( $limit) {
        $helper = Mage::helper('unityreports');
        $helper->debug(__METHOD__);
        $now = date('Y-m-d H:i:s');
        try {
            //set store to admin otherwise it will use flat tables
            $adminStore = Mage_Core_Model_App::ADMIN_STORE_ID;
            Mage::app()->setCurrentStore($adminStore);
            
            $collection = Mage::getModel('catalog/product')->getCollection()
                    ->addAttributeToSelect(array('updated_at','visibility','status'))
                    ->addAttributeToSort('updated_at', 'ASC');
            //add price 
            $collection->joinAttribute('price', 'catalog_product/price', 'entity_id', null, 'left', $adminStore);
            //add stock
            $collection->joinField('qty',
                'cataloginventory/stock_item',
                'qty',
                'product_id=entity_id',
                '{{table}}.stock_id=1',
                'left');
            //filter updated
            $table = Intelivemetrics_Unityreports_Model_Utils::getTableName('unityreports/products');
            $collection->joinField('synced_at',
                $table,
                'synced_at',
                'product_id=entity_id',
                "{{table}}.synced=1 AND e.updated_at > {{table}}.synced_at AND TIMESTAMPDIFF(MINUTE,last_sent_at,'{$now}')>60",
                'inner');
            $helper->debug($collection->getSelectSql()->__toString());
                     
            // se non ci sono record, esce
            if (count($collection) == 0) {
                $helper->debug('No product data found to sync');
                return null;
            }

            // processa gli ordini trovati
            $prodData = array();
            foreach ($collection as $product) {
                $prodData["item_" . $product->getEntityId()] = array(
                    'id' => $product->getId(),
                    'updated_at' => $product->getUpdatedAt(),
                    'price' => $product->getData('price'),
                    'qty' => $product->getData('qty'),
                    'visibility' => $this->_isVisible($product->getVisibility()),
                    'status' => $this->_isEnabled($product->getStatus()),
                );
            }
            
            return $prodData;
        } catch (Exception $ex) {
            $helper->debug($ex->getMessage(), Zend_Log::ERR);
            return null;
        }
    }

}

?>
