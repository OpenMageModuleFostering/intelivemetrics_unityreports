<?php

/**
 * Description of Orders
 *
 * @author Eddie
 * @date
 */
class Intelivemetrics_Unityreports_Model_Admin_Status_Sync_Customers extends Intelivemetrics_Unityreports_Model_Admin_Status_SyncPercent {

    protected $_base_tbl = null;
    protected $_unity_tbl = null;
    
    public function __construct() {
        $this->_base_tbl = Mage::getSingleton('unityreports/utils')->getTableName('customer/entity');
        $this->_unity_tbl = Mage::getSingleton('unityreports/utils')->getTableName('unityreports/customers');
        parent::__construct();
    }

}
