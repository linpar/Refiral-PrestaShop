{if $isEnabled}
	<!-- Begin Refiral Campaign Code -->
	{literal}
	<script type="text/javascript">var apiKey = '{/literal}{$refiralKey}{literal}';</script>
	<script type="text/javascript">var showButton = {/literal}{$flag_button}{literal};</script>
	<script src="http://www.refiral.com/api/all.js"></script>
	{/literal}
	{if $flag_invoice}
		{literal}
		<script type="text/javascript">invoiceRefiral('{/literal}{$order_subtotal}{literal}', '{/literal}{$order_total}{literal}', '{/literal}{$order_coupon}{literal}', '{/literal}{$order_cart}{literal}', '{/literal}{$order_name}{literal}', '{/literal}{$order_email}{literal}');</script>
		{/literal}
	{/if}
	<!-- End Refiral Campaign Code -->
{/if}