<?php

/**
 * Base class for sync percent
 *
 * @category  Unityreports
 * @package   Intelivemetrics_Unityreports
 * @copyright Copyright (c) 2016 Intelive Metrics Srl
 * @author    Eduard Gabriel Dumitrescu (balaur@gmail.com)
 */

class Intelivemetrics_Unityreports_Model_Admin_Status_SyncPercent extends Mage_Core_Model_Abstract {
    
    protected $_base_tbl=null;
    protected $_unity_tbl=null;
    
    public function setBaseTbl($t){
        $this->_base_tbl = $t;
        return $this;
    }
    
    public function getBaseTbl(){
        return $this->_base_tbl;
    }
    
    public function setUnityTbl($t){
        $this->_unity_tbl = $t;
        return $this;
    }
    
    public function getUnityTbl(){
        return $this->_unity_tbl;
    }

    protected function _getItemsCount($table) {
        $helper = Mage::helper('unityreports');
        $db = Mage::getSingleton('unityreports/utils')->getDb();
        $result = $db->query($query = "SELECT COUNT(*) as c FROM {$table}");

        if (!$result) {
            $helper->debug("Cannot query: {$query}");
            return FALSE;
        }

        $row = $result->fetch(PDO::FETCH_ASSOC);
        if (!$row) {
            return 0;
        }

        return $row['c'];
    }
    
    public function toHtml() {
        $total = $this->_getItemsCount($this->getBaseTbl());
        $done = $this->_getItemsCount($this->getUnityTbl());
        if($done>$total) $done=$total;
        $perc = round($done*100/$total,2);
            
        return "<b>{$perc}%</b> - synced {$done} items out of {$total} ";

    }

    public function toOptionArray() {
        return array();
    }
}
