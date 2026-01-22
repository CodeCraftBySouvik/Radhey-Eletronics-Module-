@extends('admin.layouts.app')
@section('page', 'Cashbook Module')

@section('content')
<style>
 .suggestions-list {
    position: absolute;
    top: 100%;           /* right below the input */
    left: 0;
    width: 100%;
    background: #fff;
    border: 1px solid #ccc;
    border-top: none;
    z-index: 1000;
    max-height: 200px;   /* scroll if too many suggestions */
    overflow-y: auto;
}

.suggestions-list div {
    padding: 8px 12px;
    cursor: pointer;
}

.suggestions-list div:hover {
    background-color: #f1f1f1;
}
	
	.svg-card {
    position: relative;
    border-radius: 14px;
    padding: 20px;
    color: #fff;
    min-height: 110px;
    display: flex;
    align-items: center;
    box-shadow: 0 8px 20px rgba(0,0,0,0.15);
}

.svg-card .icon-box {
    width: 55px;
    height: 55px;
    border-radius: 12px;
    display: flex;
    align-items: center;
    justify-content: center;
    background: rgba(255,255,255,0.2);
}

.svg-card svg {
    width: 32px;
    height: 32px;
}

.svg-card .content {
    margin-left: 15px;
    text-align: right;
    width: 100%;
}

.svg-card h6 {
    margin: 0;
    font-size: 14px;
    opacity: 0.9;
}

