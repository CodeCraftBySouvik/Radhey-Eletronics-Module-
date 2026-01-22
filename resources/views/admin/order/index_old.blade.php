@extends('admin.layouts.app')

@section('page', 'Order')

@section('content')
<section>
    <div class="search__filter">
        <div class="row align-items-center justify-content-end">
            <div class="col-auto">
                <form action="{{ route('admin.order.index')}}" method="GET">
                    <div class="row g-3 align-items-center">
                        <div class="col-auto">
                            <input type="search" name="term" id="term" class="form-control" placeholder="Search here.." value="{{app('request')->input('term')}}" autocomplete="off">
                        </div>
                        <div class="col-auto">
                            <button type="submit" class="btn btn-outline-danger btn-sm">Search</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="filter">
        <div class="row align-items-center justify-content-end">
            <div class="col-auto">
                <p>{{$data->count()}} records</p>
            </div>
        </div>
    </div>

    <table class="table">
        <thead>
        <tr>
            <th>#SR</th>
            <th>Name</th>
            <th>Payment Mode</th>
            <th>Amount</th>
            <th>Invoice</th>
            <th>Order time</th>
            <th>Status</th>
        </tr>
        </thead>
        <tbody>
            @forelse ($data as $index => $item)
            <tr id="row_{{$item->id}}">
                {{-- <td class="check-column">
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" value="" id="flexCheckDefault">
                        <label class="form-check-label" for="flexCheckDefault"></label>
                    </div>
                </td> --}}
                <td>
                    {{ $index + 1 }}
                </td>
                <td>
                    <p class="small text-dark mb-1">#{{$item->order_no}}</p>
                    <p class="small text-dark mb-1">{{$item->fname.' '.$item->lname}}</p>
					
					<p class="small text-dark mb-1"></p>
					
                    <p class="small text-muted mb-0">{{$item->email.' | '.$item->mobile}}</p>
                    <div class="row__action">
                        <a href="{{ route('admin.order.view', $item->id) }}">View</a>
                    </div>
                </td>
				 <td>
					@if($item->payment_method == "online_payment")

					   <p class="small text-dark mb-1"> Online</p>

					@else
						<p class="small text-dark mb-1"> Cash on Delivery</p>
					@endif
				</td>
                <td>
                    @if ($item->coupon_code_id != 0)
						@if($item->couponDetails)
						<div class="">
							<p class="small text-muted mb-1">Total: {{$item->amount}}</p>
							<p class="small mb-0">Discount: {{$item->coupon_code_discount_type == 'Percentage' ? $item->couponDetails->amount .'%' : 'Rs. ' .$item->couponDetails->amount }}</p>
							<p class="small text-muted mb-1">Final: {{$item->final_amount}}</p>
						</div>
						@endif
					@else
                    <p class="small text-dark mb-1">&#8377; {{ number_format($item->final_amount) }}</p>
                	@endif
                </td>
                @if(($item->status != 0))
                <td>
                    <a href="{{ route('admin.order.invoice', $item->id) }}" class="btn btn-sm btn-primary">Invoice</a>
                </td>
                @endif
                <td>
                    <p class="small">{{date('j M Y g:i A', strtotime($item->created_at))}}</p>
                </td>
                <td>
                    <p class="small text-muted mb-2">{{$item->billing_address.' | '.$item->billing_landmark.' | '.$item->billing_pin.' | '.$item->billing_city.' | '.$item->billing_state.' | '.$item->billing_country}}</p>
                    <div class="btn-group" role="group">
						<a href="javascript: void(0)" onclick="statusUpdate({{$item->id}}, 0)" type="button" class="status_0 btn btn-outline-primary btn-sm {{($item->status == 0) ? 'active' : ''}}">Pending</a>
                        <a href="javascript: void(0)" onclick="statusUpdate({{$item->id}}, 1)" type="button" class="status_1 btn btn-outline-primary btn-sm {{($item->status == 1) ? 'active' : ''}}">New</a>
                        <a href="javascript: void(0)" onclick="statusUpdate({{$item->id}}, 2)" type="button" class="status_2 btn btn-outline-primary btn-sm {{($item->status == 2) ? 'active' : ''}}">Confirm</a>
                        <a href="javascript: void(0)" onclick="statusUpdate({{$item->id}}, 3)" type="button" class="status_3 btn btn-outline-primary btn-sm {{($item->status == 3) ? 'active' : ''}}">Shipped</a>
                        <a href="javascript: void(0)" onclick="statusUpdate({{$item->id}}, 4)" type="button" class="status_4 btn btn-outline-success btn-sm {{($item->status == 4) ? 'active' : ''}}">Delivered</a>
                        <a href="javascript: void(0)" onclick="statusUpdate({{$item->id}}, 5)" type="button" class="status_5 btn btn-outline-danger btn-sm {{($item->status == 5) ? 'active' : ''}}" onclick="return confirm('Are you sure ?')">Cancelled</a>
                    </div>
                </td>
            </tr>
            @empty
            <tr><td colspan="100%" class="small text-muted">No data found</td></tr>
            @endforelse
        </tbody>
    </table>
</section>
@endsection

@section('script')


   <script>
        function statusUpdate(orderId, status) {
            const res = confirm('Customer will receive email about the order. Are you sure ?');

            if (res === true) {
                $('#row_'+orderId+' .btn-group .btn').addClass('disabled');
                $.ajax({
                    url: "{{ route('admin.order.status') }}",
                    type: "POST",
                    data: {
                        _token: "{{csrf_token()}}",
                        id: orderId,
                        status: status,
                    },
                  success: function(response) {
						console.log(response); // Check the full response in the console
						if (response.error === false) {
							$('#row_'+orderId+' .btn-group .btn').removeClass('active').removeClass('disabled');
							$('#row_'+orderId+' .btn-group .status_'+status).addClass('active');
							toastr.success(response.message, 'Success');
						} else {
							toastr.success(response.message, 'error');
						}
					},
					error: function(xhr, status, error) {
						toastr.success(error, 'error');
						console.error('AJAX Error: ', error); // Log the error
					}
                });
            } else {
                return false;
            }
        }
	   


    </script>
@endsection
