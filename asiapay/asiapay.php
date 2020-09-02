<?php

use PrestaShop\PrestaShop\Core\Payment\PaymentOption;

if (!defined('_PS_VERSION_'))
    exit;

class asiapay extends PaymentModule
{
	private $_html = '';
	private $_postErrors = array();

	public function __construct()
	{
		$this->name = 'asiapay';
		$this->displayName = 'AsiaPay\'s (PayDollar/PesoPay/SiamPay) Payment Service';
		$this->tab = 'payments_gateways';
		$this->author = 'Asiapay';
		$this->version = '0.8.1';

		$config = Configuration::getMultiple(array('PAYMENT_URL', 'MERCHANT_ID', 'CURRENCY', 'PAY_TYPE', 'PAY_METHOD', 'SECURE_HASH_SECRET', 'TRANSACTION_TYPE', 'CHALLENGE_PREFERENCE'));

		if (isset($config['PAYMENT_URL']))
			$this->PAYMENT_URL = $config['PAYMENT_URL'];
		if (isset($config['MERCHANT_ID']))
			$this->MERCHANT_ID = $config['MERCHANT_ID'];
		if (isset($config['CURRENCY']))
			$this->CURRENCY = $config['CURRENCY'];
		if (isset($config['PAY_TYPE']))
			$this->PAY_TYPE = $config['PAY_TYPE'];
		if (isset($config['PAY_METHOD']))
			$this->PAY_METHOD = $config['PAY_METHOD'];
		
		if($config['SECURE_HASH_SECRET'] == null){
			$this->SECURE_HASH_SECRET = '';
		}else{
			$this->SECURE_HASH_SECRET = $config['SECURE_HASH_SECRET'];
		}

		if (isset($config['TRANSACTION_TYPE']))
			$this->PAY_METHOD = $config['TRANSACTION_TYPE'];
		if (isset($config['CHALLENGE_PREFERENCE']))
			$this->PAY_METHOD = $config['CHALLENGE_PREFERENCE'];
			
		parent::__construct();
		$this->page = basename(__FILE__, '.php');
		$this->description = 'Accept payments with AsiaPay\'s (PayDollar/PesoPay/SiamPay) Payment Service';
		
	}


	function install()
	{
		if (!parent::install() || 
		!$this->registerHook('paymentReturn') || 
		!$this->registerHook('paymentOptions')||
		!$this->registerHook('header')||
		!$this->registerHook('payment')) {
            return false;
        }
		return true;			
	}


	
	
	function uninstall()
	{
		if (!Configuration::deleteByName('PAYMENT_URL')||
		!Configuration::deleteByName('MERCHANT_ID')||
		!Configuration::deleteByName('CURRENCY')||
		!Configuration::deleteByName('PAY_TYPE')||
		!Configuration::deleteByName('PAY_METHOD')||
		!Configuration::deleteByName('SECURE_HASH_SECRET')||
		!Configuration::deleteByName('TRANSACTION_TYPE')||
		!Configuration::deleteByName('CHALLENGE_PREFERENCE')||
		!parent::uninstall())
			return false;
		return true;
	}


	
	
	function getContent()
	{
		
		if (Tools::isSubmit('btnSubmit')) {
            $this->_postValidation();
            if (!count($this->_postErrors))
                $this->_html .= $this->_postProcess();
            else
                foreach ($this->_postErrors as $err)
                    $this->_html .= "<div class='alert error'>{$err}</div>";
        } else {
            $this->_html .= '<br />';
        }
		
		$this->context->smarty->assign('module_dir', $this->_path);
        
		$output = $this->context->smarty->fetch($this->local_path.'views/templates/admin/template.tpl');
		
        $this->_html .= $this->renderForm();
        
        return $output.$this->_html;
	}
	
	
	function generatePaymentSecureHash($merchantId, $merchantReferenceNumber, $currencyCode, $amount, $paymentType, $secureHashSecret) 
	{
		$buffer = $merchantId . '|' . $merchantReferenceNumber . '|' . $currencyCode . '|' . $amount . '|' . $paymentType . '|' . $secureHashSecret;
		return sha1($buffer);
	}
	
	
	function verifyPaymentDatafeed($src, $prc, $successCode, $merchantReferenceNumber, $paydollarReferenceNumber, $currencyCode, $amount, $payerAuthenticationStatus, $secureHashSecret, $secureHash) 
	{
		$buffer = $src . '|' . $prc . '|' . $successCode . '|' . $merchantReferenceNumber . '|' . $paydollarReferenceNumber . '|' . $currencyCode . '|' . $amount . '|' . $payerAuthenticationStatus . '|' . $secureHashSecret;

		$verifyData = sha1($buffer);

		if ($secureHash == $verifyData) {
			return true;
		}

		return false;
	}

	
	/*function hookPayment($params)
	{
		global $smarty;

		$smarty->assign(array('this_path' => $this->_path));

		return $this->display(__FILE__, 'payment.tpl');
	}*/

