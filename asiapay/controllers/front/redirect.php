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
			// $order = new Order($cart->id);
			// $customer = $this->context->customer;
			$customerShipAddress = $this->context->customer->getSimpleAddress($cart->id_address_delivery);
			$customerBillAddress = $this->context->customer->getSimpleAddress($cart->id_address_invoice);


			$customer = new Customer((int) $cart->id_customer);
			$shipCountryDetl = new Country((int) $customerShipAddress['id_country']);
			$billCountryDetl = new Country((int) $customerBillAddress['id_country']);

			$isGuest = $customer->is_guest;
			
			$customer_ship_details = new Address((int)($cart->id_address_delivery));
			$customer_ship_date_up = $customer_ship_details->date_upd;

			$customer_ship_update_date = date('Ymd' , strtotime($customer_ship_date_up));
			$customer_ship_updaydiff = $this->getDateDiff($customer_ship_update_date);
			$customer_ship_ageind =$this->getAcctAgeInd($customer_ship_updaydiff);

			// $customer = new Customer((int)($delivery_details->id_customer));
			$history = new Order();

			$customer_order_history = $history->getCustomerOrders($cart->id_customer,true);

			$customer_stats = $customer->getStats();

			$customer_acctAuthDate = gmdate("Ymd" , strtotime($customer_stats['last_visit']));
			// echo "<pre>";
			
			// echo "<br>";
			// print_r($cart);
			// print_r($customerShipAddress);



			$timeQ24 = date('Y-m-d H:i:s', strtotime("-1 day"));
			$timeQ6 = date('Y-m-d H:i:s', strtotime("-6 months"));
			$timeQ1 = date('Y-m-d H:i:s', strtotime("-1 year"));


			$countOrderAnyDay = $countOrder = $countOrderAnyYear = 0;

			foreach ($customer_order_history as $key => $value) {
				// if()
				$dte = $value['invoice_date'];
				if($dte >= $timeQ24)$countOrderAnyDay++;
				if($dte >= $timeQ6)$countOrder++;
				if($dte >= $timeQ1)$countOrderAnyYear++;
			}

			
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

			if($cart->id_address_delivery == $cart->id_address_invoice){
				$diffAdd = "T";
			}else{
				$diffAdd = "F";
			}

			$shippingDetl = ($diffAdd=="T")?'01':'03';


			//3ds variables
			$txnType			= Configuration::get('TRANSACTION_TYPE');
			$txnChallengePref	= Configuration::get('CHALLENGE_PREFERENCE');
			
			$threeDSCustomerEmail = $customer->email;
			$custBillAddress = preg_replace('/\D/', '',$customerBillAddress['phone']);
			$custShipAddress = preg_replace('/\D/', '',$customerShipAddress['phone']);
			
			$customer_bill_phonenum = $custBillAddress;
			$customer_bill_phonecountryCode = $billCountryDetl->call_prefix;
			$customer_bill_city = $customerBillAddress['city'];
			$customer_bill_street1 = $customerBillAddress['address1'];
			$customer_bill_street2 = $customerBillAddress['address2'];
			$customer_bill_postcode = $customerBillAddress['postcode'];
			$customer_bill_statecode = $billCountryDetl->iso_code;
			$customer_bill_countryID = $this->getCountryCodeNumeric($customer_bill_statecode);


			$customer_ship_phonenum = $custShipAddress;
			$customer_ship_city = $customerShipAddress['city'];
			$customer_ship_street1 = $customerShipAddress['address1'];
			$customer_ship_street2 = $customerShipAddress['address2'];
			$customer_ship_postcode = $customerShipAddress['postcode'];
			$customer_ship_statecode = $shipCountryDetl->iso_code;
			$customer_ship_countryID = $this->getCountryCodeNumeric($customer_ship_statecode);

			$customer_create_date = date('Ymd' , strtotime($customer->date_add));
			$customer_adddaydiff = $this->getDateDiff($customer_create_date);
			$customer_acct_ageind =$this->getAcctAgeInd($customer_adddaydiff);

			$customer_update_date = date('Ymd' , strtotime($customer->date_upd));
			$customer_updaydiff = $this->getDateDiff($customer_create_date);
			$customer_update_ageind =$this->getAcctAgeInd($customer_updaydiff);

			$customer_acctAuthMethod = "02";
			if($isGuest){
				$customer_create_date = $customer_acct_ageind = $customer_update_date = $customer_update_ageind = "";
				$customer_acctAuthMethod = "01";
			}


			$customer_ship_name_same = "F";

			if(($customer->firstname == $customerShipAddress['firstname']) && ($customer->lastname == $customerShipAddress['lastname'])){
				$customer_ship_name_same = "T";
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
				'failRetry'			=> 'no',

							//for 3ds2.0
				//Basic Parameters Customer Info
				'threeDSTransType'				=> $txnType,
				'threeDSCustomerEmail'			=> $threeDSCustomerEmail,
				'threeDSMobilePhoneCountryCode' => $customer_bill_phonecountryCode,
				'threeDSMobilePhoneNumber' 		=> $customer_bill_phonenum,
				'threeDSHomePhoneCountryCode'	=> $customer_bill_phonecountryCode,
				'threeDSHomePhoneNumber'		=> $customer_bill_phonenum,
				'threeDSWorkPhoneCountryCode' 	=> $customer_bill_phonecountryCode,
				'threeDSWorkPhoneNumber'		=> $customer_bill_phonenum,
				// 'threeDSIsFirstTimeItemOrder'	=> '',
				'threeDSChallengePreference'	=> $txnChallengePref,

				// //recurring payment related
				// 'threeDSRecurringFrequency'		=>'',
				// 'threeDSRecurringExpiry'		=>'',

				// //Billing address related
				'threeDSBillingCountryCode'		=> $customer_bill_countryID,
				'threeDSBillingState'			=> $customer_bill_statecode,
				'threeDSBillingCity' 			=> $customer_bill_city,
				'threeDSBillingLine1' 			=> $customer_bill_street1,
				'threeDSBillingLine2'			=> $customer_bill_street2,
				// 'threeDSBillingLine3'			=> $customer_bill_street2,
				'threeDSBillingPostalCode' 		=> $customer_bill_postcode,

				// //Shipping / Delivery Related
				// 'threeDSDeliveryTime'			=> '',
				'threeDSDeliveryEmail'			=> $threeDSCustomerEmail,
				'threeDSShippingDetails' 		=> $shippingDetl,
				'threeDSShippingCountryCode' 	=> $customer_ship_countryID,
				'threeDSShippingState'			=> $customer_ship_statecode,
				'threeDSShippingCity'			=> $customer_ship_city,
				'threeDSShippingLine1'			=> $customer_ship_street1,
				'threeDSShippingLine2' 			=> $customer_ship_street2,
				// 'threeDSShippingLine3'			=> $customer_ship_street2,
				'threeDSShippingPostalCode'		=> $customer_ship_postcode,
				'threeDSIsAddrMatch'			=> $diffAdd,


				// //Gift Card / Prepaid Card Purchase Related
				// 'threeDSGiftCardAmount'			=> '',
				// 'threeDSGiftCardCurr'			=> '',
				// 'threeDSGiftCardCount'			=> '',


				// //Pre-Order Purchase Related
				// 'threeDSPreOrderReason'			=> '',
				// 'threeDSPreOrderReadyDate'		=> '',

				// //Account Info Related
				'threeDSAcctCreateDate'					=> $customer_create_date,
				'threeDSAcctAgeInd'						=> $customer_acct_ageind,
				'threeDSAcctLastChangeDate' 			=> $customer_update_date,
				'threeDSAcctLastChangeInd' 				=> $customer_update_ageind,
				// 'threeDSAcctPwChangeDate'				=> '',
				// 'threeDSAcctPwChangeInd'				=> '',
				'threeDSAcctPurchaseCount' 				=> $countOrder,
				// 'threeDSAcctCardProvisionAttempt'		=> '',
				'threeDSAcctNumTransDay'				=> $countOrderAnyDay,
				'threeDSAcctNumTransYear'				=> $countOrderAnyYear,
				// 'threeDSAcctPaymentAcctDate' 			=> '',
				// 'threeDSAcctPaymentAcctInd' 			=> '',
				'threeDSAcctShippingAddrLastChangeDate'	=> $customer_ship_update_date,
				'threeDSAcctShippingAddrLastChangeInd'	=> $customer_ship_ageind,
				'threeDSAcctIsShippingAcctNameSame'		=> $customer_ship_name_same,
				// 'threeDSAcctIsSuspiciousAcct'			=> '',

				// //Account Authentication Info Related
				'threeDSAcctAuthMethod'			=> $customer_acctAuthMethod,
				'threeDSAcctAuthTimestamp'		=> $customer_acctAuthDate,

				// //Pay Token Related 
				// 'threeDSPayTokenInd'			=> '',
            ));
