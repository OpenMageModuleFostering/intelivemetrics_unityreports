<?php

/**
 * Controller publicly available actions
 * 
 * @category  Unityreports
 * @package   Intelivemetrics_Unityreports
 * @copyright Copyright (c) 2014 Intelive Metrics Srl
 * @author    Eduard Gabriel Dumitrescu (balaur@gmail.com)
 */
class Intelivemetrics_Unityreports_PublicController extends Mage_Core_Controller_Front_Action {

    const ERR = 'ERROR:';
    const HEADER = 'UnityReports: OK';

    public function imageAction() {
        try {
            $id = (int) $this->getRequest()->getParam('id');
            if (!$id) {
                die(self::ERR . 'Missing prod ID');
            }
            $w = (int) $this->getRequest()->getParam('w');
            if (!$w) {
                $w = 100;
            }

            $p = Mage::getModel('catalog/product')->load($id);
            if (!$p->getSku()) {
                die(self::ERR . 'Cannot load product');
            }

            //if this sku doesn't have an image and is type simple, we can check his parent for image
            if ((!$p->getImage() || $p->getImage() == 'no_selection') && $p->getTypeId() == 'simple') {
                list( $parentId ) = Mage::getModel('catalog/product_type_configurable')
                        ->getParentIdsByChild($p->getId());
                $p->load($parentId);
                if (!$p->getSku()) {
                    die(self::ERR . 'Cannot load parent product');
                }
            }

            //we always do square images
            $path = Mage::helper('catalog/image')
                    ->init($p, 'image')
                    ->resize($w, $w);
            
            //output image
            $ext = strtolower(pathinfo($path, PATHINFO_EXTENSION));
            switch ($ext) {
                case 'gif':
                    $type = 'image/gif';
                    break;
                case 'jpg':
                case 'jpeg':
                    $type = 'image/jpeg';
                    break;
                case 'png':
                    $type = 'image/png';
                    break;
                default:
                    $type = 'unknown';
                    break;
            }
            if ($type != 'unknown') {
                header('Content-Type:' . $type);
                header(self::HEADER);
                readfile($path);
            }
        } catch (Exception $ex) {
            die(self::ERR . $ex->getMessage());
        }
    }


}
