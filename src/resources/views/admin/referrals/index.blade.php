@php use Tonkra\Referral\Models\ReferralBonus; use Tonkra\Referral\Facades\ReferralSettings; @endphp;

@extends('layouts/contentLayoutMaster')

@section('title', __('referral::locale.labels.referral'))


@section('vendor-style')
<link rel="stylesheet" href="{{ asset(mix('vendors/css/tables/datatable/dataTables.bootstrap5.min.css')) }}">
<link rel="stylesheet" href="{{ asset(mix('vendors/css/tables/datatable/responsive.bootstrap5.min.css')) }}">
<link rel="stylesheet" href="{{ asset(mix('vendors/css/tables/datatable/buttons.bootstrap5.min.css')) }}">
<link rel="stylesheet" href="{{ asset(mix('vendors/css/extensions/sweetalert2.min.css')) }}">
@endsection

@section('page-style')
		<style>
			.nav-pills #info-tab.nav-link {
				color: var(--bs-primary);
			}
			.nav-pills #info-tab.nav-link.active {
				background-color: var(--bs-primary);
				border-color: var(--bs-primary);
				font-weight: bolder;
				color: #fff;
			}
			
			.nav-pills #preferences-tab.nav-link {
				color: var(--bs-dark);
			}
			.nav-pills #preferences-tab.nav-link.active {
				background-color: var(--bs-dark);
				border-color: var(--bs-dark);
				font-weight: bolder;
				color: #fff;
			}

			.nav-pills #redemptions-tab.nav-link {
				color: var(--bs-success);
			}
			.nav-pills #redemptions-tab.nav-link.active {
				background-color: var(--bs-success);
				border-color: var(--bs-success);
				font-weight: bolder;
				color: #fff;
			}

			.p-half {
				padding: .5rem;
			}
		</style>
@endsection


@section('content')

<div class="row">
	<div class="col-sm-12">
		<div class="row">
			<!-- Side Navigation -->
			<div class="col-md-3">
				<div class="card sticky-top" style="top: calc(62.4px + 18.2px + 1rem);">
					<div class="list-group list-group-flush" id="useradd-sidenav" role="tablist">
						<!-- Transaction Tab Link -->
						<a href="#transaction-content" class="list-group-item list-group-item-action border-0 active"
							id="transaction-tab" data-bs-toggle="tab" data-bs-target="#transaction-content" role="tab"
							aria-controls="transaction-content" aria-selected="true">
							{{__('referral::locale.referrals.referrals') }}
							<div class="float-end"><i class="ti ti-chevron-right"></i></div>
						</a>

						<!-- Payout Request Tab Link -->
						<a href="#payout-request-content" class="list-group-item list-group-item-action border-0"
							id="payout-request-tab" data-bs-toggle="tab" data-bs-target="#payout-request-content" role="tab"
							aria-controls="payout-request-content" aria-selected="false">
							Redemptons
							<div class="float-end"><i class="ti ti-chevron-right"></i></div>
						</a>

						<!-- Settings Tab Link -->
						<a href="#settings-content" class="list-group-item list-group-item-action border-0" id="settings-tab"
							data-bs-toggle="tab" data-bs-target="#settings-content" role="tab" aria-controls="settings-content"
							aria-selected="false">
							Settings
							<div class="float-end"><i class="ti ti-chevron-right"></i></div>
						</a>
					</div>
				</div>
			</div>

			<!-- Tab Content Area -->
			<div class="col-md-9">
				<div class="tab-content" id="nav-tabContent">
					<!-- Transaction Tab Content -->
					<div class="tab-pane fade show active" id="transaction-content" role="tabpanel" aria-labelledby="transaction-tab">
						@include('referral::admin.referrals._referrals')
					</div>

					<!-- Payout Request Tab Content -->
					<div class="tab-pane fade" id="payout-request-content" role="tabpanel" aria-labelledby="payout-request-tab">
								@include('referral::admin.referrals._redemptions')
					</div>

					<!-- Settings Tab Content -->
					<div class="tab-pane fade" id="settings-content" role="tabpanel" aria-labelledby="settings-tab">
						@include('referral::admin.referrals._settings')
					</div>
				</div>
			</div>
		</div>
	</div>
</div>

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
<script src="https://cdn.ckeditor.com/ckeditor5/41.1.0/classic/ckeditor.js"></script>
@endsection


@section('page-script')