	public function hookPaymentOptions($params)
	{
		if (!$this->active) {
            return;
        }
		$payment_options = [    
        	$this->getExternalPaymentOption($params),
        ];

		return $payment_options;
	}
	
	public function getExternalPaymentOption($params) {

        $externalOption = new PaymentOption();
        
        try {
            
            $externalOption->setCallToActionText($this->l('PayDollar/PesoPay/SiamPay Payment Service'))
            ->setAction($this->context->link->getModuleLink($this->name, 'redirect', array(), true))
            ->setLogo(Media::getMediaPath(_PS_MODULE_DIR_ . $this->name . '/asiapay.gif'));
            
            return $externalOption;
        }
        catch (Exception $e) {
            var_dump($e->getMessage());
            die;
        }
    }
	
	
	public function hookPaymentReturn($params)
	{
		//global $smarty;
		$state = $params['order']->getCurrentState();
		if ($state == _PS_OS_OUTOFSTOCK_ or $state == _PS_OS_PAYMENT_){
			$this->smarty->assign('status', 'ok');
		}else{
			$this->smarty->assign('status', 'failed');
		}
		return $this->display(__FILE__, 'views/templates/hook/payment_return.tpl');
	}


	private function _getAsiaPayLanguageCode($iso_code)
	{
		$asiapay_language_code = '';
		
		switch ($iso_code)
		{
			case 'en':
				$asiapay_language_code = 'E';
				break;
			case 'zh':
				$asiapay_language_code = 'C';
				break;
			case 'zh':
				$asiapay_language_code = 'C';
				break;
			case 'ko':
				$asiapay_language_code = 'K';
				break;
			case 'ja':
				$asiapay_language_code = 'J';
				break;
			case 'th':
				$asiapay_language_code = 'T';
				break;
			case 'fr':
				$asiapay_language_code = 'F';
				break;
			case 'de':
				$asiapay_language_code = 'G';
				break;
			case 'ru':
				$asiapay_language_code = 'R';
				break;
			case 'es':
				$asiapay_language_code = 'S';
				break;
			default:
				$asiapay_language_code = 'E';
		}
		
		return $asiapay_language_code;
	}
	
	private function _postValidation()
	{
		if (Tools::isSubmit('btnSubmit'))
		{
			if (!Tools::getValue('PAYMENT_URL'))
				$this->_postErrors[] = $this->l('Payment URL is required.');
			if (!Tools::getValue('MERCHANT_ID'))
				$this->_postErrors[] = $this->l('Merchant ID is required.');
			if (!Tools::getValue('CURRENCY'))
				$this->_postErrors[] = $this->l('Currency is required.');
			if (!Tools::getValue('PAY_TYPE'))
				$this->_postErrors[] = $this->l('Pay Type is required.');
			if (!Tools::getValue('PAY_METHOD'))
				$this->_postErrors[] = $this->l('Pay Method is required.');
			if (!Tools::getValue('TRANSACTION_TYPE'))
				$this->_postErrors[] = $this->l('Transaction Type is required.');
			if (!Tools::getValue('CHALLENGE_PREFERENCE'))
				$this->_postErrors[] = $this->l('Challenge Preference is required.');
		}
	}

	private function _postProcess()
	{
		if (Tools::isSubmit('btnSubmit'))
		{
			Configuration::updateValue('PAYMENT_URL', Tools::getValue('PAYMENT_URL'));
			Configuration::updateValue('MERCHANT_ID', Tools::getValue('MERCHANT_ID'));
			Configuration::updateValue('CURRENCY', Tools::getValue('CURRENCY'));
			Configuration::updateValue('PAY_TYPE', Tools::getValue('PAY_TYPE'));
			Configuration::updateValue('PAY_METHOD', Tools::getValue('PAY_METHOD'));
			Configuration::updateValue('TRANSACTION_TYPE', Tools::getValue('TRANSACTION_TYPE'));
			Configuration::updateValue('CHALLENGE_PREFERENCE', Tools::getValue('CHALLENGE_PREFERENCE'));

			if(Tools::getValue('SECURE_HASH_SECRET')){
				$secureHashSecret = Tools::getValue('SECURE_HASH_SECRET');
			}else{
				$secureHashSecret = '';
			}			
			Configuration::updateValue('SECURE_HASH_SECRET', $secureHashSecret);
		}
		$this->_html .= $this->displayConfirmation($this->l('Settings updated'));
	}
	
