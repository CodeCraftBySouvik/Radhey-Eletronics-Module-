@extends('admin.layouts.app')
@section('page', 'Stock Audit Log')
@section('content')
<section>
    <div class="row">        
        <div class="col-sm-2">  
            <a href="{{ url('/samplecsv/sample-stock-csv.csv') }}" class="btn btn-outline-success select-md">Download Sample CSV</a>   
        </div>
        <div class="col-sm-2">  
            <form action="{{ route('admin.report.save-current-stock') }}" id="saveStock">
                
                <div class="col">                                   
                    <button type="submit" onclick="return confirm('Are you sure want to log now?');" class="btn btn-outline-success select-md" id="logStock">Log Current Stock</button>
                </div>                                            
            </form>               
        </div>
    </div>
    <div class="row">        
        <div class="col-sm-8 order-1 order-xl-2">   
            @if (Session::has('message'))
            <div class="alert alert-success" id="alert-success" role="alert">
                {{ Session::get('message') }}
            </div>
            @endif
            @if (Session::has('messageErr'))
            <div class="alert alert-danger" id="alert-danger" role="alert">
                {{ Session::get('messageErr') }}
            </div>
            @endif         
            <table class="table">
                <thead>
                    <tr>                                
                        <th>#</th>
                        <th>Date</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    @php
                        $i=1;
                    @endphp
                    @forelse ($data as $index => $item)
                    <tr>                                  
                        <td>{{$i}}</td>
                        <td>
                            {{ date('d/m/Y', strtotime($item->entry_date)) }}
                            
                        </td>
                        <td>
                            
                            <button type="button" class="btn btn-outline-success select-md" data-bs-toggle="modal" data-bs-target="#exampleModal{{ date('Y-m-d', strtotime($item->entry_date)) }}"> Upload Godown Stock CSV </button>

                            <a href="{{ route('admin.stockaudit.view-final-stock',$item->entry_date) }}" class="btn btn-outline-success select-md ">View Stock Audit Report</a>


                            <a href="{{ route('admin.report.stock-audit-csv') }}?entry_date={{ date('Y-m-d', strtotime($item->entry_date)) }}" class="btn btn-outline-primary select-md">System Stock CSV</a>
                            <a href="{{ route('admin.stockaudit.get_uploaded_log_csv') }}?entry_date={{ date('Y-m-d', strtotime($item->entry_date)) }}" class="btn btn-outline-info select-md">Godown Stock CSV</a>

                            <div class="modal fade" id="exampleModal{{ date('Y-m-d', strtotime($item->entry_date)) }}" tabindex="-1" aria-labelledby="" aria-hidden="true">
                                <div class="modal-dialog modal-md">
                                    <form method="POST" action="{{ route('admin.stockaudit.upload_csv') }}" enctype="multipart/form-data" id="myForm">
                                    @csrf
                                    <input type="hidden" name="entry_date" value="{{ date('Y-m-d', strtotime($item->entry_date)) }}">   
                                    <div class="modal-content">
                                        <div class="modal-header">
                                            <h5 class="modal-title" id="prodTitle">
                                                Upload Godown Stock For {{ date('d/m/Y', strtotime($item->entry_date)) }}
                                            </h5>
                                            
                                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                        </div>
                                        <div class="modal-body">
                                            <div class="form-group mb-3">
                                                <label class="label-control">Upload File <span class="text-danger">*</span> </label>
                                                <input type="file" class="form-control" name="csv" accept=".csv" id="" required>
                                                @error('csv') <p class="small text-danger">{{ $message }}</p> @enderror
                                            </div>
                                        </div>
                                        <div class="modal-footer">
                                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                                            <button class="btn btn-success" id="submitBtn">Submit</button>
                                        </div>
                                    </div>
                                    </form>
                                </div>
                            </div>
                        </td>
                    </tr>
                    @php
                        $i++;
                    @endphp
                    @empty
                    <tr><td colspan="100%" class="small text-muted">No data found</td></tr>
                    @endforelse
                </tbody>
            </table>                 
        </div> 
        
    </div>
</section>
@endsection
@section('script')
<script>
    $(document).ready(function(){
        $('#alert-success').delay(3000).slideUp(300);

        $("#saveStock").submit(function() {
            
            // $('input').attr('disabled', 'disabled');
            $('#logStock').attr('disabled', 'disabled');
            return true;
        });
    });

    $("#myForm").submit(function() {
        $('input').attr('readonly', 'readonly');
        $('#submitBtn').attr('disabled', 'disabled');    
        $('#submitBtn').html('<i class="fi fi-br-refresh"></i>').append('   Please wait ... , It will take a bit time');
        return true;
    });
    
</script>
@endsection
