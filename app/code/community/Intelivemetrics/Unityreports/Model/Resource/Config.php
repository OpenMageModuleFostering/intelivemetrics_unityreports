<?php

/**
 * @category  Unityreports
 * @package   Intelivemetrics_Unityreports
 * @copyright Copyright (c) 2014 Intelive Metrics Srl
 * @author    Eduard Gabriel Dumitrescu (balaur@gmail.com)
 */

class Intelivemetrics_Unityreports_Model_Resource_Config extends Mage_Core_Model_Resource_Db_Abstract{
    
    protected function _construct()
    {
        $this->_init('unityreports/config', 'entity_type');
        $this->_isPkAutoIncrement = false;
    }
    
} 

?>
