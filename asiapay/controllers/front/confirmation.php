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

class AsiapayConfirmationModuleFrontController extends ModuleFrontController
{
    public function postProcess()
    {
		$cart_id = $_REQUEST['cart_id'];
		$cart = new Cart($cart_id);

		$customer = new Customer(intval($cart->id_customer));

		$asiapay = new asiapay();

		if(isset($_REQUEST['success'])) {
			
			$id_order = Order::getOrderByCartId((int)$cart_id);
			$customer = new Customer(intval($cart->id_customer));
			
			Tools::redirect('index.php?controller=order-confirmation&id_cart='.(int)($cart_id).'&id_module='.(int)($asiapay->id).'&id_order='.$id_order.'&key='.$customer->secure_key);
			
		} else if(isset($_REQUEST['fail'])) {
			
			$id_order = Order::getOrderByCartId((int)$cart_id);
			
			Tools::redirect('index.php?controller=order-confirmation&id_cart='.(int)($cart_id).'&id_module='.(int)($asiapay->id).'&id_order='.$id_order.'&key='.$customer->secure_key);
			
		} else if(isset($_REQUEST['cancel'])){
			
			Tools::redirect("index.php?controller=order&step=3", __PS_BASE_URI__, null);

		}
		
        
    }
}
