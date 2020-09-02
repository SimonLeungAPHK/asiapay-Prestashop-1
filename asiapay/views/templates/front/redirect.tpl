{*
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
*}


<h3>{l s='You have chosen to pay using PayDollar/PesoPay/SiamPay Payment Service.' mod='asiapay'}</h3>

<form id="asiapay_form" name="checkout_confirmation" action="{$paymentUrl}" method="post" />
    <input type="hidden" name="merchantId" value="{$merchantId}">
	<input type="hidden" name="lang" value="{$a_language}" />
    <input type="hidden" name="currCode" value="{$a_currency}" />
    <input type="hidden" name="payType" value="{$payType}" />
    <input type="hidden" name="payMethod" value="{$payMethod}" />
    
    
    <input type="hidden" name="amount" value="{$amount}" />
    <input type="hidden" name="orderRef" value="{$orderRef}" />
    <input type="hidden" name="remark" value="{$remark}" />
    
    <input type="hidden" name="successUrl" value="{$successUrl}" />
    <input type="hidden" name="failUrl" value="{$failUrl}" />
    <input type="hidden" name="cancelUrl" value="{$cancelUrl}" />
    
    <input type="hidden" name="secureHash" value="{$secureHash}" />
    <input type="hidden" name="failRetry" value="{$failRetry}" />  

    <input type="hidden" name="threeDSTransType" value="{$threeDSTransType}" />
    <input type="hidden" name="threeDSChallengePreference" value="{$threeDSChallengePreference}" />
    <input type="hidden" name="threeDSCustomerEmail" value="{$threeDSCustomerEmail}" />
    <input type="hidden" name="threeDSDeliveryEmail" value="{$threeDSDeliveryEmail}" />
    <input type="hidden" name="threeDSMobilePhoneCountryCode" value="{$threeDSMobilePhoneCountryCode}" />
    <input type="hidden" name="threeDSMobilePhoneNumber" value="{$threeDSMobilePhoneNumber}" />
    <input type="hidden" name="threeDSHomePhoneCountryCode" value="{$threeDSHomePhoneCountryCode}" />
    <input type="hidden" name="threeDSHomePhoneNumber" value="{$threeDSHomePhoneNumber}" />
    <input type="hidden" name="threeDSWorkPhoneCountryCode" value="{$threeDSWorkPhoneCountryCode}" />
    <input type="hidden" name="threeDSWorkPhoneNumber" value="{$threeDSWorkPhoneNumber}" />

    <input type="hidden" name="threeDSAcctCreateDate" value="{$threeDSAcctCreateDate}" />
    <input type="hidden" name="threeDSAcctAgeInd" value="{$threeDSAcctAgeInd}" />
    <input type="hidden" name="threeDSAcctPurchaseCount" value="{$threeDSAcctPurchaseCount}" />
    <input type="hidden" name="threeDSAcctNumTransDay" value="{$threeDSAcctNumTransDay}" />
    <input type="hidden" name="threeDSAcctNumTransYear" value="{$threeDSAcctNumTransYear}" />
    <input type="hidden" name="threeDSAcctIsShippingAcctNameSame" value="{$threeDSAcctIsShippingAcctNameSame}" />
    <input type="hidden" name="threeDSAcctLastChangeDate" value="{$threeDSAcctLastChangeDate}" />
    <input type="hidden" name="threeDSAcctLastChangeInd" value="{$threeDSAcctLastChangeInd}" />
    <input type="hidden" name="threeDSAcctShippingAddrLastChangeInd" value="{$threeDSAcctShippingAddrLastChangeInd}" />
    <input type="hidden" name="threeDSAcctShippingAddrLastChangeDate" value="{$threeDSAcctShippingAddrLastChangeDate}" />

    <input type="hidden" name="threeDSBillingCountryCode" value="{$threeDSBillingCountryCode}" />
    <input type="hidden" name="threeDSBillingState" value="{$threeDSBillingState}" />
    <input type="hidden" name="threeDSBillingCity" value="{$threeDSBillingCity}" />
    <input type="hidden" name="threeDSBillingLine1" value="{$threeDSBillingLine1}" />
    <input type="hidden" name="threeDSBillingLine2" value="{$threeDSBillingLine2}" />
    <input type="hidden" name="threeDSBillingPostalCode" value="{$threeDSBillingPostalCode}" />

    <input type="hidden" name="threeDSShippingCountryCode" value="{$threeDSShippingCountryCode}" />
    <input type="hidden" name="threeDSShippingState" value="{$threeDSShippingState}" />
    <input type="hidden" name="threeDSShippingCity" value="{$threeDSShippingCity}" />
    <input type="hidden" name="threeDSShippingLine1" value="{$threeDSShippingLine1}" />
    <input type="hidden" name="threeDSShippingLine2" value="{$threeDSShippingLine2}" />
    <input type="hidden" name="threeDSIsAddrMatch" value="{$threeDSIsAddrMatch}" />
    <input type="hidden" name="threeDSShippingDetails" value="{$threeDSShippingDetails}" />
    <input type="hidden" name="threeDSShippingPostalCode" value="{$threeDSShippingPostalCode}" />

    <input type="hidden" name="threeDSAcctAuthMethod" value="{$threeDSAcctAuthMethod}" />          
    <input type="hidden" name="threeDSAcctAuthTimestamp" value="{$threeDSAcctAuthTimestamp}" />

</form>

<script type="text/javascript">
	function submitAsiaPayForm(){		
		document.getElementById('asiapay_form').submit();		
	}
	setTimeout('submitAsiaPayForm()', 3000);
</script>

<p>
	{l s='If you are not automatically redirected, click ' mod='asiapay'}
	<a href="javascript:;" onclick="submitAsiaPayForm();">{l s='here' mod='asiapay'}</a>
</p>

