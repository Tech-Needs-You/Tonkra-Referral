@extends('layouts/contentLayoutMaster')

@section('title', __('referral::locale.labels.referral'))


@section('vendor-style')
    <link rel="stylesheet" href="{{ asset(mix('vendors/css/tables/datatable/dataTables.bootstrap5.min.css')) }}">
    <link rel="stylesheet" href="{{ asset(mix('vendors/css/tables/datatable/responsive.bootstrap5.min.css')) }}">
    <link rel="stylesheet" href="{{ asset(mix('vendors/css/tables/datatable/buttons.bootstrap5.min.css')) }}">
    <link rel="stylesheet" href="{{ asset(mix('vendors/css/extensions/sweetalert2.min.css')) }}">
@endsection


@section('content')
    <section id="vertical-tabs">
        <div class="row match-height">
            <div class="col-12">

                <ul class="nav nav-pills mb-2 text-uppercase" role="tablist">
                  @if ((bool)$referral_preference?->get('status'))
										<li class="nav-item">
                        <a class="nav-link @if (old('tab') == 'info' || (request()->input('tab') == 'info' || old('tab') == null)) active @endif" id="info-tab" data-bs-toggle="tab" aria-controls="info" href="#info" role="tab" aria-selected="true">
													<i data-feather="heart"></i> {{ __('referral::locale.referrals.info') }}
												</a>
                    </li>
									@endif  

                    <li class="nav-item">
                        <a class="nav-link @if ((old('tab') == 'preferences' || request()->input('tab') == 'preferences') || !(bool)$referral_preference?->get('status')) active @endif" id="preferences-tab" data-bs-toggle="tab" aria-controls="preferences"
                           href="#preferences" role="tab" aria-selected="false"> <i data-feather="settings"></i> {{__('referral::locale.labels.preferences')}} </a>
                    </li>
                </ul>

                <div class="tab-content">
                    @if ((bool)$referral_preference?->get('status'))
												<div class="tab-pane fade @if (old('tab') == 'info' || (request()->input('tab') == 'info' || old('tab') == null))  show active @endif" id="info" role="tabpanel" aria-labelledby="info-tab">
														@include('referral::customer.referrals._info')
												</div>
										@endif

                    <div class="tab-pane fade @if (old('tab') == 'preferences' || (request()->input('tab') == 'downliners')  || !(bool)$referral_preference?->get('status'))  show active @endif" id="preferences" role="tabpanel" aria-labelledby="preferences-tab">
                        @include('referral::customer.referrals._preferences')
                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection

@section('vendor-script')
    <!-- vendor files -->
    <script src="{{ asset(mix('vendors/js/tables/datatable/jquery.dataTables.min.js')) }}"></script>
    <script src="{{ asset(mix('vendors/js/tables/datatable/dataTables.bootstrap5.min.js')) }}"></script>
    <script src="{{ asset(mix('vendors/js/tables/datatable/dataTables.responsive.min.js')) }}"></script>
    <script src="{{ asset(mix('vendors/js/tables/datatable/responsive.bootstrap5.min.js')) }}"></script>
    <script src="{{ asset(mix('vendors/js/tables/datatable/datatables.checkboxes.min.js')) }}"></script>
    <script src="{{ asset(mix('vendors/js/tables/datatable/datatables.buttons.min.js')) }}"></script>
    <script src="{{ asset(mix('vendors/js/tables/datatable/buttons.html5.min.js')) }}"></script>
    <script src="{{ asset(mix('vendors/js/tables/datatable/dataTables.rowGroup.min.js')) }}"></script>

    <script src="{{ asset(mix('vendors/js/extensions/sweetalert2.all.min.js')) }}"></script>
    <script src="{{ asset(mix('vendors/js/extensions/polyfill.min.js')) }}"></script>
@endsection


