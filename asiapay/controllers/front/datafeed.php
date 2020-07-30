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

class AsiapayDatafeedModuleFrontController extends ModuleFrontController
{
    /**
     * This class should be use by your Instant Payment
     * Notification system to validate the order remotely
     */
    public function postProcess()
    {
		$success_code 		= Tools::isSubmit('successcode') 	? Tools::getValue('successcode') 	: "" ;
		$prc 				= Tools::isSubmit('prc') 			? Tools::getValue('prc') 			: "" ;
		$src 				= Tools::isSubmit('src') 			? Tools::getValue('src') 			: "" ;
		$order_ref 			= Tools::isSubmit('Ref') 			? Tools::getValue('Ref') 			: "" ;
		$payment_ref 		= Tools::isSubmit('PayRef') 		? Tools::getValue('PayRef')			: "" ;
		$currency	 		= Tools::isSubmit('Cur') 			? Tools::getValue('Cur') 			: "" ;
		$amount 			= Tools::isSubmit('Amt') 			? Tools::getValue('Amt') 			: "" ;
		$payerAuth 			= Tools::isSubmit('payerAuth') 		? Tools::getValue('payerAuth') 		: "" ;
		$secureHash 		= Tools::isSubmit('secureHash') 	? Tools::getValue('secureHash')		: "" ;
		$cart_id 			= Tools::isSubmit('remark') 		? Tools::getValue('remark')			: "" ;
		
        
		$result = "OK! ";

		$secureHashSecret	= Configuration::get('SECURE_HASH_SECRET');
		
		$cart = new Cart((int) $cart_id);
        $customer = new Customer((int) $cart->id_customer);
		
		
		$order_id = $cart->id;
		$currency_id = $cart->id_currency;
		
		$order = new Order($order_id);
        
		$asiapay = new asiapay();
		
		if($secureHashSecret != '') {
			$secureHashs = explode(',', $secureHash);
			$isValidSecureHash = false;
			while ( list ( $key, $value ) = each ( $secureHashs ) ) {
				$check = $this->verifyPaymentDatafeed($src, $prc, $success_code, $order_ref, $payment_ref, $currency, $amount, $payerAuth, $secureHashSecret, $value);
				if($check){
					$isValidSecureHash = true;
					break;
				}
			}
		}

		if($secureHashSecret == '' || $isValidSecureHash) {

			if($success_code == "0") {
							
				$message = "Your Payment was successful. Payment Ref: " . $payment_ref;
				$asiapay->validateOrder(
				$order_id, 
				Configuration::get('PS_OS_PAYMENT'), 
				$amount, 
				$asiapay->displayName,  
				$message, 
				array("transaction_id" => $payment_ref), 
				$currency_id, 
				false, 
				$customer->secure_key);
				//echo " - Accepted";
				$result = $result." - Accepted";
				
			} else {
				
				$message = "Your Payment was rejected. Payment Ref: " . $payment_ref;
				$asiapay->validateOrder(
				$order_id, 
				Configuration::get('PS_OS_ERROR'), 
				$amount, 
				$asiapay->displayName,  
				$message, 
				array("transaction_id" => $payment_ref), 
				$currency_id, 
				false, 
				$customer->secure_key);
				$result = $result." - Rejected";
				
			}

		} else {
			
			$result = $result." - Invalid SecureHash";
		}
		
		$this->context->smarty->assign(array(
				'result' 		=> $result
            ));
			
		return $this->setTemplate('module:asiapay/views/templates/front/datafeed.tpl');
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

}