	public function renderForm() {

        $fields_form = array(
            'form' => array(
                'legend' => array(
                    'title' => $this->l('AsiaPay Setup')
                ),
                'input' => array(
                    array(
                        'type' => 'text',
                        'label' => $this->l('Payment URL'),
                        'name' => 'PAYMENT_URL',
                        'required' => true
                    ),
                    array(
                        'type' => 'text',
                        'label' => $this->l('Merchant Id'),
                        'name' => 'MERCHANT_ID',
                        'required' => true
                    ),
                    array(
                        'type' => 'text',
                        'label' => $this->l('Currency'),
                        'name' => 'CURRENCY',
                        'required' => true
                    ),
                    array(
                        'type' => 'text',
                        'label' => $this->l('Pay Type'),
                        'name' => 'PAY_TYPE',
                        'required' => true
                    ),
                    array(
                        'type' => 'text',
                        'label' => $this->l('Pay Method'),
                        'name' => 'PAY_METHOD',
                        'required' => true
                    ),
                    array(
                        'type' => 'text',
                        'label' => $this->l('Secure Hash Secret'),
                        'name' => 'SECURE_HASH_SECRET',
                        'required' => false
                    ),
                    array(
                        'type' => 'text',
                        'label' => $this->l('Transaction Type'),
                        'name' => 'TRANSACTION_TYPE',
                        'required' => true
                    ),
                    array(
                        'type' => 'text',
                        'label' => $this->l('Challenge Preference'),
                        'name' => 'CHALLENGE_PREFERENCE',
                        'required' => true
                    )
                ),
                'submit' => array(
                    'title' => $this->l('Save')
                )
            )
        );
        
        $helper                           = new HelperForm();
        $helper->show_toolbar             = false;
        $helper->table                    = $this->table;
        $lang                             = new Language((int) Configuration::get('PS_LANG_DEFAULT'));
        $helper->default_form_language    = $lang->id;
        $helper->allow_employee_form_lang = Configuration::get('PS_BO_ALLOW_EMPLOYEE_FORM_LANG') ? Configuration::get('PS_BO_ALLOW_EMPLOYEE_FORM_LANG') : 0;
        $this->fields_form                = array();
        $helper->id                       = (int) Tools::getValue('id_carrier');
        $helper->identifier               = $this->identifier;
        $helper->submit_action            = 'btnSubmit';
        $helper->currentIndex             = $this->context->link->getAdminLink('AdminModules', false) . '&configure=' . $this->name . '&tab_module=' . $this->tab . '&module_name=' . $this->name;
        $helper->token                    = Tools::getAdminTokenLite('AdminModules');
        $helper->tpl_vars                 = array(
            'fields_value' => $this->getConfigFieldsValues(),
            'languages' => $this->context->controller->getLanguages(),
            'id_language' => $this->context->language->id
        );
        
        return $helper->generateForm(array(
            $fields_form
        ));
    }
	
	public function getConfigFieldsValues() {

        return array(
            'PAYMENT_URL' => Tools::getValue('PAYMENT_URL', Configuration::get('PAYMENT_URL')),
            'MERCHANT_ID' => Tools::getValue('MERCHANT_ID', Configuration::get('MERCHANT_ID')),
            'CURRENCY' => Tools::getValue('CURRENCY', Configuration::get('CURRENCY')),
            'PAY_TYPE' => Tools::getValue('PAY_TYPE', Configuration::get('PAY_TYPE')),
            'PAY_METHOD' => Tools::getValue('PAY_METHOD', Configuration::get('PAY_METHOD')),
            'SECURE_HASH_SECRET' => Tools::getValue('SECURE_HASH_SECRET', Configuration::get('SECURE_HASH_SECRET')),
            'TRANSACTION_TYPE' => Tools::getValue('TRANSACTION_TYPE', Configuration::get('TRANSACTION_TYPE')),
            'CHALLENGE_PREFERENCE' => Tools::getValue('CHALLENGE_PREFERENCE', Configuration::get('CHALLENGE_PREFERENCE')),
        );
    }
}

?>