@section('page-script')

    <script>
        $(document).ready(function () {
				"use strict"
				
				// init table dom
				let Table = $("table[id=downliners-table]");
				
				// init list view datatable
				let dataListView = $('.datatables-basic-downliners').DataTable({
						"processing": true,
						"serverSide": true,
						"ajax": {
								"url": "{{ route('referral.customer.downliners.search') }}",
								"dataType": "json",
								"type": "POST",
								"data": {_token: "{{csrf_token()}}"}
						},
						"columns": [
								{"data": 'responsive_id', orderable: false, searchable: false},
								{"data": "uid"},
								{"data": "uid"},
								{"data": "name"},
								{"data": "email", orderable: false, searchable: true},
								{"data": "balance", orderable: false, searchable: false},
								{"data": "status", orderable: false, searchable: false},
								{"data": "action", orderable: false, searchable: false}
						],
						searchDelay: 500,
						columnDefs: [
								{
										// For Responsive
										className: 'control',
										orderable: false,
										responsivePriority: 2,
										targets: 0
								},
								{
										// For Checkboxes
										targets: 1,
										orderable: false,
										responsivePriority: 3,
										render: function (data) {
												return (
														'<div class="form-check"> <input class="form-check-input dt-checkboxes" type="checkbox" value="" id="' +
														data +
														'" /><label class="form-check-label" for="' +
														data +
														'"></label></div>'
												);
										},
										checkboxes: {
												selectAllRender:
														'<div class="form-check"> <input class="form-check-input" type="checkbox" value="" id="checkboxSelectAll" /><label class="form-check-label" for="checkboxSelectAll"></label></div>',
												selectRow: true
										}
								},
								{
										targets: 2,
										visible: false
								},
								{
										// Avatar image/badge, Name and post
										targets: 3,
										responsivePriority: 1,
										render: function (data, type, full) {
												var $user_img = full['avatar'],
														$name = full['name'],
														$created_at = full['created_at'],
														$email = full['email'];
												if ($user_img) {
														// For Avatar image
														var $output =
																'<img src="' + $user_img + '" alt="Avatar" width="32" height="32">';
												} else {
														// For Avatar badge
														var stateNum = full['status'];
														var states = ['success', 'danger', 'warning', 'info', 'dark', 'primary', 'secondary'];
														var $state = states[stateNum],
																$name = full['name'],
																$initials = $name.match(/\b\w/g) || [];
														$initials = (($initials.shift() || '') + ($initials.pop() || '')).toUpperCase();
														$output = '<span class="avatar-content">' + $initials + '</span>';
												}
												var colorClass = $user_img === '' ? ' bg-light-' + $state + ' ' : '';
												// Creates full output for row
												return '<div class="d-flex justify-content-left align-items-center">' +
														'<div class="avatar ' +
														colorClass +
														' me-1">' +
														$output +
														'</div>' +
														'<div class="d-flex flex-column">' +
														'<span class="emp_name text-truncate fw-bold">' +
														$name +
														'</span>' +
														'<small class="emp_post text-truncate text-muted">' +
														$created_at +
														'</small>' +
														'</div>' +
														'</div>';
										}
								},
								{
										// Status
										targets: 6,
										responsivePriority: 4,
										render: function (data, type, full) {
												var $status 			= full['status'],
														$status_color = full['status_color'],
														$status_label = full['status_label'];
												
												// Creates full output for row
												return '<a nohref class="'+ $status_color +' px-1" data-bs-toggle="tooltip" data-bs-placement="top" title="' + $status_label + '">' +
														feather.icons[$status].toSvg({class: 'font-medium-5'}) +
														'</a>';
										}
								},
								{
										// Actions
										targets: -1,
										title: '{{ __('locale.labels.actions') }}',
										orderable: false,
										render: function (data, type, full) {
												var $super_user = '';
												
												return (
														$super_user +
														'<a class="copy text-primary pe-1" data-text="'+ full['copy'] +'" data-bs-toggle="tooltip" data-bs-placement="top" title="' + full['copy_label'] + '" >' +
														feather.icons['copy'].toSvg({class: 'font-medium-4'}) +
														'</a>'//+
														// '<a nohref="' + full['top_up'] + '" class="text-success pe-1" data-bs-toggle="tooltip" data-bs-placement="top" title="' + full['top_up_label'] + '" onclick="alert(\'Under construction\')">' +
														// feather.icons['trending-up'].toSvg({class: 'font-medium-4'}) +
														// '</a>'+
														// '<a nohref="' + full['report'] + '" class="text-danger" data-bs-toggle="tooltip" data-bs-placement="top" title="' + full['report_label'] + '" onclick="alert(\'Under construction\')">' +
														// feather.icons['flag'].toSvg({class: 'font-medium-4'}) +
														// '</a>'
												);
										}
								}
						],
						dom: '<"d-flex justify-content-between align-items-center mx-0 row"<"col-sm-12 col-md-6"l><"col-sm-12 col-md-6"f>>t<"d-flex justify-content-between mx-0 row"<"col-sm-12 col-md-6"i><"col-sm-12 col-md-6"p>>',
						language: {
								paginate: {
										// remove previous & next text from pagination
										previous: '&nbsp;',
										next: '&nbsp;'
								},
								sLengthMenu: "_MENU_",
								sZeroRecords: "{{ __('locale.datatables.no_results') }}",
								sSearch: "{{ __('locale.datatables.search') }}",
								sProcessing: "{{ __('locale.datatables.processing') }}",
								sInfo: "{{ __('locale.datatables.showing_entries', ['start' => '_START_', 'end' => '_END_', 'total' => '_TOTAL_']) }}"
						},
						responsive: {
								details: {
										display: $.fn.dataTable.Responsive.display.modal({
												header: function (row) {
														let data = row.data();
														return 'Details of ' + data['name'];
												}
										}),
										type: 'column',
										renderer: function (api, rowIdx, columns) {
												let data = $.map(columns, function (col) {
														return col.title !== '' // ? Do not show row in modal popup if title is blank (for check box)
																? '<tr data-dt-row="' +
																col.rowIdx +
																'" data-dt-column="' +
																col.columnIndex +
																'">' +
																'<td>' +
																col.title +
																':' +
																'</td> ' +
																'<td>' +
																col.data +
																'</td>' +
																'</tr>'
																: '';
												}).join('');
												return data ? $('<table class="table"/>').append('<tbody>' + data + '</tbody>') : false;
										}
								}
						},
						aLengthMenu: [[10, 20, 50, 100], [10, 20, 50, 100]],
						select: {
								style: "multi"
						},
						order: [[2, "desc"]],
						displayLength: 10,
				});

				$("body").on('click', '.copy', function (e) {
					e.preventDefault()
					copyToClipboard($(this).data('text'));
				})
			});
    </script>
@endsection


