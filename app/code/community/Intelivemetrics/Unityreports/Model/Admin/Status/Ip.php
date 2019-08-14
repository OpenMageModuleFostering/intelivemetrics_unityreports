<?php

/**
 * Description of Orders
 *
 * @author Eddie
 * @date
 */
class Intelivemetrics_Unityreports_Model_Admin_Status_Ip extends Intelivemetrics_Unityreports_Model_Admin_Status_SyncPercent {

    public function toHtml() {
        $host= gethostname();
$ip = gethostbyname($host);
            
        return $_SERVER['SERVER_ADDR'];

    }

}
