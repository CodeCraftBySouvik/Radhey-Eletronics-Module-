<!DOCTYPE html>
<html>
<head>
	<title>{{ $invoice->invoice_no }}</title>
</head>
<body >
	<table border="1" style="width: 100%; margin: 0 auto; border-collapse: collapse;" cellpadding="0" cellspacing="0" id="invoice_table">
		<tr>
			<td colspan="10">
				<table border="1" style="width: 100%; border-collapse: collapse;" cellpadding="10" cellspacing="0">
					<tr>
						<td rowspan="2" style="font-size: 12px;">
							<p style="margin: 0">Sender</p>
							<p style="margin: 0;"><strong>AGNI</strong><br/>
						</td>
						<td style="font-size: 12px;">							
							<p style="margin: 0;">Invoice No:<br/>
								<strong>{{$invoice->invoice_no}}</strong>
							</p>
						</td>
						<td style="font-size: 12px;">
							<p style="margin: 0;">Invoice Date:<br/><strong>{{ date('d-M-Y',strtotime($invoice->created_at)) }}</strong></p>
						</td>
					</tr>
					<tr>
						<td align="center" style="font-size: 12px;">
							<p style="margin: 0;">Order No: {{$invoice->order->order_no}}</p>
							<p style="margin: 0;">Order Date: {{ date('d-M-Y',strtotime($invoice->order->created_at)) }}</p>
						</td>
						<td style="font-size: 12px;">
							&nbsp;
						</td>
					</tr>
					<tr>
						<td style="font-size: 12px;">
							<p style="margin: 0;">Bill To: 
								@if (!empty($invoice->address_outstation)) (OutSation) @endif
								<br/>
								<strong>{{$invoice->store->bussiness_name}}</strong>
								<br/>
								@if(!empty($invoice->mobile))
								<p style="margin: 0;">Mobile : {{$invoice->mobile}}</p>
								@endif
								<p style="margin: 0;">WhatsApp : {{$invoice->store->whatsapp}}</p>								
                            	{{$invoice->store->billing_address}}
								<br/>
								{{$invoice->store->billing_landmark}}
								<br/>
								{{$invoice->store->billing_state}}
								<br/>
								{{$invoice->store->billing_city}}
								<br/>
								{{$invoice->store->billing_pin}}
                            </p>
							
						</td>
						<td style="font-size: 12px;">
							<p style="margin: 0;">Ship To: 
								@if (!empty($invoice->address_outstation)) (OutSation) @endif 
								<br/>
								<strong>{{$invoice->store->bussiness_name}}</strong>
								<br/>
								@if(!empty($invoice->user->mobile))
								<p style="margin: 0;">Mobile : {{$invoice->user->mobile}}</p>
								@endif
								<p style="margin: 0;">WhatsApp : {{$invoice->store->whatsapp}}</p>
								
                            	{{$invoice->store->shipping_address}}
								<br/>
								{{$invoice->store->shipping_landmark}}
								<br/>
								{{$invoice->store->shipping_state}}
								<br/>
								{{$invoice->store->shipping_city}}
								<br/>
								{{$invoice->store->shipping_pin}}
                            </p>
							
						</td>
						<td style="font-size: 12px;">
                            &nbsp;
						</td>
					</tr>
				</table>
			</td>
		</tr>
		<tr>
			<th align="center" style="font-size: 12px;">Sl No.</th>
			<th align="center" style="font-size: 12px;">Descriptions of Goods</th>
			<th align="center" style="font-size: 12px;">Total Ctns</th>
			<th align="center" style="font-size: 12px;">Total Pcs</th>
			<th align="center" style="font-size: 12px;">Price per Piece (Exc.Tax)</th>
			<th align="center" style="font-size: 12px;">Total Amount (Exc.Tax)</th>
			<th align="center" style="font-size: 12px;">HSN Code</th>
			@if (!empty($invoice->store_address_outstation))
			<th align="center" style="font-size: 12px;">IGST</th>   
			@else
			<th align="center" style="font-size: 12px;">CGST</th> 
			<th align="center" style="font-size: 12px;">SGST</th>
			@endif
			<th align="center" style="font-size: 12px;">Total Amount (Inc.Tax)</th>
		</tr>
		@php
			$i=1;
			$sum_count = 0;
			$total_invoice_price = 0;
		@endphp
		@forelse ($invoice->products as $items)      

		@php
			$sum_count += $items->quantity;						
			$total_invoice_price += $items->total_price;

			
		@endphp
		<tr>
			<td align="center" style="font-size: 12px;">{{$i}}</td>
			<td align="center" style="font-size: 12px;"><strong>{{$items->product_name}}</strong></td>
			<td align="center" style="font-size: 12px;">{{$items->quantity}}</td>	
			<td align="center" style="font-size: 12px;">{{$items->pcs}}</td>					
			<td align="center" style="font-size: 12px;">Rs. {{ number_format((float)$items->price, 2, '.', '') }}</td>
			<td align="center" style="font-size: 12px;">Rs. {{ number_format((float)$items->count_price, 2, '.', '') }}</td>	
			<td align="center" style="font-size: 12px;">{{$items->hsn_code}}</td>
			@if (!empty($invoice->store_address_outstation))
			<td align="center" style="font-size: 12px;">
				{{$items->igst}} %
			</td>
			@else
			<td align="center" style="font-size: 12px;">
				{{$items->cgst}} %                          
			</td>
			<td align="center" style="font-size: 12px;">
				{{$items->sgst}} %
			</td>
			@endif
			<td align="center" style="font-size: 12px;">
				Rs. {{ number_format((float)$items->total_price, 2, '.', '')}}
			</td>
		</tr>
		
		@php
			$i++;
		@endphp
		@empty
		<tr>
			<td colspan="10">No items available</td>
		</tr>    
		@endforelse
		
		<tr>
			<td></td>
			<td align="center" style="font-size: 12px;"><strong> Invoice Amount</strong></td>
			<td colspan="7" style="font-size: 12px;">&nbsp;</td>
			<td align="center" style="font-size: 12px;"><strong>Rs. {{ number_format((float)$total_invoice_price, 2, '.', '') }}</strong> </td>
		</tr>
		<tr>
			<td colspan="10">
				<table border="0" style="width: 100%; border-collapse: collapse;" cellpadding="10" cellspacing="0">
					<tr>
						<td valign="top">
							<p style="margin: 0;">Amount Chargeable (in words)<br/><strong>INR {{getAmountAlphabetically($total_invoice_price)}}</strong></p>
						</td>
						<td align="right" valign="top"><h4 style="margin: 0;">E. & O.E</h4></td>
					</tr>
					<tr>
						<td style="width: 50%;">
							<p style="margin: 0;"><u>Declaration</u></p>
							<p style="margin: 0;">1. All claims, if any, for shortages or damages must be reported to customer service on the day of delivery through the contact us page on the web store 2. All Disputes are subject to Maharashtra (27) jurisdiction only.</p>
						</td>
						<td align="center" style="width: 50%; border-top: 1px solid #000; border-left: 1px solid #000;">
							<h3>AGNI International</h3>
							<h3>Authorised Signatory</h3>
						</td>
					</tr>
				</table>
			</td>
		</tr>
		<tr>
			<td colspan="10">
				<table border="0" style="width: 100%; border-collapse: collapse;" cellpadding="10" cellspacing="0">
					<tr>
						<td style="width: 49%;"><p style="margin: 0;"><strong>Bill By</strong></td>
						<td style="width: 2%;" align="center">:</td>
						<td style="width: 49%;"></td>
					</tr>
				</table>
			</td>
		</tr>
	</table>
    <script>
                
    </script>
</body>
</html>