<?php

/**
 * Checks status of the sync cron
 *
 * @category  Unityreports
 * @package   Intelivemetrics_Unityreports
 * @copyright Copyright (c) 2014 Intelive Metrics Srl
 * @author    Eduard Gabriel Dumitrescu (balaur@gmail.com)
 */
class Intelivemetrics_Unityreports_Model_Admin_Status_Sync_Orders extends Intelivemetrics_Unityreports_Model_Admin_Status_SyncPercent {

    protected $_base_tbl = null;
    protected $_unity_tbl = null;
    
    public function __construct() {
        $this->_base_tbl = Mage::getSingleton('unityreports/utils')->getTableName('sales/order');
        $this->_unity_tbl = Mage::getSingleton('unityreports/utils')->getTableName('unityreports/orders');
        parent::__construct();
    }

}
