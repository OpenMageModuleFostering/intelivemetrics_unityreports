<?php

/**
 * Data sync cron
 *
 * @category  Unityreports
 * @package   Intelivemetrics_Unityreports
 * @copyright Copyright (c) 2014 Intelive Metrics Srl
 * @author    Eduard Gabriel Dumitrescu (balaur@gmail.com)
 */

class Intelivemetrics_Unityreports_Model_Cron_Sync 
extends Intelivemetrics_Unityreports_Model_Cron{
    
    /**
     * Metodo invocato dal Cron di Magento (vedi xml di configurazione)
     * @assert () == true
     * @return null
     */
    public function sync() {
        $helper = Mage::helper('unityreports');
        try {
            //Check app status before syncing
            if(!$this->_appIsOk()){
                $helper->debug('App is not ok, cannot send data');
                return false;
            }
            
            //start syncing
            if (($res=Mage::getModel('unityreports/sync_product')->runSync())!==Intelivemetrics_Unityreports_Model_Sync::NOTHING_TO_SYNC) {
                $helper->debug('OK syncing products');
                return $res;
            }
            
            if (($res=Mage::getModel('unityreports/sync_order')->runSync())!==Intelivemetrics_Unityreports_Model_Sync::NOTHING_TO_SYNC) {
                $helper->debug('OK syncing orders');
                return $res;
            }

            if (($res=Mage::getModel('unityreports/sync_invoice')->runSync())!==Intelivemetrics_Unityreports_Model_Sync::NOTHING_TO_SYNC) {
                $helper->debug('OK syncing invoices');
                return $res;
            }

            if (($res=Mage::getModel('unityreports/sync_creditnote')->runSync())!==Intelivemetrics_Unityreports_Model_Sync::NOTHING_TO_SYNC) {
                $helper->debug('OK syncing credit memos');
                return $res;
            }

            if (($res=Mage::getModel('unityreports/sync_customer')->runSync())!==Intelivemetrics_Unityreports_Model_Sync::NOTHING_TO_SYNC) {
                $helper->debug('OK syncing customers');
                return $res;
            }
            
            if (($res=Mage::getModel('unityreports/sync_customerAction')->runSync())!==Intelivemetrics_Unityreports_Model_Sync::NOTHING_TO_SYNC) {
                $helper->debug('OK syncing customer actions');
                return $res;
            }

            if (($res=Mage::getModel('unityreports/sync_abcart')->runSync())!==Intelivemetrics_Unityreports_Model_Sync::NOTHING_TO_SYNC) {
                $helper->debug('OK syncing abcarts');
                return $res;
            }
            
            if (($res=Mage::getModel('unityreports/sync_productVariation')->runSync())!==Intelivemetrics_Unityreports_Model_Sync::NOTHING_TO_SYNC) {
                $helper->debug('OK syncing product variations');
                return $res;
            }
            
        } catch (Exception $e) {
            $helper->debug($e, Zend_Log::ERR);
            $helper->debug('FILE: ' . __FILE__.'LINE: ' . __LINE__);
            return false;
        }
        
        return true;
    }

}

?>
