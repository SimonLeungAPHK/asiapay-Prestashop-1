<?php
/**
* 2007-2017 PrestaShop
*
* NOTICE OF LICENSE
*
* This source file is subject to the Academic Free License (AFL 3.0)
* that is bundled with this package in the file LICENSE.txt.
* It is also available through the world-wide-web at this URL:
* http://opensource.org/licenses/afl-3.0.php
* If you did not receive a copy of the license and are unable to
* obtain it through the world-wide-web, please send an email
* to license@prestashop.com so we can send you a copy immediately.
*
* DISCLAIMER
*
* Do not edit or add to this file if you wish to upgrade PrestaShop to newer
* versions in the future. If you wish to customize PrestaShop for your
* needs please refer to http://www.prestashop.com for more information.
*
*  @author    PrestaShop SA <contact@prestashop.com>
*  @copyright 2007-2017 PrestaShop SA
*  @license   http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
*  International Registered Trademark & Property of PrestaShop SA
*/

class AsiapayRedirectModuleFrontController extends ModuleFrontController
{
    /**
     * Do whatever you have to before redirecting the customer on the website of your payment processor.
     */
    public function postProcess()
    {
        
			$cart = $this->context->cart;
			
			$paymentUrl			= Configuration::get('PAYMENT_URL');
			$merchantId			= Configuration::get('MERCHANT_ID');
			$currency			= Configuration::get('CURRENCY');
			$payType			= Configuration::get('PAY_TYPE');
			$payMethod			= Configuration::get('PAY_METHOD');
			
			$lang 				= new Language($this->context->language->id);
			$language			= $this->getAsiaPayLanguageCode($lang->iso_code);
			
			$secureHashSecret	= Configuration::get('SECURE_HASH_SECRET');
				
			$amount				= (float) $cart->getOrderTotal(true, Cart::BOTH);
			
			$cart_id 			= $cart->id;
		
			$orderRef			= $cart_id ;
			$remark				= $cart_id ;
			
			/* to identify if using http or https */
			if(!empty($_SERVER["HTTPS"])) {
				if($_SERVER["HTTPS"]!=="off") {
					$httpOrhttps = 'https';
				} else {
					$httpOrhttps = 'http';
				} 
			} else {
				$httpOrhttps = 'http';
			}
			
			$successUrl			=  $httpOrhttps.'://'.$_SERVER['HTTP_HOST'].__PS_BASE_URI__.'module/asiapay/confirmation?success=true&cart_id='.$cart_id;
			$failUrl			=  $httpOrhttps.'://'.$_SERVER['HTTP_HOST'].__PS_BASE_URI__.'module/asiapay/confirmation?fail=true&cart_id='.$cart_id;
			$cancelUrl			=  $httpOrhttps.'://'.$_SERVER['HTTP_HOST'].__PS_BASE_URI__.'module/asiapay/confirmation?cancel=true&cart_id='.$cart_id;
			
			if($secureHashSecret == ''){
				$secureHash = '';
			}else{
				$secureHash	= $this->generatePaymentSecureHash($merchantId, $orderRef, $currency, $amount, $payType, $secureHashSecret);
			}
			
            $this->context->smarty->assign(array(
				'paymentUrl' 		=> $paymentUrl,
				'merchantId' 		=> $merchantId,
				'payType'		 	=> $payType,
				'payMethod'			=> $payMethod,
				'a_language'		=> $language,
				'a_currency'		=> $currency,
				'amount'			=> $amount,
				'orderRef'			=> $orderRef,
				'remark'			=> $remark,
				'successUrl'		=> $successUrl,
				'failUrl' 			=> $failUrl,
				'cancelUrl' 		=> $cancelUrl,
				'secureHash'		=> $secureHash,
				'failRetry'			=> 'no'
            ));

            return $this->setTemplate('module:asiapay/views/templates/front/redirect.tpl');
        //}
    }
	
	function getAsiaPayLanguageCode($iso_code)
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
			case 'tw':
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
	
	function generatePaymentSecureHash($merchantId, $merchantReferenceNumber, $currencyCode, $amount, $paymentType, $secureHashSecret) 
	{
		$buffer = $merchantId . '|' . $merchantReferenceNumber . '|' . $currencyCode . '|' . $amount . '|' . $paymentType . '|' . $secureHashSecret;
		return sha1($buffer);
	}

    
}
