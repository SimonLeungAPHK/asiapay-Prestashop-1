{if $status == 'ok'}
	<p>
		{l s='Your order has been completed. We are preparing to send its product immediately.' mod='asiapay'}<br />
		<br />
		{l s='For any questions or for further information, please contact our ' mod='asiapay'}
		<a href="{$base_dir}contact-form.php">
			{l s='customer support' mod='asiapay'}
		</a>.		
	</p>
{else}
	<p class="warning">
		{l s='We noticed a problem with your order. If you think this is an error, you can contact our ' mod='asiapay'}
		<a href="{$base_dir}contact-form.php">
			{l s='customer support' mod='asiapay'}
		</a>.
	</p>
{/if}