@extends('admin.layouts.app')
@section('page', 'Day Cash Entry')

@section('content')

<section class="container">
    <section class="admin__title">
        <h5>Daily Cash Entry</h5>
    </section>

    <section>
        <ul class="breadcrumb_menu">
            <li>Daily Cash Entry</li>
            <li></li>
        </ul>
    </section>

    <div class="row mb-4">
        <div class="col-lg-12 col-md-6 mb-md-0 mb-4">
            <div class="card my-4">

                <div class="card-header pb-0">
                    @if (session('success'))
                        <div class="alert alert-success">
                            {{ session('success') }}
                        </div>
                    @endif

                    @if (session('error'))
                        <div class="alert alert-danger">
                            {{ session('error') }}
                        </div>
                    @endif
                </div>

                <div class="card-body px-0 pb-2 mx-4">
                    <form action="" method="POST">
                        @csrf

                        <div class="row">

                            {{-- Entry Type --}}
                            <label class="form-label">
                                Type <span class="text-danger">*</span>
                            </label>
                            <div class="mb-2">
                                <select name="entry_type"
                                        class="form-control @error('entry_type') is-invalid @enderror">
                                    <option value="">Select Type</option>
                                    <option value="collected" {{ old('entry_type') == 'collected' ? 'selected' : '' }}>
                                        Collect
                                    </option>
                                    <option value="given" {{ old('entry_type') == 'given' ? 'selected' : '' }}>
                                        Given
                                    </option>
                                </select>
                            </div>
                            @error('entry_type')
                                <p class="text-danger">{{ $message }}</p>
                            @enderror

                            {{-- User --}}
                            <label class="form-label">
                                User <span class="text-danger">*</span>
                            </label>
                            <div class="mb-2">
                                <select name="staff_id"
                                        class="form-control @error('staff_id') is-invalid @enderror">
                                    <option value="">Choose an user</option>
                                    @foreach ($staffs as $staff)
										<option value="{{ $staff->id }}"
											{{ old('staff_id') == $staff->id ? 'selected' : '' }}>
											{{ ucwords($staff->name) }}
										</option>
									@endforeach
                                    
                                </select>
                            </div>
                            @error('staff_id')
                                <p class="text-danger">{{ $message }}</p>
                            @enderror

                            {{-- Wallet Balance --}}
                            <label class="form-label">
                                Current Wallet Balance <span class="text-danger">*</span>
                            </label>
                            <div class="mb-2">
                                <input type="text"
                                       class="form-control"
                                       value="{{ $totalWallet ?? '' }}"
                                       disabled>
                            </div>

                            {{-- Collected Section --}}
                            @if(old('entry_type') === 'collected')

                                <div class="form-check mt-2">
                                    <input type="checkbox"
                                           class="form-check-input"
                                           name="payment_cash"
                                           value="1"
                                           {{ old('payment_cash') ? 'checked' : '' }}>
                                    <label class="form-check-label">Cash</label>
                                </div>

                                <div class="form-group mt-2">
                                    <label>Cash Collected Amount</label>
                                    <input type="number"
                                           name="cash_collected_amount"
                                           class="form-control"
                                           value="{{ old('cash_collected_amount') }}">
                                    @error('cash_collected_amount')
                                        <span class="text-danger">{{ $message }}</span>
                                    @enderror
                                </div>

                                <div class="form-check mt-2">
                                    <input type="checkbox"
                                           class="form-check-input"
                                           name="payment_digital"
                                           value="1"
                                           {{ old('payment_digital') ? 'checked' : '' }}>
                                    <label class="form-check-label">Digital Payment</label>
                                </div>

                                <div class="form-group mt-2">
                                    <label>Digital Payment Collected Amount</label>
                                    <input type="number"
                                           name="digital_collected_amount"
                                           class="form-control"
                                           value="{{ old('digital_collected_amount') }}">
                                    @error('digital_collected_amount')
                                        <span class="text-danger">{{ $message }}</span>
                                    @enderror
                                </div>

                            @endif

                            {{-- Given Section --}}
                            @if(old('entry_type') === 'given')
                                <label class="form-label mt-2">
                                    Given Amount <span class="text-danger">*</span>
                                </label>
                                <div class="mb-2">
                                    <input type="number"
                                           name="given_amount"
                                           class="form-control"
                                           value="{{ old('given_amount') }}"
                                           placeholder="Enter Disbursed Amount">
                                </div>
                                @error('given_amount')
                                    <p class="text-danger">{{ $message }}</p>
                                @enderror
                            @endif

                            {{-- Submit --}}
                            <div class="mt-4">
                                <button type="submit" class="btn btn-sm btn-success">
                                    Create
                                </button>
                            </div>

                        </div>
                    </form>
                </div>

            </div>
        </div>
    </div>
</section>


@endsection