<script>
	$(document).ready(function () {
		"use strict"
		
		// init table dom
		let Table = $("table[id=downliners-table]");
		let RedemptionsTable = $("table[id=redemptions-table]");

		// Initialize CKEditor on the guidelines textarea
		ClassicEditor.create(document.querySelector('#guideline'), {
            toolbar: [
                'heading', '|',
                'bold', 'italic', 'link', 'bulletedList', 'numberedList', '|',
                'blockQuote', 'insertTable', 'undo', 'redo'
            ]
        })
        .catch(error => {
            console.error(error);
        });
				
		// init list view datatable
		let dataListView = $('.datatables-basic-referrals').DataTable({
				"processing": true,
				"serverSide": true,
				"ajax": {
						"url": "{{ route('referral.admin.referrals.search') }}",
						"dataType": "json",
						"type": "POST",
						"data": {_token: "{{csrf_token()}}"}
				},
				"columns": [
						{"data": 'responsive_id', orderable: false, searchable: false},
						{"data": "uid"},
						{"data": "name", orderable: false, searchable: false},
						{"data": "upliner", orderable: false, searchable: false},
						{"data": "downliner_count", orderable: false, searchable: false},
						{"data": "available_bonus", searchable: false},
						{"data": "balance", orderable: true, searchable: false},
						{"data": "status", orderable: false, searchable: false},
						{"data": "created_at", visible: false},
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
								// Avatar image/badge, Name and post
								targets: 2,
								responsivePriority: 1,
								render: function (data, type, full) {
										var $user_img = full['avatar'],
												$name = full['isAdmin'] || full['super_user'] ? full['name'] : full['user_id'],
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
												'<small class="emp_name text-truncate fw-bold">' +
												$name +
												'</small>' +
												'<small class="emp_post text-truncate text-muted">' +
												$created_at +
												'</small>' +
												'</div>' +
												'</div>';
								}
						},
						{
								// Avatar image/badge, Name and post
								targets: 3,
								responsivePriority: 5,
								render: function (data, type, full) {
										var $user_img = full['referrer_avatar'],
												$hasReferrer = full['hasReferrer'],
												$name = full['referrer'],
												$created_at = full['created_at'];
										if(!$hasReferrer){
										    return `<i class="fas fa-user-slash text-muted" title="{{ __('referral::locale.referrals.no_upliner_set') }}"></i>`;
										}
										
										if ($user_img) {
												// For Avatar image
												var $output = '<img src="' + $user_img + '" alt="Avatar" width="32" height="32">';
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
												'<small class="emp_name text-truncate fw-bold">' +
												$name +
												'</small>' +
												'<small class="emp_post text-truncate text-muted">' +
												$created_at +
												'</small>' +
												'</div>' +
												'</div>';
								}
						},
							{
								// Avatar image/badge, Name and post
								targets: 5,
								responsivePriority: 4,
								render: function (data, type, full) {
										var $available_bonus = full['available_bonus'],
												$earned_bonus = full['earned_bonus']
										
										
										return '<div class="d-flex justify-content-left align-items-center">' +
												'<div class="d-flex flex-column">' +
													'<small class="emp_name text-truncate fw-bold">' + $available_bonus + '</small>' +
													'<small class="emp_post text-truncate text-muted">' + $earned_bonus + '</small>' +
												'</div>' +
												'</div>';
								}
						},
						{
								// Status
								targets: 7,
								responsivePriority: 5,
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
				order: [[8, "desc"]],
				displayLength: 10,
		});
		
		// init list view datatable
		let redemptionsDataListView = $('.datatables-basic-redemptions').DataTable({
            "processing": true,
            "serverSide": true,
            "ajax": {
                "url": "{{ route('referral.admin.redemptions.search') }}",
                "dataType": "json",
                "type": "POST",
                "data": {_token: "{{csrf_token()}}"}
            },
            "columns": [
                {"data": 'responsive_id', orderable: false, searchable: false},
                {"data": "uid"},
                // {"data": "request_id"},
                {"data": "recipient", orderable: false, searchable: false},
                {"data": "amount"},
                {"data": "moneytary_value"},
                {"data": "payout_method"},
                {"data": "status"},
                {"data": "created_at", visible: false},
                {"data": "actions", orderable: false, searchable: false}
            ],
            "searchDelay": 500,
            "columnDefs": [
                {
                    // For Responsive
                    "targets": 0,
                    "className": 'control',
                    "orderable": false,
                    "responsivePriority": 2
                },
                {
                    // For Checkboxes
                    "targets": 1,
                    "orderable": false,
                    "responsivePriority": 3,
                    "render": function (data) {
                        return (
                            '<div class="form-check"> <input class="form-check-input dt-checkboxes" type="checkbox" value="" id="' +
                            data +
                            '" /><label class="form-check-label" for="' +
                            data +
                            '"></label></div>'
                        );
                    },
                    "checkboxes": {
                        "selectAllRender":
                            '<div class="form-check"> <input class="form-check-input" type="checkbox" value="" id="checkboxSelectAll" /><label class="form-check-label" for="checkboxSelectAll"></label></div>',
                        "selectRow": true
                    }
                },
                {
                    // Avatar image/badge, Name and post
                    "targets": 2,
                    "responsivePriority": 1,
                    "render": function (data, type, full) {
                        var $user_img = full['avatar'],
                            $isAdmin = full['isAdmin'],
                            $name = $isAdmin ? full['name'] : full['user_id'],
                            $created_at = full['created_at'];
                        
                        if ($user_img) {
                            var $output = '<img src="' + $user_img + '" alt="Avatar" width="32" height="32">';
                        } else {
                            // For Avatar badge
                            var stateNum = full['status'];
                            var states = ['success', 'danger', 'warning', 'info', 'dark', 'primary', 'secondary'];
                            var $state = states[stateNum], $initials = $name.match(/\b\w/g) || [];
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
                            '<small class="emp_name text-truncate fw-bold text-sm">' +
                            $name +
                            '</small>' +
                            '</div>' +
                            '</div>';
                    }
                },
                {
                    "targets": 3,
                    "responsivePriority": 4,
                    "render": function (data, type, full) {
                        var $created_at = full['created_at'],
                            $payout_method = full['actual_payout_method'],
                            $amount = $payout_method == "{{ \Tonkra\Referral\Models\ReferralRedemption::PAYOUT_WALLET }}"
                                            ? '<span class="text-muted text-truncate text-decoration-line-through">' + full['amount'] + '</span>' 
                                            : '<span class="emp_name text-truncate fw-bold">' + full['amount'] + '</span>';
        
                        // Creates full output for row
                        return '<div class="d-flex justify-content-left align-items-center">' +
                            '<div class="d-flex flex-column">' +
                            $amount +
                            '<small class="emp_post text-truncate text-muted">' +
                            $created_at +
                            '</small>' +
                            '</div>' +
                            '</div>';
                    }
                },
                {
                    "targets": 4,
                    "responsivePriority": 5,
                    "render": function (data, type, full) {
                        var $payout_method = full['actual_payout_method'],
                            $moneytary_value = $payout_method == "{{ \Tonkra\Referral\Models\ReferralRedemption::PAYOUT_SMS }}"
                                                ? '<span class="text-muted text-truncate text-decoration-line-through">' + full['moneytary_value'] + '</span>' 
                                                : '<span class="emp_name text-truncate fw-bold">' + full['moneytary_value'] + '</span>';
        
                        return $moneytary_value;
                    }
                },
                {
                    // Status
                    "targets": 5,
                    "responsivePriority": 6,
                },
                {
                    // Actions
                    "targets": -1,
                    "title": '{{ __('locale.labels.actions') }}',
                    "orderable": false,
                    "render": function (data, type, full) {
                        let actions = '',
                            $actual_status = full['actual_status'],
                            $status = {label: '', btn_color: '', btn_border: '', status: ''},
                            $status_dropdown_list = '';
                        
                        if(full['actual_status'] != "{{ \Tonkra\Referral\Models\ReferralRedemption::STATUS_COMPLETED }}"){
                            $status.label = "{{ __('referral::locale.referral_redemptions.complete') }}";
                            $status.btn_color = "btn-success";
                            $status.btn_border = "btn-outline-success";
                            $status.status = "{{ \Tonkra\Referral\Models\ReferralRedemption::STATUS_COMPLETED }}";
                        }
                        
                        full['status_dropdown_list'].forEach(el => {
                            if(full['actual_status'] == el){
                                return;
                            }
                            $status_dropdown_list += `<li><a class="dropdown-item action-change-status text-capitalize" data-id="${full['uid']}"
                                    data-new-status="${el}"> ${el.replace(/_/g, ' ')} </a></li>`;
                        });
        
                        if($actual_status != '{{ \Tonkra\Referral\Models\ReferralRedemption::STATUS_COMPLETED }}'){
                            actions += `<div class="btn-group">
                                            <button class="btn btn-sm ${$status.btn_color} ${$status.btn_border} action-change-status" type="button"
                                                data-id="${full['uid']}" data-new-status="${$status.status}">
                                                ${$status.label}
                                            </button>
                                            <button type="button" class="btn btn-sm btn-default ${$status.btn_border} dropdown-toggle dropdown-toggle-split"
                                                data-bs-toggle="dropdown" aria-expanded="false" title="{{ __('locale.labels.change_status') }}">
                                                <span class="visually-hidden">Toggle Dropdown</span>
                                            </button>
                                            <ul class="dropdown-menu">
                                                ${$status_dropdown_list}
                                            </ul>
                                        </div>`;
                        }
                        
                        return actions;
                    }
                }
            ],
            "dom": '<"d-flex justify-content-between align-items-center mx-0 row"<"col-sm-12 col-md-6"l><"col-sm-12 col-md-6"f>>t<"d-flex justify-content-between mx-0 row"<"col-sm-12 col-md-6"i><"col-sm-12 col-md-6"p>>',
            "language": {
                "paginate": {
                    // remove previous & next text from pagination
                    "previous": '&nbsp;',
                    "next": '&nbsp;'
                },
                "sLengthMenu": "_MENU_",
                "sZeroRecords": "{{ __('locale.datatables.no_results') }}",
                "sSearch": "{{ __('locale.datatables.search') }}",
                "sProcessing": "{{ __('locale.datatables.processing') }}",
                "sInfo": "{{ __('locale.datatables.showing_entries', ['start' => '_START_', 'end' => '_END_', 'total' => '_TOTAL_']) }}"
            },
            "responsive": {
                "details": {
                    "display": $.fn.dataTable.Responsive.display.modal({
                        "header": function (row) {
                            let data = row.data();
                            return 'Details of ' + data['name'];
                        }
                    }),
                    "type": 'column',
                    "renderer": function (api, rowIdx, columns) {
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
            "aLengthMenu": [[10, 20, 50, 100, -1], [10, 20, 50, 100, 'All']],
            "select": {
                "style": "multi"
            },
            "order": [[7, "desc"]],
            "displayLength": 10
        });

		// On Change Status
		$("body").on('click', 'button.action-change-status, a.action-change-status', function (e) {
				e.stopPropagation();
				let id = $(this).data('id'), new_status = $(this).data('new-status'), $button = $(this);
				
				Swal.fire({
						title: "{{ __('locale.labels.are_you_sure') }}",
						text: "{{ __('locale.labels.able_to_revert') }}",
						icon: 'warning',
						showCancelButton: true,
						confirmButtonText: "{{ __('locale.labels.yes') }}",
						customClass: {
								confirmButton: 'btn btn-primary',
								cancelButton: 'btn btn-outline-danger ms-1'
						},
						buttonsStyling: false,
				}).then(function (result) {
						if (result.value) {
								$button.prepend('<i class="fas fa-spinner fa-spin me-2"></i>');
								$button.prop('disabled', true);

								$.ajax({
										url: "{{ url(config('referral.admin_path').'/redemptions')}}" + '/' + id + '/update-status',
										type: "PUT",
										data: {
												_token: "{{csrf_token()}}",
												status: new_status
										},
										success: function (data) {
												$button.find('i').remove();
												$button.prop('disabled', false);
												// showToast(data);
												location.reload();

										},
										error: function (reject) {
												$button.find('i').remove();
												$button.prop('disabled', false);
												if (reject.status === 422) {
														let errors = reject.responseJSON.errors;
														$.each(errors, function (key, value) {
																toastr['warning'](value[0], "{{__('locale.labels.attention')}}", {
																		closeButton: true,
																		positionClass: 'toast-top-right',
																		progressBar: true,
																		newestOnTop: true,
																		rtl: isRtl
																});
														});
												} else {
														toastr['warning'](reject.responseJSON.message, "{{__('locale.labels.attention')}}", {
																positionClass: 'toast-top-right',
																containerId: 'toast-top-right',
																progressBar: true,
																closeButton: true,
																newestOnTop: true
														});
												}
										},
										complete: function(data){
												$button.find('i').remove();
												$button.prop('disabled', false);
												$button.closest('.modal').modal('hide');
										}
								})
						}
				})
		});

		// $("body").on('click', '.copy', function (e) {
		// 	e.preventDefault()
		// 	copyToClipboard($(this).data('text'));
		// })
	});
</script>
@endsection