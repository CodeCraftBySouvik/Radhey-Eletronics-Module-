<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>LEDGER | AGNI</title>    
</head>
<body>
    @php
        if($user_type == 'store'){
            $user_type = "Customer";
        } 
    @endphp
    <h3 style="margin: 0;">
        <span style="margin: 0;">{{ucwords($user_type)}} : </span><strong style="margin: 0;">{{ urldecode($select_user_name) }}</strong>
    </h3>
    <h4 style="margin: 0;">
        <span style="margin: 0;">From :  <strong style="margin: 0;">{{ date('d/m/Y', strtotime($from_date)) }}</strong></span>
        <span> - </span>
        <span style="margin: 0;">To :  <strong style="margin: 0;">{{ date('d/m/Y', strtotime($to_date)) }}</strong></span>
    </h4>
    
    <table border="1" style="width: 100%; border-collapse: collapse;" cellpadding="0" cellspacing="0">
        <thead>
            <th>Date</th>
            <th>Purpose</th>
            <th>Description</th>
            <th>Debit</th>
            <th>Credit</th>
            <th>Closing</th>
        </thead>
        <tbody>
            @php
                $net_value = 0;
                $cred_ob_amount = $deb_ob_amount = $zero_ob_amount = "";
                $getCrDrOB = getCrDr($day_opening_amount);
                if($getCrDrOB == 'Cr'){
                    $cred_ob_amount = $day_opening_amount;
                } else if($getCrDrOB == 'Dr'){
                    $deb_ob_amount = $day_opening_amount;
                } else if($getCrDrOB == ''){
                    $zero_ob_amount = "";
                }
                
                if(!empty($is_opening_bal_showable)){
                    $net_value += $day_opening_amount;
                }
                
                
                
            @endphp
            @if ( !empty($is_opening_bal_showable))
            <tr>
                <td align="center">{{ date('d/m/Y', strtotime($from_date)) }}</td>
                <td align="center">Opening Balance</td>
                <td align="center">Day Opening Balance</td>
                <td align="center">{{ replaceMinusSign($deb_ob_amount) }} </td>
                <td align="center">{{ $cred_ob_amount }} </td>
                <td align="center">                            
                    {{ replaceMinusSign($day_opening_amount) }} 
                    {{ getCrDr($day_opening_amount) }}
                </td>
            </tr>
            
            @endif
            
            @foreach ($data as $item)

            @php
                $creditAmount = $debitAmount = '';
                if(!empty($item->is_credit)){
                    $creditAmount = $item->transaction_amount;
                    $net_value += $item->transaction_amount;
                }
                if(!empty($item->is_debit)){
                    $debitAmount = $item->transaction_amount;
                    $net_value -= $item->transaction_amount;
                }
                $show_payment_mode = "( ".ucwords($item->bank_cash)." )";
            @endphp
            <tr>
                <td align="center">{{date('d/m/Y', strtotime($item->entry_date))}}</td>
                <td align="center">{{ ucwords(str_replace("_"," ",$item->purpose)) }} {{$show_payment_mode}}</td>  
                <td align="center">{{ ucwords($item->purpose_description) }}</td>
                <td align="center">{{$debitAmount}}</td>
                <td align="center">{{$creditAmount}}</td>
                <td align="center">
                    {{-- {{$item->last_closing_amount}} {{getCrDr($item->last_closing_amount)}} --}}
                    {{ replaceMinusSign($net_value) }} {{getCrDr($net_value)}}
                </td>
            </tr>    
            @endforeach             
        </tbody>
    </table>
</body>
</html>