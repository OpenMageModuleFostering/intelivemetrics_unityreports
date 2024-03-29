<?php

/**
 * Reset all synced items action
 *
 * @category  Unityreports
 * @package   Intelivemetrics_Unityreports
 * @copyright Copyright (c) 2014 Intelive Metrics Srl
 * @author    Eduard Gabriel Dumitrescu (balaur@gmail.com)
 */


class Intelivemetrics_Unityreports_Model_Request_ResetAll extends Intelivemetrics_Unityreports_Model_Request_Base implements Intelivemetrics_Unityreports_Model_Request_Interface {

    public function execute($settings=array()) {
        Intelivemetrics_Unityreports_Model_Utils::log(__METHOD__);

        $syncActive = Mage::getConfig(Intelivemetrics_Unityreports_Model_Config::XML_GENERAL_STATUS);

        //stop syncing
        if ($syncActive) {
            $this->stopSync();
        }

        //reset
        $tables = array(
            Intelivemetrics_Unityreports_Model_Utils::getTableName('unityreports/abcarts'),
            Intelivemetrics_Unityreports_Model_Utils::getTableName('unityreports/creditnotes'),
            Intelivemetrics_Unityreports_Model_Utils::getTableName('unityreports/customers'),
            Intelivemetrics_Unityreports_Model_Utils::getTableName('unityreports/invoices'),
            Intelivemetrics_Unityreports_Model_Utils::getTableName('unityreports/orders'),
        );
        $db = Mage::getSingleton('unityreports/utils')->getDb();
        foreach ($tables as $table) {
            try {
                $db->query("TRUNCATE $table;");
                Intelivemetrics_Unityreports_Model_Utils::log("Truncated $table");
            } catch (Exception $ex) {
                Intelivemetrics_Unityreports_Model_Utils::log($ex->getMessage());
                //something went wrong. Stop
                return false;
            }
        }

        //start syncing
        if ($syncActive) {
            $this->startSync();
        }

        //alles gut
        return $out = array(
            'done' => true,
        );
    }

}

?>
