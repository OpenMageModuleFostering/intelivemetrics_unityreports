<?php

/**
 * Options for on/off selector
 *
 * @category  Unityreports
 * @package   Intelivemetrics_Unityreports
 * @copyright Copyright (c) 2014 Intelive Metrics Srl
 * @author    Eduard Gabriel Dumitrescu (balaur@gmail.com)
 */
class Intelivemetrics_Unityreports_Model_Admin_Status_Attributes extends Mage_Core_Model_Abstract {

    public function toOptionArray() {
        $attributes = Mage::getResourceModel('catalog/product_attribute_collection')
                ->addFieldToFilter('is_user_defined', 1)
                ->getItems();
        foreach ($attributes as $attribute) {
            $attrib_data[] = array('value' => $attribute->getAttributeCode(), 'label' => $attribute->getData('frontend_label'));
        }

        return $attrib_data;
    }

}
