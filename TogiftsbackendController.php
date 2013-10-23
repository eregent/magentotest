<?php
class Eugen_Togifts_Adminhtml_TogiftsbackendController extends Mage_Adminhtml_Controller_Action
{
	public function indexAction()
    {
        $action = Mage::app()->getRequest()->getParam('action');
        
        if($action=='conforder'){
            $datedel = Mage::app()->getRequest()->getParam('datedel');
            $uuid = Mage::app()->getRequest()->getParam('uuid');
            if($datedel!=''&&$uuid!=''){
                $this->setConfirmOrder($uuid, $datedel);
            }
        }elseif($action=='delivered'){
            $uuid = Mage::app()->getRequest()->getParam('uuid');
            if($uuid!=''){
                $this->setConfirmDelivered($uuid);
            }            
        }
        $sort = Mage::app()->getRequest()->getParam('sortor');
        Mage::register('mytogift', $this->getGiftsList($sort));
                
       $this->loadLayout();
	   $this->_title($this->__("Хочу в подарок"));
	   $this->renderLayout();
    }
    
    public function setConfirmDelivered($uuid){
        $headers = array('Content-Type: application/json');

        $feed_url = "https://boomstarter.ru/api/v1.1/partners/gifts/".$uuid."/delivery_state";
        
        $body = '{ "shop_uuid":"27bb3bf8-f7a2-49e9-8445-9d062c7d3871", "shop_token": "f9e3218e-4eb1-4c03-8e40-fa4b41a0a4b2", "delivery_state": "delivery" }';
        
        $curl = new Varien_Http_Adapter_Curl();
        $curl->setConfig(array(
            'header' => false,
            'timeout'   => 15 
        ));
        $options = array(CURLOPT_CUSTOMREQUEST => 'PUT');
        $curl->setOptions($options);        
        $curl->write(Zend_Http_Client::POST, $feed_url, '1.1', $headers, $body);
        $data = $curl->read();
        if ($curl->getInfo(CURLINFO_HTTP_CODE) != 200) {
            Mage::getSingleton('adminhtml/session')->addError($this->__('Sorry! Error Confirm :( '));
        }else{
            Mage::getSingleton('adminhtml/session')->addError($this->__('Готово'));
        }        
    }
    
    public function setConfirmOrder($uuid, $date){
        $headers = array('Content-Type: application/json');
        $randval = rand(10000000, 19999999);

        $feed_url = "https://boomstarter.ru/api/v1.1/partners/gifts/".$uuid."/order";
        $feed_url_shipp = "https://boomstarter.ru/api/v1.1/partners/gifts/".$uuid."/schedule";
        
        $body_order = '{ "shop_uuid":"27bb3bf8-f7a2-49e9-8445-9d062c7d3871", "shop_token": "f9e3218e-4eb1-4c03-8e40-fa4b41a0a4b2", "order_id": "'.$randval.'" }';
        $body_date = '{ "shop_uuid":"27bb3bf8-f7a2-49e9-8445-9d062c7d3871", "shop_token": "f9e3218e-4eb1-4c03-8e40-fa4b41a0a4b2", "delivery_date": "'.$date.'" }';
        
        $curl = new Varien_Http_Adapter_Curl();
        $curl->setConfig(array(
            'header' => false,
            'timeout'   => 15 
        ));
        $curl->write(Zend_Http_Client::POST, $feed_url, '1.1', $headers, $body_order);
        $data = $curl->read();
        if ($curl->getInfo(CURLINFO_HTTP_CODE) != 200) {
            Mage::getSingleton('adminhtml/session')->addError($this->__('Sorry! Error Confirm :( '));
        }else{
            $curl->close();
            $curl = new Varien_Http_Adapter_Curl();
            $curl->setConfig(array(
                'header' => false,
                'timeout'   => 15 
            ));
            $curl->write(Zend_Http_Client::POST, $feed_url_shipp, '1.1', $headers, $body_date);
            $data = $curl->read();
            if ($curl->getInfo(CURLINFO_HTTP_CODE) != 200) {
                Mage::getSingleton('adminhtml/session')->addError($this->__('Sorry! Error Set Date :( '));
            }else{
                Mage::getSingleton('adminhtml/session')->addError($this->__('Готово'));
            }
        }
        $curl->close();    
    }
    
    public function getGiftsList($sort){
        $filter = '';
        if($sort==1){
            $filter = '/pending';
        }elseif($sort==2){
            $filter = '/shipping';
        }elseif($sort==3){
            $filter = '/delivered';
        }
        
        $headers = array('Content-Type: application/json');
        $get = '?shop_uuid=27bb3bf8-f7a2-49e9-8445-9d062c7d3871&shop_token=f9e3218e-4eb1-4c03-8e40-fa4b41a0a4b2';
        $feed_url = "https://boomstarter.ru/api/v1.1/partners/gifts".$filter.$get;
        
        $curl = new Varien_Http_Adapter_Curl();
        $curl->setConfig(array(
            'header' => false,
            'timeout'   => 15 
        ));
        $curl->write(Zend_Http_Client::GET, $feed_url, '1.1', $headers);
        $data = $curl->read();
        if ($curl->getInfo(CURLINFO_HTTP_CODE) != 200) {
            Mage::getSingleton('adminhtml/session')->addError($this->__('Sorry! Error :( '));
        }else{
            $data = json_decode($data);
            return $data; 
        }
        $curl->close();        
    }
}
