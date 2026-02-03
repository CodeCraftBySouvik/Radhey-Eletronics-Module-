@extends('admin.layouts.app')

{{-- @section('page', 'Employee Attendance Report') --}}

@section('content')
<div class="container">
    <h2 class="mb-3">Attendance Report - {{ \Carbon\Carbon::create($year, $month, 1)->format('F Y') }}</h2>

        <form method="GET" action="{{ route('admin.hr.attendance-report') }}" class="row g-2 mb-3">
        <div class="col-md-4">
            <input
                type="month"
                name="month_year"
                class="form-control"
                value="{{ sprintf('%04d-%02d', $year, $month) }}"
            >
        </div>


        <div class="col-md-4">
            <select name="user_id" class="form-control">
                <option value="">All Employees</option>
                @foreach($users as $u)
                    <option value="{{ $u->id }}" {{ request('user_id') == $u->id ? 'selected' : '' }}>
                        {{ $u->name }}
                    </option>
                @endforeach
            </select>
        </div>

        <div class="col-md-2">
           <div class="d-flex justify-content-start">
            <button type="submit" class="btn btn-primary me-2">Filter</button>
            <a href="{{ route('admin.hr.attendance-report') }}" class="btn btn-secondary">Reset</a>
             
           </div>
        </div>

    </form>

    {{-- Legend --}}
    <div class="mb-2">
        <span class="badge text-white" style="background-color: #28a745;">P - Present</span>
        <span class="badge text-white" style="background-color: #dc3545;">A - Absent</span>
        <span class="badge text-dark" style="background-color: #ffc107;">W - Week Off</span>
    </div>

    <div class="table-responsive" style="overflow-x: auto; max-height: 600px;">
        <table class="table table-bordered table-sm text-center align-middle attendance-table">
            <thead class="table-dark position-sticky top-0">
                <tr>
                    <th style="position: sticky; left: 0; z-index:5;">User</th>
                    @for ($day = 1; $day <= $daysInMonth; $day++)
                        @php
                            $date = \Carbon\Carbon::create($year, $month, $day);
                            $weekday = $date->format('D');
                        @endphp
                        <th>{{ $day }} ({{ $weekday }})</th>
                    @endfor
                </tr>
            </thead>
            <tbody>
                @foreach ($attendanceSheet as $sheet)
                    <tr class="hover-row">
                        <td style="position: sticky; left: 0;  font-weight:600;">{{ $sheet['user']->name }}</td>
                        @for ($day = 1; $day <= $daysInMonth; $day++)
                            @php
                                $status = $sheet['attendance'][$day]; // 'P', 'A', 'W'
                                $bgColor = '';
                                $textColor = 'text-white';

                                switch($status) {
                                    case 'P': $bgColor = '#28a745'; break; // Green
                                    case 'A': $bgColor = '#dc3545'; break; // Red
                                    case 'W': $bgColor = '#ffc107'; $textColor = 'text-dark'; break; // Yellow
                                    default: $bgColor = ''; $textColor = '';
                                }
                            @endphp
                            <td class="{{ $textColor }}" style="background-color: {{ $bgColor }};">
                                {{ $status }}
                            </td>
                        @endfor
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>

{{-- Custom styles for better UI/UX --}}
<style>
    .attendance-table th, .attendance-table td {
        padding: 8px;
        font-size: 13px;
        min-width: 40px;
    }

    /* Hover effect on rows */
    .hover-row:hover {
        background-color: #f1f1f1;
    }

    /* Sticky headers for scroll */
    .attendance-table thead th {
        top: 0;
        z-index: 3;
    }

    /* Scrollable table */
    .table-responsive {
        overflow-x: auto;
    }

    /* Make sticky first column */
    .attendance-table td:first-child, 
    .attendance-table th:first-child {
        z-index: 4;
    }

    /* Rounded legend badges */
    .badge {
        padding: 5px 10px;
        font-size: 12px;
        margin-right: 5px;
    }
    .table thead tr th {
     background-color:#343a40 !important;
    }
    .table thead tr td {
     background-color:#fff !important;
    }
</style>
@endsection