// exit;
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

	function getCountryCodeNumeric($code){
		$countrycode = array('AF'=>'4','AL'=>'8','DZ'=>'12','AS'=>'16','AD'=>'20','AO'=>'24','AI'=>'660','AQ'=>'10','AG'=>'28','AR'=>'32','AM'=>'51','AW'=>'533','AU'=>'36','AT'=>'40','AZ'=>'31','BS'=>'44','BH'=>'48','BD'=>'50','BB'=>'52','BY'=>'112','BE'=>'56','BZ'=>'84','BJ'=>'204','BM'=>'60','BT'=>'64','BO'=>'68','BO'=>'68','BA'=>'70','BW'=>'72','BV'=>'74','BR'=>'76','IO'=>'86','BN'=>'96','BN'=>'96','BG'=>'100','BF'=>'854','BI'=>'108','KH'=>'116','CM'=>'120','CA'=>'124','CV'=>'132','KY'=>'136','CF'=>'140','TD'=>'148','CL'=>'152','CN'=>'156','CX'=>'162','CC'=>'166','CO'=>'170','KM'=>'174','CG'=>'178','CD'=>'180','CK'=>'184','CR'=>'188','CI'=>'384','CI'=>'384','HR'=>'191','CU'=>'192','CY'=>'196','CZ'=>'203','DK'=>'208','DJ'=>'262','DM'=>'212','DO'=>'214','EC'=>'218','EG'=>'818','SV'=>'222','GQ'=>'226','ER'=>'232','EE'=>'233','ET'=>'231','FK'=>'238','FO'=>'234','FJ'=>'242','FI'=>'246','FR'=>'250','GF'=>'254','PF'=>'258','TF'=>'260','GA'=>'266','GM'=>'270','GE'=>'268','DE'=>'276','GH'=>'288','GI'=>'292','GR'=>'300','GL'=>'304','GD'=>'308','GP'=>'312','GU'=>'316','GT'=>'320','GG'=>'831','GN'=>'324','GW'=>'624','GY'=>'328','HT'=>'332','HM'=>'334','VA'=>'336','HN'=>'340','HK'=>'344','HU'=>'348','IS'=>'352','IN'=>'356','ID'=>'360','IR'=>'364','IQ'=>'368','IE'=>'372','IM'=>'833','IL'=>'376','IT'=>'380','JM'=>'388','JP'=>'392','JE'=>'832','JO'=>'400','KZ'=>'398','KE'=>'404','KI'=>'296','KP'=>'408','KR'=>'410','KR'=>'410','KW'=>'414','KG'=>'417','LA'=>'418','LV'=>'428','LB'=>'422','LS'=>'426','LR'=>'430','LY'=>'434','LY'=>'434','LI'=>'438','LT'=>'440','LU'=>'442','MO'=>'446','MK'=>'807','MG'=>'450','MW'=>'454','MY'=>'458','MV'=>'462','ML'=>'466','MT'=>'470','MH'=>'584','MQ'=>'474','MR'=>'478','MU'=>'480','YT'=>'175','MX'=>'484','FM'=>'583','MD'=>'498','MC'=>'492','MN'=>'496','ME'=>'499','MS'=>'500','MA'=>'504','MZ'=>'508','MM'=>'104','MM'=>'104','NA'=>'516','NR'=>'520','NP'=>'524','NL'=>'528','AN'=>'530','NC'=>'540','NZ'=>'554','NI'=>'558','NE'=>'562','NG'=>'566','NU'=>'570','NF'=>'574','MP'=>'580','NO'=>'578','OM'=>'512','PK'=>'586','PW'=>'585','PS'=>'275','PA'=>'591','PG'=>'598','PY'=>'600','PE'=>'604','PH'=>'608','PN'=>'612','PL'=>'616','PT'=>'620','PR'=>'630','QA'=>'634','RE'=>'638','RO'=>'642','RU'=>'643','RU'=>'643','RW'=>'646','SH'=>'654','KN'=>'659','LC'=>'662','PM'=>'666','VC'=>'670','VC'=>'670','VC'=>'670','WS'=>'882','SM'=>'674','ST'=>'678','SA'=>'682','SN'=>'686','RS'=>'688','SC'=>'690','SL'=>'694','SG'=>'702','SK'=>'703','SI'=>'705','SB'=>'90','SO'=>'706','ZA'=>'710','GS'=>'239','ES'=>'724','LK'=>'144','SD'=>'736','SR'=>'740','SJ'=>'744','SZ'=>'748','SE'=>'752','CH'=>'756','SY'=>'760','TW'=>'158','TW'=>'158','TJ'=>'762','TZ'=>'834','TH'=>'764','TL'=>'626','TG'=>'768','TK'=>'772','TO'=>'776','TT'=>'780','TT'=>'780','TN'=>'788','TR'=>'792','TM'=>'795','TC'=>'796','TV'=>'798','UG'=>'800','UA'=>'804','AE'=>'784','GB'=>'826','US'=>'840','UM'=>'581','UY'=>'858','UZ'=>'860','VU'=>'548','VE'=>'862','VE'=>'862','VN'=>'704','VN'=>'704','VG'=>'92','VI'=>'850','WF'=>'876','EH'=>'732','YE'=>'887','ZM'=>'894','ZW'=>'716');
		return $countrycode[$code];

	}

	function getDateDiff($d){
    		$datenow = date('Ymd');
			$dt1 = new \DateTime($datenow);
			$dt2 = new \DateTime($d);
			$interval = $dt1->diff($dt2)->format('%a');
			return $interval;
    }

	function getAcctAgeInd($d){
    	switch ($d) {
    		case 0:
    			# code...
    			$ret = "02";
    			break;
    		case $d<30:
    			# code...
    			$ret = "03";
    			break;
    		case $d>30 && $d<60:
    			# code...
    			$ret = "04";
    			break;
    		case $d>60:
    			$ret = "05"	;
				break;	
    		default:
    			# code...
    			break;
    	}
    	return $ret;

    }

    
}
