<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>SALES | AGNI</title>    
</head>
<body>
   
    <h4 style="margin: 0;">
        <span style="margin: 0;">From :  <strong style="margin: 0;">{{ date('d/m/Y', strtotime($from_date)) }}</strong></span>
        <span> - </span>
        <span style="margin: 0;">To :  <strong style="margin: 0;">{{ date('d/m/Y', strtotime($to_date)) }}</strong></span>
    </h4>
    
    <table border="1" style="width: 100%; border-collapse: collapse;" cellpadding="0" cellspacing="0">
        <thead>
            <th>Date</th>
            <th>Order No</th>
            <th>Store</th>
            <th>Amount</th>
            {{-- <th>Status</th> --}}
        </thead>
        <tbody>                        
            @foreach ($orders as $order)
            <tr>
                <td align="center" style="padding: 5px;">{{ date('d/m/Y', strtotime($order->created_at)) }}</td>
                <td align="center" style="padding: 5px;">{{ $order->order->order_no }}</td>    
                <td align="center" style="padding: 5px;">{{ $order->store->bussiness_name }}</td>  
                <td align="center" style="padding: 5px;">Rs. {{ number_format((float)$order->net_price, 2, '.', '') }}</td>
                {{-- <td align="center" style="padding: 5px;">
                    @if (!empty($order->packingslip))
                        @if (!empty($order->packingslip->is_disbursed))
                            <span class="badge bg-success">DISBURSED</span>
                        @else
                            <span class="badge bg-warning">YET TO DISBURSE</span>
                        @endif
                    @else
                        <span class="badge bg-warning">YET TO DISBURSE</span>
                    @endif
                </td> --}}
            </tr> 
            <tr style="width: 100%">
                <td colspan="5" style="padding: 10px 0px;">
                    <table border="1" style="width: 100%; border-collapse: collapse;" cellpadding="0" cellspacing="0">
                        <thead>
                            <th>#</th>
                            <th>Product</th>
                            <th>Quantity</th>
                            <th>Price per Pcs</th>
                            <th>Total Price</th>
                        </thead>
                        <tbody>
                            @php
                                $j = 1;
                            @endphp
                            @forelse ($order->products as $item)
                            @php
                                // $total_price = ($item->pcs * $item->single_product_price);
                            @endphp
                            <tr>
                                <td style="padding: 5px;">{{$j}}</td>
                                <td style="padding: 5px;">{{$item->product_name}}</td>
                                <td style="padding: 5px;">{{$item->quantity}} ctns ({{$item->pcs}} pcs)</td>
                                <td style="padding: 5px;">Rs. {{ number_format((float)$item->single_product_price, 2, '.', '') }}</td>
                                <td style="padding: 5px;">
                                    Rs. {{ number_format((float)$item->total_price, 2, '.', '') }}
                                </td>
                            </tr>
                            @php
                                $j++;
                            @endphp
                            @empty
                                
                            @endforelse
                            
                        </tbody>
                    </table>
                </td>
                
            </tr>   
            @endforeach             
        </tbody>
    </table>
</body>
</html>