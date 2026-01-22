<!DOCTYPE html>
<html>
<head>
	<title>{{ $packingslip->slipno }}</title>
</head>
<body>
    <table id="packing_table" style="width: 100%; border-collapse: collapse;" border="1" cellpadding="0" cellspacing="0">
        <tr>
            <th colspan="4"><h3>PACKING SLIP</h3></th>
        </tr>
        <tr>
            <td colspan="2" style="padding: 20px; border-right: 1px solid #000;">
                {{$packingslip->store->bussiness_name}}
            </td>
            <td colspan="2" style="padding: 20px;">{{$packingslip->slipno}}<br/>{{date('d/m/Y', strtotime($packingslip->updated_at))}}</td>
        </tr>
        <tr style="border-top: 1px solid #000;">
            <th style="padding: 20px; border-bottom: 1px solid #000; text-align: left;">Descriptions of goods</th>
            <th style="padding: 20px; border-bottom: 1px solid #000; border-left: 1px solid #000; border-right: 1px solid #000;">Ctns</th>
            {{-- <th style="padding: 20px; border-bottom: 1px solid #000;">Pcs per Ctn</th> --}}
            <th style="padding: 20px; border-bottom: 1px solid #000; white-space: nowrap;">Total Pieces</th>
            <th style="padding: 20px; border-bottom: 1px solid #000;">Units</th>
        </tr>
        @php
            $total_no_ctn = $total_pcs = 0;
        @endphp
        @forelse ($data as $item)
        @php
            $total_no_ctn += $item->quantity;
            $total_pcs += $item->pcs;

        @endphp
        <tr>
            <td style="padding: 8px 20px;">{{$item->pro_name}}</td>
            <td style="padding: 8px 20px; text-align: center; border-left: 1px solid #000; border-right: 1px solid #000;">{{$item->quantity}}</td>
            <td style="padding: 8px 20px; text-align: center;">{{$item->pcs}}</td>
            <td style="padding: 8px 20px; text-align: center;">Pieces</td>
        </tr>    
        @empty
        <tr>
            <td colspan="4">No items is there</td>                            
        </tr>    
        @endforelse
        <tr style="border-top: 1px solid #000;">
            <td style="padding: 8px 20px;">Total No of Ctns</td>
            <td style="padding: 8px 20px; text-align: center; border-left: 1px solid #000; border-right: 1px solid #000;">{{$total_no_ctn}}</td>
            <td style="padding: 8px 20px; text-align: center;">{{$total_pcs}}</td>
            <td style="padding: 8px 20px; text-align: center;"></td>
            {{-- <td style="padding: 20px; text-align: center;"></td> --}}
        </tr>
        {{-- <tr>
            <td>
                <table style="width: 100%; border-collapse: collapse;" cellpadding="0" cellspacing="0">
                    <thead>
                        
                    </thead>
                    <tbody style="height: 400px; vertical-align: top;">
                                              

                    </tbody>
                    <tfoot>
                        
                    </tfoot>
                </table>
            </td>
        </tr> --}}
    </table>
    <script>
        
    </script>
</body>
</html>