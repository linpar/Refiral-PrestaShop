<?php
// Prevent visitors to load this file directly
if (!defined('_PS_VERSION_'))
	exit;

class Refiral extends Module {

	public function __construct() {
		$this->name = 'refiral';
		$this->tab = version_compare(_PS_VERSION_, '1.4.0.0', '>=')?'advertising_marketing':'Refiral';
		$this->version = '1.0';
		$this->author = 'Refiral';
		$this->need_instance = 0;

		parent::__construct();

		$this->displayName = $this->l('Refiral');
		$this->description = $this->l('Launch your referral campaign virally.');
	}

	function install()
	{
		if (!parent::install() || !$this->registerHook('footer') || !Configuration::updateValue('REFIRALKEY', ''))
			return false;

		return true;
	}

	function uninstall()
	{
		if (!Configuration::deleteByName('REFIRALKEY') || !parent::uninstall())
			return false;
		return true;
	}
	
	// Get subtotal and products info
	private function getCartDetails($cart)
	{
		$subTotal = 0;
		$products_array = array();
		$products = $cart->getProducts();
		foreach($products as $product)
		{
			$subTotal += $product['total_wt'];
			$products_array[] = array("id" => $product['id_product'], "name" => $product['name'], "quantity" => $product['cart_quantity']);
		}
		$subTotal += $cart->getOrderTotal(true, Cart::ONLY_SHIPPING);
		return array("sub_total" => $subTotal, "products" => json_encode($products_array));
	}
	
	function hookFooter($params)
	{
		global $smarty;
		global $cookie;
		$isEnabled = Configuration::get('ENABLECAMPAIGN');
		$refiralKey = Configuration::get('REFIRALKEY');

		if ($refiralKey)
		{
			$smarty->assign(array('refiralKey' => $refiralKey));
			$smarty->assign(array('isEnabled' => $isEnabled));
			$smarty->assign(array('flag_button' => 'true')); // show button

			if($cookie->id_cart)
			{
				$customer = new Customer((int)$params['cart']->id_customer);
				$cookie->email = $customer->email; //Set email id
				$cookie->name = $customer->firstname.$customer->lastname; // Set first name
				
				// Get grand total
				$cart = new Cart($cookie->id_cart);
				$cookie->grandTotal = $cart->getOrderTotal(true);
				
				// Get Coupon Code
				$cartRule = new CartRule();
				$coupons = $cartRule->getCustomerCartRules((int)$params['cart']->id_lang,(int)$params['cart']->id_customer,true,true,false,$cart);
				$cookie->couponCode = $coupons[0]['code'];
				
				// Get subtotal and products info
				$subNproducts = $this->getCartDetails($cart);
				$cookie->cartInfo = $subNproducts['products'];
				$cookie->subTotal = $subNproducts['sub_total'];
			}
			else if(isset($cookie->name))
			{
				$smarty->assign(array('flag_button' => 'false')); // hide button
				$smarty->assign(array('flag_invoice' => 'true')); // send invoice data
				$smarty->assign(array('order_name' => $cookie->name));
				$smarty->assign(array('order_email' => $cookie->email));
				$smarty->assign(array('order_subtotal' => $cookie->subTotal));
				$smarty->assign(array('order_total' => $cookie->grandTotal));
				$smarty->assign(array('order_coupon' => $cookie->couponCode));
				$smarty->assign(array('order_cart' => $cookie->cartInfo));
				unset($cookie->name);
				unset($cookie->email);
				unset($cookie->subTotal);
				unset($cookie->grandTotal);
				unset($cookie->couponCode);
				unset($cookie->cartInfo);
			}

			return $this->display(__FILE__, 'refiral.tpl');
		}
		return $output;
	}
	
	public function getContent()
	{
		$adminHtml = '<h2><img src="'.$this->_path.'logo.gif" alt="" /> '.$this->displayName.'</h2>';
		if (Tools::isSubmit('submit_refiral'))
		{
			$isEnabled = Tools::getValue('enable_campaign');
			Configuration::updateValue('ENABLECAMPAIGN', $isEnabled);

			$refiralKey = Tools::getValue('refiral_key');
			Configuration::updateValue('REFIRALKEY', $refiralKey);

			$adminHtml .= '<div class="conf confirm"><img src="../img/admin/ok.gif" alt="Confirmation" />Settings updated</div>';
		}
		return $adminHtml.$this->displayForm();
	}

	public function displayForm()
	{
		global $cookie;

		$isEnabled = Configuration::get('ENABLECAMPAIGN');
		$refiralKey = Configuration::get('REFIRALKEY');
		
		if ($isEnabled)
        	$enabledHtml = '<option value="1" selected="selected">Yes</option>
							<option value="0">No</option>';
		else
        	$enabledHtml = '<option value="1">Yes</option>
							<option value="0" selected="selected">No</option>';

		$html = '<form action="'.$_SERVER['REQUEST_URI'].'" method="post">
			<p>Sign up at <a href="http://www.refiral.com" target="_blank" style="color:#E6624D" title="Refiral"><strong>Refiral.com</strong></a> to get Refiral key.</p>
			<fieldset>
				<legend><span style="line-height:40px"><a href="http://www.refiral.com" target="_blank" title="Refiral"><img src="http://cdn.refiral.com/main/images/logo.png" alt="Refiral"/></a></span></legend>

				<label>Enable Campaign</label>
				<div class="margin-form">
					<select name="enable_campaign">'.$enabledHtml.'</select>
				</div>
				<p class="margin-form">Select Yes to Enable or No to Disable your Refiral campaign.</p>
				<div class="clear"></div>

				<label>Refiral Key</label>
				<div class="margin-form">
					<input type="text" name="refiral_key" style="width:250px;" value="'.$refiralKey.'" />
				</div>
				<p class="margin-form">Get you Refiral key from Integration page in admin panel of Refiral.</p>
				<div class="clear"></div>
				
				<label>&nbsp;</label>
				<input style="background:#F9745F;padding:13px 15px;border-radius:4px;margin-top:3px;color:#FFF;border:0;" type="submit" name="submit_refiral" value="'.$this->l('Update Settings').'" class="button" />

			</fieldset>
		</form>
		';
		return $html;
	}

}
?>