.svg-card h3 {
    margin: 0;
    font-weight: 700;
}

	.bg-green { background: linear-gradient(135deg, #00b09b, #96c93d); }
.bg-red { background: linear-gradient(135deg, #e53935, #e35d5b); }
.bg-blue { background: linear-gradient(135deg, #1e88e5, #42a5f5); }
.bg-purple { background: linear-gradient(135deg, #5e35b1, #7e57c2); }
.bg-orange { background: linear-gradient(135deg, #fb8c00, #fbc02d); }
.bg-cyan { background: linear-gradient(135deg, #00acc1, #26c6da); }


</style>
<section class="container">
	
    <!-- Breadcrumb -->
    <div class="mb-3">
        <ul class="breadcrumb bg-white p-2 rounded">
            <li class="breadcrumb-item">Accounting</li>
            <li class="breadcrumb-item active text-danger">Cashbook</li>
        </ul>
    </div>
    <!-- Filters -->
    <div class="card mb-3">
        <div class="card-body">
			<form method="GET" action="{{ route('admin.accounting.cash_book_module') }}">
            <div class="row g-2 align-items-end">
                <div class="col-md-3">
                    <label>Staff name</label>
					 <div class="position-relative">
						<input type="text" name="staff_name" value="{{ $staffName ?? '' }}" onkeyup="getUser(this.value)" class="form-control" placeholder="Staff name" autocomplete="off">
						<div id="suggestions" class="suggestions-list"></div>
					</div>
                </div>

                <div class="col-md-3">
                    <label>Start Date</label>
                    <input type="date" name="start_date" id="start_date"  value="{{ $startDate }}" class="form-control" onchange="applyFilters()" value="{{ date('Y-m-d') }}">
                </div>

                <div class="col-md-3">
                    <label>End Date</label>
                    <input type="date" name="end_date" id="end_date"  value="{{ $endDate }}" class="form-control" onchange="applyFilters()" value="{{ date('Y-m-d') }}">
                </div>
				
				
				
                <div class="col-md-3">
                    <a href="{{ route('admin.accounting.cash_book_module') }}" class="btn btn-danger w-100">CLEAR</a>
                </div>
            </div>
		  </form>
        </div>
    </div>

    <!-- Summary Cards -->
    <div class="row g-3 mb-3">
		<div class="col-md-3">
			<div class="svg-card bg-purple">

				<div class="icon-box">
					<!-- SALES SVG -->
					<!--<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="white">
						<path stroke-width="2"
							  d="M3 3v18h18M7 15l4-4 3 3 5-6"/>
					</svg>-->
						<svg xmlns="http://www.w3.org/2000/svg" version="1.1" xmlns:xlink="http://www.w3.org/1999/xlink"  x="0" y="0" viewBox="0 0 512 512" style="enable-background:new 0 0 512 512" xml:space="preserve" class=""><g><path d="M478.743 250.208c-16.117-16.458-42.608-16.746-59.051-.645l-1.936 1.893v-141.37a7.001 7.001 0 0 0-7.001-7.001h-50.74a7.001 7.001 0 0 0-7.001 7.001v169.777c-5.026-4.575-11.276-7.824-18.199-9.185v-99.392a7.001 7.001 0 0 0-7.001-7.001h-50.712a7.001 7.001 0 0 0-7.001 7.001v98.674h-13.14a112.642 112.642 0 0 0-5.059-3.553V135.059a7.001 7.001 0 0 0-7.001-7.001h-50.74a7.001 7.001 0 0 0-7.001 7.001v120.62c-6.05 1.92-12.117 4.651-18.198 8.177v-85.342a7.001 7.001 0 0 0-7.001-7.001h-50.712a7.001 7.001 0 0 0-7.001 7.001v132.4l-11.745-11.747a7.002 7.002 0 0 0-9.902 0L23.41 358.355a7.001 7.001 0 0 0 0 9.901L165.114 509.96a7.003 7.003 0 0 0 9.903-.001l59.159-59.188a7.002 7.002 0 0 0-.001-9.9l-19.295-19.299 13.643-13.624 126.701-.083c14.657 0 27.405-5.202 37.891-15.46l84.986-83.115c16.466-16.124 16.754-42.629.642-59.082zm-74.99-133.12v148.066l-36.737 35.936V117.088zm-119.651 61.2h36.709v91.672h-36.709zm-82.942-36.227h36.737v116.577c-12.125-5.376-24.385-7.353-36.737-5.962zm-46.204 43.455v87.859c-.307.24-.614.466-.921.709a6.731 6.731 0 0 0-.365.311l-35.422 32.285V185.516zm15.108 309.591L38.262 363.305l49.285-49.285 15.683 15.686c.022.025.04.052.063.076.177.195.364.374.557.544l115.473 115.496zm298.243-195.825-84.984 83.113c-7.887 7.717-17.078 11.468-28.103 11.468l-129.6.085a7 7 0 0 0-4.943 2.047L204.98 411.67l-86.363-86.381 44.306-40.382c30.22-23.882 58.799-24.673 87.369-2.423a7.001 7.001 0 0 0 4.302 1.478h72.878c13.223 0 23.981 10.758 23.981 23.981 0 .473-.018.942-.045 1.409-.009.163-.024.324-.037.486a25.013 25.013 0 0 1-.158 1.46c-.044.311-.095.62-.151.929-.026.145-.049.292-.078.436-.092.46-.196.917-.316 1.37-.006.025-.014.048-.021.073-.126.47-.265.936-.421 1.398l-.009.028a24.21 24.21 0 0 1-1.901 4.225c-.057.1-.104.203-.156.305-4.169 7.107-11.88 11.892-20.69 11.892H225.624a7.001 7.001 0 0 0 0 14.002h101.849c13.792 0 25.888-7.396 32.543-18.431l55.432-54.223c.14-.127.275-.258.404-.395l13.634-13.337c10.929-10.701 28.536-10.506 39.252.435 10.709 10.937 10.515 28.558-.431 39.277zM117.45 140.761a7.002 7.002 0 0 1 .141-9.901l73.502-71.433a7 7 0 0 1 7.162-1.598l96.762 33.355 92.847-77.192h-24.817a7.001 7.001 0 0 1 0-14.002h42.662a7.001 7.001 0 0 1 7.001 7.001V49.68a7.001 7.001 0 0 1-14.002 0V23.187l-97.77 81.284a6.998 6.998 0 0 1-6.758 1.235L197.764 72.47l-70.413 68.431a6.98 6.98 0 0 1-4.879 1.98 6.967 6.967 0 0 1-5.022-2.12z" fill="#ffffff" opacity="1" data-original="#000000" class=""></path></g></svg>
				</div>

				<div class="content">
					<h6>Total Sales</h6>
					<h3>{{ number_format($totalOrderSales, 2) }}</h3>
				</div>

			</div>
		</div>


        <div class="col-md-3">
			<div class="svg-card bg-green">
				<div class="icon-box">
					<!---<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="white">
						<path stroke-width="2"
							  d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8V6m0 12v-2"/>
					</svg>--->
						<svg xmlns="http://www.w3.org/2000/svg" version="1.1" xmlns:xlink="http://www.w3.org/1999/xlink" x="0" y="0" viewBox="0 0 500 500" style="enable-background:new 0 0 512 512" xml:space="preserve" class=""><g><path fill-rule="evenodd" d="M57.285 104.432a6.86 6.86 0 0 1 6.856-6.856l48.754-.001a6.86 6.86 0 0 1 6.856 6.856 6.853 6.853 0 0 1-6.856 6.856l-48.754.001a6.853 6.853 0 0 1-6.856-6.856zm385.424-.009a6.852 6.852 0 0 1-6.856 6.856l-48.748.001a6.844 6.844 0 0 1-6.85-6.856 6.851 6.851 0 0 1 6.85-6.856l48.748-.001a6.86 6.86 0 0 1 6.856 6.856zM57.286 140.994a6.86 6.86 0 0 1 6.856-6.856l48.754-.001a6.86 6.86 0 0 1 6.856 6.856 6.853 6.853 0 0 1-6.856 6.856l-48.754.001a6.853 6.853 0 0 1-6.856-6.856zm385.424-.01a6.852 6.852 0 0 1-6.856 6.856l-48.749.001a6.844 6.844 0 0 1-6.85-6.856 6.851 6.851 0 0 1 6.85-6.856l48.749-.001a6.86 6.86 0 0 1 6.856 6.856zm-223.943-36.555a6.86 6.86 0 0 1 6.856-6.856l48.748-.001a6.851 6.851 0 0 1 6.85 6.856 6.844 6.844 0 0 1-6.85 6.856l-48.748.001a6.853 6.853 0 0 1-6.856-6.856zm.001 36.561a6.86 6.86 0 0 1 6.856-6.856l48.748-.001a6.851 6.851 0 0 1 6.85 6.856 6.844 6.844 0 0 1-6.85 6.856l-48.748.001a6.853 6.853 0 0 1-6.856-6.856zm222.676 201.549c-30.993 27.176-101.411 75.407-134.552 92.151-12.522 6.325-26.401 9.762-43.677 10.814-35.187 2.138-63.933.487-91.733-1.112-23.132-1.334-45.11-2.598-70.2-1.829l-.002-78.852c12.132-4.626 22.138-8.908 31.946-13.678 23.409-11.258 34.083-16.399 60.86-18.525 29.988-2.386 45.057 2.922 69.559 16.552 1.762.982 17.149 9.888 13.811 19.915-.143.883-1.49 3.098-2.322 4.176-5.458 7.058-18.026 4.789-25.148 1.549-11.821-5.388-32.247-11.054-44.301-11.025-6.088.029-12.77 1.288-19.863 3.737a6.862 6.862 0 0 0-4.251 8.722c1.242 3.575 5.146 5.475 8.722 4.239 5.722-1.979 10.962-2.986 15.595-2.986 9.559 0 28.452 5.238 38.416 9.777 14.717 6.713 34.288 7.113 43.712-8.596 22.11 1.813 50.25-8.226 70.685-17.381 25.35-11.368 51.376-27.171 66.3-40.259 5.588-4.909 13.728-3.921 18.263 1.94 4.764 6.156 3.927 15.628-1.82 20.671zM87.571 457.909c0 2.131-1.791 3.933-3.916 3.933l-29.954.001c-2.131 0-3.933-1.802-3.934-3.933l-.002-100.304c0-2.131 1.802-3.933 3.933-3.933l29.954-.001c2.125 0 3.916 1.802 3.916 3.933zm348.114-154.721c-7.203-.693-14.203 1.595-19.725 6.429-26.165 22.927-89.165 55.442-124.289 54.447-.497-11.974-10.605-22.006-21.354-27.99-25.998-14.462-44.197-20.884-77.322-18.244-30.09 2.4-42.886 8.901-67.109 20.523-7.919 3.818-16.652 7.567-26.476 11.379-2.9-5.787-8.866-9.767-15.757-9.767l-29.954.001c-9.727 0-17.645 7.913-17.645 17.64l.002 100.304c0 9.727 7.919 17.645 17.646 17.645l29.954-.001c9.727 0 17.628-7.919 17.627-17.646v-1.634c45.116-1.405 82.43 3.989 127.953 3.965 10.957-.012 22.491-.301 34.811-1.052 19.205-1.173 34.782-5.066 49.031-12.258 34.545-17.461 105.171-65.813 137.406-94.086 11.199-9.819 12.822-27.476 3.62-39.368-4.592-5.932-11.124-9.582-18.419-10.287zm20.281-132.499c3.61 0 6.55-2.934 6.55-6.556l-.002-91.392-27.73.001a6.853 6.853 0 0 1-6.856-6.856l-.001-27.73-60.93.001c-3.61 0-6.556 2.94-6.556 6.55l.003 119.428A6.562 6.562 0 0 0 367 170.69zM441.639 47.854l11.177 11.176H441.64zm32.576 13.186-34.587-34.585a6.836 6.836 0 0 0-4.846-2.01l-67.786.002c-11.171 0-20.262 9.092-20.261 20.262l.003 119.428c0 11.182 9.092 20.267 20.262 20.267l37.618-.001.001 35.048-69.31.002a6.856 6.856 0 0 0-6.856 6.851l.001 50.475-10.576-10.575a6.854 6.854 0 0 0-9.698 0 6.865 6.865 0 0 0 0 9.698l22.4 22.387c0 .006.006.006.006.006l.011.017c2.742 2.543 6.902 2.48 9.501-.069v-.012c.012 0 .012 0 .012-.011h.011l.017-.012c0-.012.012-.012.012-.012 0-.011.006-.011.006-.017l22.283-22.278a6.854 6.854 0 0 0 0-9.698 6.84 6.84 0 0 0-9.686 0l-10.587 10.576-.001-43.619 69.316-.002a6.848 6.848 0 0 0 6.85-6.856l-.001-41.898 37.636-.001c11.171 0 20.261-9.086 20.261-20.268l-.002-98.248a6.856 6.856 0 0 0-2.01-4.847zM37.487 164.144a6.554 6.554 0 0 0 6.556 6.555l88.954-.002a6.553 6.553 0 0 0 6.55-6.556l-.002-91.392-27.73.001a6.853 6.853 0 0 1-6.856-6.856l-.001-27.73-60.918.002a6.558 6.558 0 0 0-6.556 6.55zM118.67 47.862l11.188 11.176H118.67zM81.661 184.41l.001 41.898a6.857 6.857 0 0 0 6.856 6.856l69.31-.002.001 43.619-10.576-10.575a6.854 6.854 0 0 0-9.698 0 6.865 6.865 0 0 0 0 9.698l22.313 22.312c.012 0 .012 0 .023.006 0 .011.006.011.006.023 1.311 1.253 2.967 1.946 4.788 1.946 1.778 0 3.549-.707 4.8-1.958 0 0 .011 0 .011-.011l22.329-22.319c2.68-2.68 2.68-7.018 0-9.698s-7.023-2.68-9.698 0l-10.587 10.576-.001-50.475a6.848 6.848 0 0 0-6.856-6.85l-69.31.002-.001-35.048 37.624-.001c11.176 0 20.262-9.086 20.261-20.268l-.002-98.248a6.877 6.877 0 0 0-2.004-4.846l-34.587-34.585a6.86 6.86 0 0 0-4.852-2.01l-67.774.002c-11.182 0-20.267 9.092-20.267 20.262l.003 119.428c0 11.182 9.086 20.267 20.268 20.267zM198.96 44.712l.003 119.428a6.558 6.558 0 0 0 6.556 6.555l88.96-.002a6.561 6.561 0 0 0 6.555-6.556l-.002-91.392-27.73.001a6.854 6.854 0 0 1-6.862-6.856l-.001-27.73-60.924.001c-3.615.001-6.555 2.942-6.555 6.551zm81.192 3.146 11.182 11.176h-11.182zm-74.633 136.549 37.63-.001.002 92.373-10.587-10.575a6.854 6.854 0 0 0-9.698 0 6.876 6.876 0 0 0 0 9.698c2.878 2.883 21.68 21.908 23.11 22.97 2.695 2.001 6.511 1.662 8.872-.693l22.282-22.278a6.854 6.854 0 0 0 0-9.698 6.847 6.847 0 0 0-9.692 0l-10.575 10.576-.002-92.373 37.618-.001c11.171 0 20.267-9.086 20.267-20.268l-.002-98.248a6.838 6.838 0 0 0-2.016-4.846l-34.587-34.585a6.824 6.824 0 0 0-4.84-2.01l-67.786.002c-11.171 0-20.267 9.092-20.267 20.262l.003 119.428c0 11.182 9.098 20.268 20.268 20.267z" clip-rule="evenodd" fill="#ffffff" opacity="1" data-original="#000000" class=""></path></g></svg>
				</div>
				<div class="content">
					<h6>Total Collection</h6>
					<h3>{{ number_format($totalCollections, 2) }}</h3>
				</div>
			</div>
		</div>
		
		 <!-- Total Expenses -->
		<div class="col-md-3">
			<div class="svg-card bg-red">
				<div class="icon-box">
					<!---<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="white">
						<path stroke-width="2" d="M9 14l6-6M9 8h.01M15 14h.01"/>
					</svg>--->
<svg xmlns="http://www.w3.org/2000/svg" version="1.1" xmlns:xlink="http://www.w3.org/1999/xlink" x="0" y="0" viewBox="0 0 682.667 682.667" style="enable-background:new 0 0 512 512" xml:space="preserve" class=""><g><defs><clipPath id="a" clipPathUnits="userSpaceOnUse"><path d="M0 512h512V0H0Z" fill="#ffffff" opacity="1" data-original="#000000"></path></clipPath></defs><g clip-path="url(#a)" transform="matrix(1.33333 0 0 -1.33333 0 682.667)"><path d="M0 0h223.522c12.296 0 22.264 9.968 22.264 22.264v86.309" style="stroke-width:15;stroke-linecap:round;stroke-linejoin:round;stroke-miterlimit:10;stroke-dasharray:none;stroke-opacity:1" transform="translate(232.593 52.338)" fill="none" stroke="#ffffff" stroke-width="15" stroke-linecap="round" stroke-linejoin="round" stroke-miterlimit="10" stroke-dasharray="none" stroke-opacity="" data-original="#000000" opacity="1" class=""></path><path d="M0 0h-60.689c-12.296 0-22.264-9.968-22.264-22.264V-169.66" style="stroke-width:15;stroke-linecap:round;stroke-linejoin:round;stroke-miterlimit:10;stroke-dasharray:none;stroke-opacity:1" transform="translate(208.911 504.5)" fill="none" stroke="#ffffff" stroke-width="15" stroke-linecap="round" stroke-linejoin="round" stroke-miterlimit="10" stroke-dasharray="none" stroke-opacity="" data-original="#000000" opacity="1" class=""></path><path d="M0 0v287.147c0 12.296-9.968 22.264-22.264 22.264H-235.29" style="stroke-width:15;stroke-linecap:round;stroke-linejoin:round;stroke-miterlimit:10;stroke-dasharray:none;stroke-opacity:1" transform="translate(478.379 195.089)" fill="none" stroke="#ffffff" stroke-width="15" stroke-linecap="round" stroke-linejoin="round" stroke-miterlimit="10" stroke-dasharray="none" stroke-opacity="" data-original="#000000" opacity="1" class=""></path><path d="M435.2 377.006H169.138v73.718H435.2Z" style="stroke-width:15;stroke-linecap:round;stroke-linejoin:round;stroke-miterlimit:10;stroke-dasharray:none;stroke-opacity:1" fill="none" stroke="#ffffff" stroke-width="15" stroke-linecap="round" stroke-linejoin="round" stroke-miterlimit="10" stroke-dasharray="none" stroke-opacity="" data-original="#000000" opacity="1" class=""></path><path d="M0 0a14.318 14.318 0 0 0 12.391 7.131h18.57c7.912 0 14.326-6.414 14.326-14.326 0-7.912-6.414-14.326-14.326-14.326h-12.57" style="stroke-width:15;stroke-linecap:round;stroke-linejoin:round;stroke-miterlimit:10;stroke-dasharray:none;stroke-opacity:1" transform="translate(244.538 321.791)" fill="none" stroke="#ffffff" stroke-width="15" stroke-linecap="round" stroke-linejoin="round" stroke-miterlimit="10" stroke-dasharray="none" stroke-opacity="" data-original="#000000" opacity="1" class=""></path><path d="M0 0h-18.57c-7.912 0-14.326 6.414-14.326 14.326 0 7.912 6.414 14.326 14.326 14.326H0c7.912 0 14.326-6.414 14.326-14.326C14.326 6.414 7.912 0 0 0Z" style="stroke-width:15;stroke-linecap:round;stroke-linejoin:round;stroke-miterlimit:10;stroke-dasharray:none;stroke-opacity:1" transform="translate(347.41 300.27)" fill="none" stroke="#ffffff" stroke-width="15" stroke-linecap="round" stroke-linejoin="round" stroke-miterlimit="10" stroke-dasharray="none" stroke-opacity="" data-original="#000000" opacity="1" class=""></path><path d="M0 0h-18.57c-7.912 0-14.326 6.414-14.326 14.326 0 7.912 6.414 14.326 14.326 14.326H0c7.912 0 14.326-6.414 14.326-14.326C14.326 6.414 7.912 0 0 0Z" style="stroke-width:15;stroke-linecap:round;stroke-linejoin:round;stroke-miterlimit:10;stroke-dasharray:none;stroke-opacity:1" transform="translate(419.32 300.27)" fill="none" stroke="#ffffff" stroke-width="15" stroke-linecap="round" stroke-linejoin="round" stroke-miterlimit="10" stroke-dasharray="none" stroke-opacity="" data-original="#000000" opacity="1" class=""></path><path d="M0 0h14.57c7.912 0 14.326-6.414 14.326-14.326 0-7.912-6.414-14.326-14.326-14.326H-4c-7.912 0-14.326 6.414-14.326 14.326" style="stroke-width:15;stroke-linecap:round;stroke-linejoin:round;stroke-miterlimit:10;stroke-dasharray:none;stroke-opacity:1" transform="translate(260.929 255.996)" fill="none" stroke="#ffffff" stroke-width="15" stroke-linecap="round" stroke-linejoin="round" stroke-miterlimit="10" stroke-dasharray="none" stroke-opacity="" data-original="#000000" opacity="1" class=""></path><path d="M0 0h-18.57c-7.912 0-14.326 6.414-14.326 14.326 0 7.912 6.414 14.326 14.326 14.326H0c7.912 0 14.326-6.414 14.326-14.326C14.326 6.414 7.912 0 0 0Z" style="stroke-width:15;stroke-linecap:round;stroke-linejoin:round;stroke-miterlimit:10;stroke-dasharray:none;stroke-opacity:1" transform="translate(347.41 227.343)" fill="none" stroke="#ffffff" stroke-width="15" stroke-linecap="round" stroke-linejoin="round" stroke-miterlimit="10" stroke-dasharray="none" stroke-opacity="" data-original="#000000" opacity="1" class=""></path><path d="M0 0h-18.57c-7.912 0-14.326 6.414-14.326 14.326 0 7.912 6.414 14.326 14.326 14.326H0c7.912 0 14.326-6.414 14.326-14.326C14.326 6.414 7.912 0 0 0Z" style="stroke-width:15;stroke-linecap:round;stroke-linejoin:round;stroke-miterlimit:10;stroke-dasharray:none;stroke-opacity:1" transform="translate(419.32 227.343)" fill="none" stroke="#ffffff" stroke-width="15" stroke-linecap="round" stroke-linejoin="round" stroke-miterlimit="10" stroke-dasharray="none" stroke-opacity="" data-original="#000000" opacity="1" class=""></path><path d="M0 0v0c0 7.912 6.414 14.326 14.326 14.326h18.57c7.913 0 14.327-6.414 14.327-14.326 0-7.912-6.414-14.327-14.327-14.327h-16.57" style="stroke-width:15;stroke-linecap:round;stroke-linejoin:round;stroke-miterlimit:10;stroke-dasharray:none;stroke-opacity:1" transform="translate(242.602 183.069)" fill="none" stroke="#ffffff" stroke-width="15" stroke-linecap="round" stroke-linejoin="round" stroke-miterlimit="10" stroke-dasharray="none" stroke-opacity="" data-original="#000000" opacity="1" class=""></path><path d="M0 0h-18.57c-7.912 0-14.326 6.415-14.326 14.327s6.414 14.326 14.326 14.326H0c7.912 0 14.326-6.414 14.326-14.326C14.326 6.415 7.912 0 0 0Z" style="stroke-width:15;stroke-linecap:round;stroke-linejoin:round;stroke-miterlimit:10;stroke-dasharray:none;stroke-opacity:1" transform="translate(347.41 168.742)" fill="none" stroke="#ffffff" stroke-width="15" stroke-linecap="round" stroke-linejoin="round" stroke-miterlimit="10" stroke-dasharray="none" stroke-opacity="" data-original="#000000" opacity="1" class=""></path><path d="M0 0h-18.57c-7.912 0-14.326 6.415-14.326 14.327s6.414 14.326 14.326 14.326H0c7.912 0 14.326-6.414 14.326-14.326C14.326 6.415 7.912 0 0 0Z" style="stroke-width:15;stroke-linecap:round;stroke-linejoin:round;stroke-miterlimit:10;stroke-dasharray:none;stroke-opacity:1" transform="translate(419.32 168.742)" fill="none" stroke="#ffffff" stroke-width="15" stroke-linecap="round" stroke-linejoin="round" stroke-miterlimit="10" stroke-dasharray="none" stroke-opacity="" data-original="#000000" opacity="1" class=""></path><path d="M0 0h12.57c7.912 0 14.326-6.414 14.326-14.326 0-7.912-6.414-14.327-14.326-14.327H-4" style="stroke-width:15;stroke-linecap:round;stroke-linejoin:round;stroke-miterlimit:10;stroke-dasharray:none;stroke-opacity:1" transform="translate(262.929 138.794)" fill="none" stroke="#ffffff" stroke-width="15" stroke-linecap="round" stroke-linejoin="round" stroke-miterlimit="10" stroke-dasharray="none" stroke-opacity="" data-original="#000000" opacity="1" class=""></path><path d="M0 0h-18.57c-7.912 0-14.326 6.415-14.326 14.327s6.414 14.326 14.326 14.326H0c7.912 0 14.326-6.414 14.326-14.326C14.326 6.415 7.912 0 0 0Z" style="stroke-width:15;stroke-linecap:round;stroke-linejoin:round;stroke-miterlimit:10;stroke-dasharray:none;stroke-opacity:1" transform="translate(347.41 110.142)" fill="none" stroke="#ffffff" stroke-width="15" stroke-linecap="round" stroke-linejoin="round" stroke-miterlimit="10" stroke-dasharray="none" stroke-opacity="" data-original="#000000" opacity="1" class=""></path><path d="M0 0h-18.57c-7.912 0-14.326 6.415-14.326 14.327s6.414 14.326 14.326 14.326H0c7.912 0 14.326-6.414 14.326-14.326C14.326 6.415 7.912 0 0 0Z" style="stroke-width:15;stroke-linecap:round;stroke-linejoin:round;stroke-miterlimit:10;stroke-dasharray:none;stroke-opacity:1" transform="translate(419.32 110.142)" fill="none" stroke="#ffffff" stroke-width="15" stroke-linecap="round" stroke-linejoin="round" stroke-miterlimit="10" stroke-dasharray="none" stroke-opacity="" data-original="#000000" opacity="1" class=""></path><path d="M0 0v-41.878c0-15.808 34.533-28.977 80.291-31.901" style="stroke-width:15;stroke-linecap:round;stroke-linejoin:round;stroke-miterlimit:10;stroke-dasharray:none;stroke-opacity:1" transform="translate(33.62 81.886)" fill="none" stroke="#ffffff" stroke-width="15" stroke-linecap="round" stroke-linejoin="round" stroke-miterlimit="10" stroke-dasharray="none" stroke-opacity="" data-original="#000000" opacity="1" class=""></path><path d="M0 0c47.836 2.361 84.504 15.852 84.504 32.141v41.878" style="stroke-width:15;stroke-linecap:round;stroke-linejoin:round;stroke-miterlimit:10;stroke-dasharray:none;stroke-opacity:1" transform="translate(148.089 7.867)" fill="none" stroke="#ffffff" stroke-width="15" stroke-linecap="round" stroke-linejoin="round" stroke-miterlimit="10" stroke-dasharray="none" stroke-opacity="" data-original="#000000" opacity="1" class=""></path><path d="M0 0c3.864-3.469 5.972-7.213 5.972-11.119 0-17.953-44.542-32.507-99.487-32.507-54.944 0-99.486 14.554-99.486 32.507 0 9.681 12.95 18.373 33.498 24.328" style="stroke-width:15;stroke-linecap:round;stroke-linejoin:round;stroke-miterlimit:10;stroke-dasharray:none;stroke-opacity:1" transform="translate(226.622 93.004)" fill="none" stroke="#ffffff" stroke-width="15" stroke-linecap="round" stroke-linejoin="round" stroke-miterlimit="10" stroke-dasharray="none" stroke-opacity="" data-original="#000000" opacity="1" class=""></path><path d="M0 0v-41.878c0-17.954 44.542-32.507 99.486-32.507 54.945 0 99.487 14.553 99.487 32.507V0" style="stroke-width:15;stroke-linecap:round;stroke-linejoin:round;stroke-miterlimit:10;stroke-dasharray:none;stroke-opacity:1" transform="translate(61.892 160.733)" fill="none" stroke="#ffffff" stroke-width="15" stroke-linecap="round" stroke-linejoin="round" stroke-miterlimit="10" stroke-dasharray="none" stroke-opacity="" data-original="#000000" opacity="1" class=""></path><path d="M0 0c20.773-5.958 33.886-14.698 33.886-24.44 0-17.953-44.542-32.507-99.486-32.507-54.945 0-99.486 14.554-99.486 32.507 0 3.962 2.169 7.759 6.141 11.27" style="stroke-width:15;stroke-linecap:round;stroke-linejoin:round;stroke-miterlimit:10;stroke-dasharray:none;stroke-opacity:1" transform="translate(226.978 185.173)" fill="none" stroke="#ffffff" stroke-width="15" stroke-linecap="round" stroke-linejoin="round" stroke-miterlimit="10" stroke-dasharray="none" stroke-opacity="" data-original="#000000" opacity="1" class=""></path><path d="M0 0v-41.878c0-17.954 44.542-32.508 99.486-32.508 54.945 0 99.487 14.554 99.487 32.508v38.554" style="stroke-width:15;stroke-linecap:round;stroke-linejoin:round;stroke-miterlimit:10;stroke-dasharray:none;stroke-opacity:1" transform="translate(33.62 239.58)" fill="none" stroke="#ffffff" stroke-width="15" stroke-linecap="round" stroke-linejoin="round" stroke-miterlimit="10" stroke-dasharray="none" stroke-opacity="" data-original="#000000" opacity="1" class=""></path><path d="M0 0c-5.095-16.392-47.462-29.184-98.973-29.184-54.944 0-99.486 14.554-99.486 32.508 0 8.343 9.618 15.951 25.429 21.707" style="stroke-width:15;stroke-linecap:round;stroke-linejoin:round;stroke-miterlimit:10;stroke-dasharray:none;stroke-opacity:1" transform="translate(232.08 236.257)" fill="none" stroke="#ffffff" stroke-width="15" stroke-linecap="round" stroke-linejoin="round" stroke-miterlimit="10" stroke-dasharray="none" stroke-opacity="" data-original="#000000" opacity="1" class=""></path><path d="M0 0v-41.878c0-17.954 44.542-32.508 99.486-32.508 54.945 0 99.487 14.554 99.487 32.508V0" style="stroke-width:15;stroke-linecap:round;stroke-linejoin:round;stroke-miterlimit:10;stroke-dasharray:none;stroke-opacity:1" transform="translate(61.892 302.332)" fill="none" stroke="#ffffff" stroke-width="15" stroke-linecap="round" stroke-linejoin="round" stroke-miterlimit="10" stroke-dasharray="none" stroke-opacity="" data-original="#000000" opacity="1" class=""></path><path d="M0 0c0-17.953-44.542-32.507-99.486-32.507-54.945 0-99.487 14.554-99.487 32.507 0 17.954 44.542 32.508 99.487 32.508C-44.542 32.508 0 17.954 0 0Z" style="stroke-width:15;stroke-linecap:round;stroke-linejoin:round;stroke-miterlimit:10;stroke-dasharray:none;stroke-opacity:1" transform="translate(260.864 302.332)" fill="none" stroke="#ffffff" stroke-width="15" stroke-linecap="round" stroke-linejoin="round" stroke-miterlimit="10" stroke-dasharray="none" stroke-opacity="" data-original="#000000" opacity="1" class=""></path><path d="M0 0h25.358" style="stroke-width:15;stroke-linecap:round;stroke-linejoin:round;stroke-miterlimit:10;stroke-dasharray:none;stroke-opacity:1" transform="translate(68.814 356.426)" fill="none" stroke="#ffffff" stroke-width="15" stroke-linecap="round" stroke-linejoin="round" stroke-miterlimit="10" stroke-dasharray="none" stroke-opacity="" data-original="#000000" opacity="1" class=""></path><path d="M0 0h25.358" style="stroke-width:15;stroke-linecap:round;stroke-linejoin:round;stroke-miterlimit:10;stroke-dasharray:none;stroke-opacity:1" transform="translate(68.814 379.666)" fill="none" stroke="#ffffff" stroke-width="15" stroke-linecap="round" stroke-linejoin="round" stroke-miterlimit="10" stroke-dasharray="none" stroke-opacity="" data-original="#000000" opacity="1" class=""></path><path d="M0 0h-.1" style="stroke-width:15;stroke-linecap:round;stroke-linejoin:round;stroke-miterlimit:10;stroke-dasharray:none;stroke-opacity:1" transform="translate(94.222 405.45)" fill="none" stroke="#ffffff" stroke-width="15" stroke-linecap="round" stroke-linejoin="round" stroke-miterlimit="10" stroke-dasharray="none" stroke-opacity="" data-original="#000000" opacity="1" class=""></path></g></g></svg>
				</div>
				<div class="content">
					<h6>Total Expenses</h6>
					<h3>{{ number_format($totalExpenses, 2) }}</h3>
				</div>
			</div>
		</div>

		
        <div class="col-md-3">
			<div class="svg-card bg-orange">
				<div class="icon-box">
					<!---<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="white">
						<path stroke-width="2"  d="M3 7h18v10H3z"/>
					</svg>--->			
			
<svg xmlns="http://www.w3.org/2000/svg" version="1.1" xmlns:xlink="http://www.w3.org/1999/xlink"  x="0" y="0" viewBox="0 0 512 512" style="enable-background:new 0 0 512 512" xml:space="preserve" class=""><g><path d="M482 275.988h-10v-70c0-27.596-19.378-50.736-45.238-56.568l-45.515-78.834c-6.67-11.553-17.44-19.818-30.327-23.271-9.099-2.437-18.481-2.258-27.279.398l-33.096-33.097c-19.475-19.473-51.159-19.475-70.634 0L86.541 147.988H58c-31.981 0-58 26.019-58 58v248c0 31.981 26.019 58 58 58h133c5.522 0 10-4.478 10-10s-4.478-10-10-10H58c-20.953 0-38-17.047-38-38v-248c0-20.953 17.047-38 38-38h356c20.953 0 38 17.047 38 38v70h-74c-29.775 0-54 24.225-54 54s24.225 54 54 54h74v70c0 20.953-17.047 38-38 38H281c-5.522 0-10 4.478-10 10s4.478 10 10 10h133c31.981 0 58-26.019 58-58v-70h10c16.542 0 30-13.458 30-30v-48c0-16.541-13.458-30-30-30zM345.744 66.634c7.726 2.07 14.184 7.025 18.183 13.952l38.915 67.402H187.293l135.728-78.363c6.926-3.998 14.994-5.062 22.723-2.991zm-111.69-37.875c5.655-5.656 13.176-8.771 21.174-8.771 7.999 0 15.52 3.115 21.175 8.771l28.331 28.331-157.44 90.898h-32.468zM492 353.988c0 5.514-4.486 10-10 10H378c-18.748 0-34-15.252-34-34s15.252-34 34-34h104c5.514 0 10 4.486 10 10z" fill="#ffffff" opacity="1" data-original="#000000" class=""></path><path d="M376.33 319.988c-2.63 0-5.21 1.069-7.07 2.93s-2.93 4.43-2.93 7.07c0 2.63 1.069 5.21 2.93 7.069a10.077 10.077 0 0 0 7.07 2.931c2.64 0 5.21-1.07 7.069-2.931 1.87-1.859 2.931-4.439 2.931-7.069s-1.061-5.21-2.931-7.07a10.054 10.054 0 0 0-7.069-2.93zM236 491.988c-2.63 0-5.21 1.069-7.07 2.93s-2.93 4.44-2.93 7.07 1.069 5.21 2.93 7.069c1.86 1.86 4.44 2.931 7.07 2.931s5.21-1.07 7.069-2.931c1.86-1.859 2.931-4.439 2.931-7.069s-1.07-5.21-2.931-7.07a10.072 10.072 0 0 0-7.069-2.93z" fill="#ffffff" opacity="1" data-original="#000000" class=""></path></g></svg>					
					
				</div>
				<div class="content">
					<h6>Total Wallet</h6>
					<h3>{{ number_format($totalWallet, 2) }}</h3>
				</div>
			</div>
		</div>
		</div>
		

       
       <div class="row g-3 mb-4">

		<!-- Cash -->
		<div class="col-md-4">
			<div class="svg-card bg-blue">
				<div class="icon-box">
					<!---<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="white">
						<path stroke-width="2" d="M12 6v12M6 12h12"/>
					</svg>--->
						<svg xmlns="http://www.w3.org/2000/svg" version="1.1" xmlns:xlink="http://www.w3.org/1999/xlink" x="0" y="0" viewBox="0 0 512 512" style="enable-background:new 0 0 512 512" xml:space="preserve" class=""><g><path d="M512 215.76a6.928 6.928 0 0 0-3.473-5.993l-35.173-20.201 35.187-20.31c2.144-1.239 3.464-3.527 3.459-6.002s-1.329-4.759-3.473-5.991a599306.821 599306.821 0 0 1-179.865-103.3 6.912 6.912 0 0 0-6.905.007L3.464 237.74a6.92 6.92 0 0 0 .014 11.991c11.722 6.736 23.447 13.464 35.17 20.199L3.459 290.244A6.91 6.91 0 0 0 0 296.242a6.928 6.928 0 0 0 3.473 5.993l35.172 20.2-35.186 20.311a6.916 6.916 0 0 0-3.459 6 6.928 6.928 0 0 0 3.473 5.993l179.865 103.3a6.917 6.917 0 0 0 6.905-.009L508.54 274.262a6.91 6.91 0 0 0 3.459-6 6.927 6.927 0 0 0-3.473-5.991l-35.172-20.199 35.186-20.314a6.91 6.91 0 0 0 3.46-5.998zM325.225 67.947a607885.758 607885.758 0 0 0 165.986 95.331l-119.802 69.15a5206.762 5206.762 0 0 1-19.895-11.369l68.107-39.299a6.92 6.92 0 0 0 2.369-9.719c-1.464-2.293-3.613-6.854-1.532-11.631a6.922 6.922 0 0 0-2.901-8.759l-73.631-42.318a6.923 6.923 0 0 0-5.865-.482c-9.459 3.532-22.369 2.973-32.878-1.417a6.934 6.934 0 0 0-6.126.394l-72.072 41.617-7.55-4.347-13.953-8.032zm81.951 105.016-69.546 40.13a109647.28 109647.28 0 0 1-96.783-55.667l62.279-35.967c11.914 4.164 25.387 4.687 36.586 1.414l66.315 38.113c-.586 3.938-.194 8.015 1.149 11.977zm-266.717 1.639c6.684 3.84 13.369 7.676 20.054 11.516l-67.991 39.277a6.911 6.911 0 0 0-3.261 4.351 6.904 6.904 0 0 0 .892 5.365c1.464 2.293 3.613 6.854 1.536 11.626a6.922 6.922 0 0 0 2.901 8.759l73.608 42.252a6.916 6.916 0 0 0 5.865.482c9.45-3.529 22.36-2.975 32.883 1.41a6.894 6.894 0 0 0 6.121-.394l11.342-6.55c20.233-11.685 40.463-23.368 60.699-35.04l21.358 12.26-119.693 69.128a447608.223 447608.223 0 0 1-165.991-95.329zm-35.491 59.584 69.417-40.102c29.279 16.815 58.559 33.63 87.84 50.438l9.007 5.17c-17.917 10.336-35.83 20.677-53.741 31.021L209 285.618c-11.923-4.164-25.387-4.676-36.59-1.408l-66.288-38.05c.581-3.936.189-8.013-1.154-11.974zM52.52 277.897a459935.93 459935.93 0 0 0 130.818 75.131 6.917 6.917 0 0 0 6.905-.009l123.19-71.148.088 36.505-126.746 73.174-165.991-95.331zm134.255 166.158L20.784 348.724l31.739-18.319 130.815 75.13a6.917 6.917 0 0 0 6.905-.009L313.56 334.33l.089 36.478zm140.693-81.225-.225-92.953a6.91 6.91 0 0 0-3.473-5.984s-131.198-75.309-169.459-97.291l37.324-21.541s161.41 92.932 172.932 99.216l.194 97.018zm163.748-94.543-112.633 65.031-.073-36.487 80.989-46.758zm-112.739 12.585-.073-36.501 81.091-46.807 31.721 18.218zm-211.824-23.763a6.916 6.916 0 0 1 2.532-9.45l30.311-17.502a6.911 6.911 0 0 1 9.45 2.532 6.916 6.916 0 0 1-2.532 9.45l-30.311 17.502a6.911 6.911 0 0 1-9.45-2.532zm182.689-105.487a6.916 6.916 0 0 1-2.532 9.45l-26.658 15.392a6.87 6.87 0 0 1-3.455.928 6.91 6.91 0 0 1-5.995-3.459 6.916 6.916 0 0 1 2.532-9.45l26.658-15.392a6.911 6.911 0 0 1 9.45 2.531z" fill="#ffffff" opacity="1" data-original="#000000" class=""></path></g></svg>
				</div>
				<div class="content">
					<h6>Total Cash</h6>
					<h3>{{ number_format($totalcashCollections, 2) }}</h3>
				</div>
			</div>
		</div>

		<!-- NEFT -->
		<div class="col-md-4">
			<div class="svg-card bg-cyan">
				<div class="icon-box">
					<!--<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="white">
						<path stroke-width="2" d="M4 4h16v16H4z"/>
					</svg>-->
						<svg xmlns="http://www.w3.org/2000/svg" version="1.1" xmlns:xlink="http://www.w3.org/1999/xlink" x="0" y="0" viewBox="0 0 28 28" style="enable-background:new 0 0 512 512" xml:space="preserve" class=""><g><g data-name="Layer 2"><path d="M4 12.5a.5.5 0 0 0 .5-.5V6.5h6.293L9.646 7.646a.5.5 0 0 0 .708.708l2-2a.5.5 0 0 0 0-.708l-2-2a.5.5 0 0 0-.708.708L10.793 5.5H4a.5.5 0 0 0-.5.5v6a.5.5 0 0 0 .5.5ZM24 17.5a.5.5 0 0 0-.5.5v4.5h-6.293l1.147-1.146a.5.5 0 0 0-.708-.708l-2 2a.5.5 0 0 0 0 .708l2 2a.5.5 0 0 0 .708-.708L17.207 23.5H24a.5.5 0 0 0 .5-.5v-5a.5.5 0 0 0-.5-.5ZM13.143 16.5h-.951l-4.286-3.871a.5.5 0 0 0-.664 0L2.812 16.5H2a.5.5 0 0 0-.5.5v2a.5.5 0 0 0 .5.5h.5v4.062a1.29 1.29 0 0 0-1 1.245V26a.5.5 0 0 0 .5.5h11.143a.5.5 0 0 0 .5-.5v-1.193a1.294 1.294 0 0 0-1.143-1.274V19.5h.643a.5.5 0 0 0 .5-.5v-2a.5.5 0 0 0-.5-.5Zm-5.577-2.831L10.7 16.5H4.331ZM9.5 19.5v4h-4v-4Zm-5 4h-1v-4h1Zm8.143 1.307v.693H2.5v-.693a.308.308 0 0 1 .308-.307h9.528a.308.308 0 0 1 .307.307ZM11.5 23.5h-1v-4h1Zm1.143-5H2.5v-1h10.143ZM26 8.5a.5.5 0 0 0 .5-.5V6a.5.5 0 0 0-.5-.5h-.95l-4.286-3.871a.5.5 0 0 0-.664 0L15.669 5.5h-.812a.5.5 0 0 0-.5.5v2a.5.5 0 0 0 .5.5h.5v4.062a1.29 1.29 0 0 0-1 1.245V15a.5.5 0 0 0 .5.5H26a.5.5 0 0 0 .5-.5v-1.193a1.3 1.3 0 0 0-1.143-1.274V8.5Zm-5.576-5.831L23.558 5.5h-6.37ZM15.357 6.5H25.5v1H15.357Zm3 6v-4h4v4Zm-1-4v4h-1v-4Zm8.143 5.307v.693H15.357v-.693a.308.308 0 0 1 .307-.307h9.528a.308.308 0 0 1 .308.307ZM24.357 12.5h-1v-4h1Z" fill="#ffffff" opacity="1" data-original="#000000" class=""></path></g></g></svg>		
					
				</div>
				<div class="content">
					<h6>Total NEFT</h6>
					<h3>{{ number_format($totalneftCollections, 2) }}</h3>
				</div>
			</div>
		</div>

		<!-- Cheque -->
		<div class="col-md-4">
			<div class="svg-card bg-purple">
				<div class="icon-box">
					<!--<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="white">
						<path stroke-width="2" d="M3 10h18M3 14h18"/>
					</svg>-->
					<svg xmlns="http://www.w3.org/2000/svg" version="1.1" xmlns:xlink="http://www.w3.org/1999/xlink" x="0" y="0" viewBox="0 0 510.272 510.272" style="enable-background:new 0 0 512 512" xml:space="preserve" class=""><g><path d="M41.942 324.242h202.43c4.143 0 7.5-3.358 7.5-7.5s-3.357-7.5-7.5-7.5H41.942a7.5 7.5 0 0 0 0 15zM224.234 393.193c1.861 6.389 3.785 12.997 10.386 12.997 3.567 0 5.996 0 34.363-33.116 8.374 9.433 23.45 23.764 37.763 23.762.78 0 1.562-.043 2.337-.131 8.547-.977 15.441-6.706 21.525-11.76 11.14-9.256 13.138-9.153 17.544-4.62 16.464 16.934 36.541 11.185 44.854 5.779 3.474-2.258 4.484-6.919 2.227-10.392-2.257-3.474-6.879-4.474-10.351-2.217-1.429.895-14.288 8.396-25.976-3.626-8.27-8.507-16.479-8.295-21.911-6.618-5.966 1.842-11.054 6.069-15.974 10.157-4.689 3.896-9.539 7.925-13.643 8.394-8.344.96-24.237-14.316-32.348-24.934a7.5 7.5 0 0 0-11.686-.306c-8.816 10.395-19.006 21.991-25.648 29.097-2.462-9.24-5.81-24.022-8.47-37.069a7.499 7.499 0 0 0-6.781-5.98 7.478 7.478 0 0 0-7.606 4.889l-18.402 50.018a7.5 7.5 0 0 0 14.078 5.179l9.53-25.905c1.609 6.86 3.017 12.376 4.189 16.402zM41.942 290.504h202.43c4.143 0 7.5-3.358 7.5-7.5s-3.357-7.5-7.5-7.5H41.942a7.5 7.5 0 0 0 0 15zM53.347 257.081h259.052c10.424 0 18.904-8.48 18.904-18.904v-21.861c0-10.424-8.48-18.904-18.904-18.904H53.347c-10.424 0-18.904 8.48-18.904 18.904v21.861c-.001 10.423 8.48 18.904 18.904 18.904zm-3.905-40.766a3.909 3.909 0 0 1 3.904-3.904h259.052a3.908 3.908 0 0 1 3.904 3.904v21.861a3.908 3.908 0 0 1-3.904 3.904H53.347a3.909 3.909 0 0 1-3.904-3.904v-21.861zM459.78 389.135a7.5 7.5 0 0 0-7.5 7.5v17.77c0 4.384-3.566 7.95-7.95 7.95h-222.7c-4.143 0-7.5 3.358-7.5 7.5s3.357 7.5 7.5 7.5h222.7c12.655 0 22.95-10.295 22.95-22.95v-17.77a7.5 7.5 0 0 0-7.5-7.5z" fill="#ffffff" opacity="1" data-original="#000000" class=""></path><path d="M505.005 128.44c-4.546-7.261-11.51-12.665-19.775-15.422l2.583-9.712c1.572-5.909.608-12.085-2.713-17.39-3.22-5.141-8.295-8.851-14.291-10.446l-6.478-1.723c-12.572-3.343-25.394 3.706-28.587 15.71l-21.146 79.507H22.939C10.291 168.965 0 179.26 0 191.915v222.49c0 12.655 10.291 22.95 22.939 22.95h164.69c4.143 0 7.5-3.358 7.5-7.5s-3.357-7.5-7.5-7.5H22.939c-4.378 0-7.939-3.566-7.939-7.95v-222.49c0-4.383 3.562-7.95 7.939-7.95h387.67l-5.323 20.017-.017.053c-.005.018-.007.036-.012.055l-15.38 57.827c-.004.014-.01.027-.013.041l-.009.042-6.801 25.57c-2.755 10.362 2.554 21.076 12.138 25.967l-5.376 20.211a7.5 7.5 0 0 0 14.496 3.856l5.284-19.865a24.45 24.45 0 0 0 3.301.228 23.74 23.74 0 0 0 11.231-2.804c5.518-2.954 9.422-7.835 10.993-13.745l6.808-25.598.005-.013.005-.022 10.34-38.878v125.729c0 4.142 3.357 7.5 7.5 7.5s7.5-3.358 7.5-7.5v-170.72c0-2.453-.384-4.842-1.136-7.135l15.218-57.218c4.574 1.731 8.403 4.803 10.925 8.831 2.84 4.548 3.684 9.76 2.376 14.677l-12.357 46.461a7.5 7.5 0 0 0 14.496 3.856l12.357-46.461c2.385-8.971.912-18.374-4.153-26.487zm-84.378 169.122c-.492 1.85-1.762 3.403-3.576 4.375-1.982 1.062-4.349 1.31-6.658.695l-6.478-1.723c-4.576-1.217-7.431-5.472-6.364-9.485l4.884-18.363 23.076 6.138zm8.739-32.859-23.076-6.137 11.55-43.426 23.076 6.137zm15.405-57.923-23.076-6.137L450.24 93.314c.872-3.28 4.099-5.398 7.747-5.398.815 0 8.966 2.051 8.966 2.051 2.309.614 4.238 2.003 5.433 3.91 1.093 1.745 1.423 3.724.931 5.574z" fill="#ffffff" opacity="1" data-original="#000000" class=""></path></g></svg>
				</div>
				<div class="content">
					<h6>Total Cheque</h6>
					<h3>{{ number_format($totalchequeCollections, 2) }}</h3>
				</div>
			</div>
		</div>

	</div>


    <!-- Add Payment Button -->
    <div class="text-end mb-2">
        <a href="{{route('admin.accounting.add_payment_receipt')}}" class="btn btn-success">
            + ADD PAYMENT RECEIPT
        </a>
    </div>

    <!-- Payment Collection Table -->
    <div class="card">
        <div class="card-header bg-light">
            <strong>Payment Collection Details</strong>
        </div>

        <div class="card-body table-responsive">
            <table class="table text-center">
                <thead class="table-light">
                    <tr>
                        <th>#</th>
                        <th>Voucher No</th>
                        <th>Payment Date</th>
                        <th>Collected By</th>
                        <th>Customer</th>
                        <th>Collection Amount</th>
                        <th>Collected From</th>
                        <th>Approval</th>
                       <!-- <th>Action</th>  -->
                    </tr>
                </thead>
                <tbody>
				 @forelse($paymentCollections as $index => $collection)
					<tr>
					   <td>{{ $index + 1 }}</td>
                            <td>{{ $collection->vouchar_no }}</td>
                            <td>{{ \Carbon\Carbon::parse($collection->created_at)->format('d-m-Y') }}</td>
                            <td>{{ optional($collection->users)->name ?? 'N/A' }}</td>
                            <td>{{ optional($collection->stores)->store_name ?? 'N/A' }}</td>
                            <td>{{ number_format($collection->collection_amount, 2) }}</td>
                            <td>{{ ucwords($collection->payment_type) }}</td>
                            <td>
                                @if($collection->is_ledger_added == 1)
                                    <span class="badge bg-success">Approved</span>
                                @endif
                            </td>
                           <!-- <td>
                                @if(!empty($collection->is_ledger_added))
                                    <button class="btn btn-outline-warning btn-sm revoke-btn" data-id="{{ $collection->id }}">
                                        Revoke
                                    </button>
                                @endif
                                <a href="" class="btn btn-outline-primary btn-sm">
                                    Download
                                </a>
                            </td> -->
					</tr>
				 @empty
                    <tr>
                        <td colspan="9" class="text-muted">
                            No collection records found in selected date range.
                        </td>
                    </tr>
				@endforelse
                </tbody>
            </table>
        </div>
    </div>
	
	 <!-- Add Payment Button -->
    <div class="text-end mb-2">
        <a href="{{route('admin.accounting.add_expenses')}}" class="btn btn-success">
            + ADD EXPENSE
        </a>
    </div>
	
	 <!-- Payment Expense Table -->
    <div class="card">
        <div class="card-header bg-light">
            <strong>Expense Details</strong>
        </div>

        <div class="card-body table-responsive">
            <table class="table text-center">
                <thead class="table-light">
                    <tr>
                        <th>#</th>
                        <th>Expense Date</th>
                        <th>Transaction ID</th>
                        <th>Amount</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
					@forelse($paymentExpenses as $index => $expense)
					 @php
						$ExpenseAt = "";
						$ExpenseType = "";

						$expenseData =($expense->staff_id ? DB::table('users')->where('id',
						$expense->staff_id)->first() :
						($expense->store_id ? DB::table('users')->where('id', $expense->store_id)->first() :
						($expense->supplier_id ? DB::table('suppliers')->where('id', $expense->supplier_id)->first() :
						null)));



						$expenseType = $expense->expense_id ? DB::table('expense')->where('id',
						$expense->expense_id)->first() : null;
						$ExpenseType = $expenseType ? $expenseType->title : "";
                     @endphp
					<tr class="store_details_row">
					        <td>{{ $index + 1 }}</td>
						    <td>{{ $expense->payment_date }}</td>
                            <td>{{ $expense->voucher_no }}</td>
							<td>{{ $expense->amount }}</td>
                            <td>
                                 <a href="{{ route('admin.accounting.edit_expense', $expense->id) }}" class="btn btn-outline-success select-md">Edit</a>
                            </td>
                           
					</tr>
					 <tr>                        

                        <td colspan="5" class="store_details_column">

                            <div class="store_details">

                                <table class="table">                                   

                                    <tr>   

                                       <td><span>Amount: <strong>Rs. {{number_format((float)$expense->amount, 2, '.', '')}}</strong></span></td> 
                                        @if (!empty($expense->payment_mode))

                                            <td><span>Payment Mode: <strong>{{ ucwords($expense->payment_mode)}}</strong></span></td>    

                                        @endif

                                        @if (!empty($expense->bank_name))

                                            <td><span>Bank: <strong>{{ ucwords($expense->bank_name)}}</strong></span></td>    

                                        @endif

                                        @if (!empty($expense->chq_utr_no))

                                            <td><span>Cheque / UTR No: <strong>{{ ucwords($expense->chq_utr_no)}}</strong></span></td>    

                                        @endif

                                        @if (!empty($expense->narration))

                                            <td><span>Narration: <strong>{{ ucwords($expense->narration)}}</strong></span></td>    

                                        @endif

                                    </tr>

                                    <tr>

                                        @if (!empty($expense->creator))

                                            <td><span>Created By: <strong>{{ ucwords($expense->creator->name)}}</strong></span></td>  

                                            <td><span>Created At: <strong>{{ date('d/m/Y h:i A', strtotime($expense->created_at)) }}</strong></span></td>   
                                        @endif
                                        @if($ExpenseAt)
                                        <td><span>Expense At: <strong>{{ $ExpenseAt }}</strong></span></td> 
                                        @endif
                                        @if($ExpenseType)
                                        <td><span>Expense: <strong>{{ $ExpenseType }}</strong></span></td> 
                                        @endif
                                    </tr>

                                    <tr>

                                        @if (!empty($expense->updater))

                                            <td><span>Updated By: <strong>{{ ucwords($expense->updater->name)}}</strong></span></td>  

                                            <td><span>Updated At: <strong>{{ date('d/m/Y h:i A', strtotime($expense->updated_at)) }}</strong></span></td>    

                                        @endif

                                    </tr>

                                </table>

                            </div>

                        </td>

                    </tr>
				 @empty
                    <tr>
                        <td colspan="9" class="text-muted">
                             No expense records found in selected date range.
                        </td>
                    </tr>
				@endforelse
                   
                </tbody>
            </table>
        </div>
    </div>

</section>
<script>
function getUser(query) {
    if (query.length === 0) {
        document.getElementById('suggestions').innerHTML = '';
        return;
    }

    fetch(`{{ route('admin.accounting.get_staff') }}?query=${query}`)
    .then(response => response.json())
    .then(data => {
        let html = '';
        data.forEach(name => {
                html += `<div onclick="selectStaff('${name}')">${name}</div>`;
            });
        document.getElementById('suggestions').innerHTML = html;
    });

}
	
	function selectStaff(name) {
		const input = document.querySelector('input[name="staff_name"]');
		input.value = name;
		document.getElementById('suggestions').innerHTML = '';

		// submit the form automatically
		input.closest('form').submit();
	}

	
	
	function applyFilters() {
		const startDate = document.getElementById('start_date').value;
		const endDate = document.getElementById('end_date').value;
		const staffName = document.querySelector('input[name="staff_name"]').value;

		let url = '{{ route("admin.accounting.cash_book_module") }}?';
		url += 'start_date=' + startDate;
		url += '&end_date=' + endDate;

		if (staffName) {
			url += '&staff_name=' + encodeURIComponent(staffName);
		}

		window.location.href = url;
	}

</script>


@endsection
