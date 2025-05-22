@extends('layouts/contentLayoutMaster')

@section('title', __('locale.menu.Invoices'))

@section('vendor-style')
{{-- vendor css files --}}
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

            /* Hide responsive button on desktop */
            @media (min-width: 992px) {
                .dtr-control:before {
                    display: none !important;
                }
            }
		</style>
@endsection

@section('content')

<section id="vertical-tabs">
	<div class="row match-height">
		<div class="col-12">

			<ul class="nav nav-pills mb-2" role="tablist">

				<li class="nav-item border-1 border-primary rounded me-1 mb-1">
					<a class="nav-link text-uppercase p-half @if (old('tab') == 'invoices' || (request()->input('tab') == 'invoices' || request()->input('tab') == null)) active @endif"
						id="invoices-tab" data-bs-toggle="tab" aria-controls="invoices" href="#invoices" role="tab"
						aria-selected="true">
						<i class="fa fa-cart-shopping"></i> {{ __('referral::locale.labels.invoices') }}
					</a>
				</li>

				<li class="nav-item border-1 border-success rounded me-1 mb-1">
					<a class="nav-link text-uppercase p-half @if (old('tab') == 'redemptions' || request()->input('tab') == 'redemptions')) active @endif"
						id="redemptions-tab" data-bs-toggle="tab" aria-controls="redemptions" href="#redemptions" role="tab"
						aria-selected="false"> <i class="fa fa-star"></i> {{__('referral::locale.labels.redemptions')}} </a>
				</li>
			</ul>

			<div class="tab-content">

				<div class="tab-pane fade @if (old('tab') == 'invoices' || (request()->input('tab') == 'invoices' || request()->input('tab') == null))  show active @endif"
					id="invoices" role="tabpanel" aria-labelledby="invoices-tab">
					@include('referral::admin.Invoices._invoices')
				</div>

				<div class="tab-pane fade @if (old('tab') == 'redemptions' || request()->input('tab') == 'downliners')  show active @endif"
					id="redemptions" role="tabpanel" aria-labelledby="redemptions-tab">
					@include('referral::admin.Invoices._redemptions')
				</div>

			</div>
		</div>
	</div>
</section>



@endsection

@section('vendor-script')
{{-- vendor files --}}
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
{{-- Page js files --}}
<script>
	$(document).ready(function () {

            //show response message
            function showResponseMessage(data) {

                if (data.status === 'success') {
                    toastr['success'](data.message, '{{__('locale.labels.success')}}!!', {
                        closeButton: true,
                        positionClass: 'toast-top-right',
                        progressBar: true,
                        newestOnTop: true,
                        rtl: isRtl
                    });
                    dataListView.draw();
                } else if (data.status === 'error') {
                    toastr['error'](data.message, '{{ __('locale.labels.opps') }}!', {
                        closeButton: true,
                        positionClass: 'toast-top-right',
                        progressBar: true,
                        newestOnTop: true,
                        rtl: isRtl
                    });
                    dataListView.draw();
                } else {
                    toastr['warning']("{{__('locale.exceptions.something_went_wrong')}}", '{{ __('locale.labels.warning') }}!', {
                        closeButton: true,
                        positionClass: 'toast-top-right',
                        progressBar: true,
                        newestOnTop: true,
                        rtl: isRtl
                    });
                }
            }

            // init table dom
            let Table = $("table");

            // init list view datatable
            let dataListView = $('.datatables-basic').DataTable({

                "processing": true,
                "serverSide": true,
                "ajax": {
                    "url": "{{ route('admin.invoices.search') }}",
                    "dataType": "json",
                    "type": "POST",
                    "data": {_token: "{{csrf_token()}}"}
                },
                "columns": [
                    {"data": 'responsive_id', orderable: false, searchable: false},
                    {"data": "uid"},
                    // {"data": "uid"},
                    {"data": "id"},
                    // {"data": "created_at"},
                    {"data": "user_id"},
                    {"data": "type"},
                    {"data": "description"},
                    {"data": "amount"},
                    {"data": "status"},
                    {"data": "actions", orderable: false, searchable: false}
                ],

                searchDelay: 1500,
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
                        responsivePriority: 4,
                        render: function (data, type, full) {
                            var $created_at = full['created_at'],
                                $id = full['id']
                            // Creates full output for row
                            return '<div class="d-flex justify-content-left align-items-center">' +
                                '<div class="d-flex flex-column">' +
                                '<span class="emp_name text-truncate fw-bold">' +
                                $id +
                                '</span>' +
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
                        responsivePriority: 1,
                        render: function (data, type, full) {
                            var $user_img = full['avatar'],
                                $email = full['email'],
                                $name = full['user_id'];
                            if ($user_img) {
                                // For Avatar image
                                var $output =
                                    '<img src="' + $user_img + '" alt="Avatar" width="32" height="32">';
                            } else {
                                // For Avatar badge
                                var stateNum = full['status'];
                                var states = ['success', 'danger', 'warning', 'info', 'dark', 'primary', 'secondary'];
                                var $state = states[stateNum],
                                    $name = full['user_id'],
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
                                $email +
                                '</small>' +
                                '</div>' +
                                '</div>';
                        }
                    },
                    {
                        // Actions
                        targets: -1,
                        title: '{{ __('locale.labels.actions') }}',
                        orderable: false,
                        render: function (data, type, full) {
                            let $actions = '';

                            if (full['get_status'] === 'pending') {
                                $actions = '<span class="action-approve text-warning cursor-pointer pe-1" data-bs-toggle="tooltip" data-placement="top" title="' + full['status_locale'] + '" data-id=' + full['uid'] + '>' +
                                    feather.icons['check-square'].toSvg({class: 'font-medium-4'}) +
                                    '</span>';
                            }

                            $actions += '<span class="action-delete text-danger pe-1 cursor-pointer" data-id=' + full['delete'] + '>' +
                                feather.icons['trash'].toSvg({class: 'font-medium-4'}) +
                                '</span>' +
                                '<a href="' + full['edit'] + '" class="text-primary  pe-1">' +
                                feather.icons['eye'].toSvg({class: 'font-medium-4'}) +
                                '</a>';


                            return ($actions);

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


            // On Approve
            Table.delegate(".action-approve", "click", function (e) {
                e.stopPropagation();
                let id = $(this).data('id');
                Swal.fire({
                    title: 'Are you sure?',
                    text: 'You are about to approve this invoice.',
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonText: 'Yes, approve it!',
                    cancelButtonText: 'Cancel',
                    showLoaderOnConfirm: true,
                    preConfirm: () => {
                        return new Promise((resolve) => {
                            $.ajax({
                                url: "{{ url(config('app.admin_path').'/invoices')}}" + '/' + id + '/approve',
                                type: "POST",
                                data: {
                                    _token: "{{csrf_token()}}"
                                },
                                success: function (data) {
                                    resolve(data);
                                },
                                error: function (reject) {
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
                                }
                            });
                        });
                    }
                }).then((result) => {
                    if (result.isConfirmed) {
                        showResponseMessage(result.value);
                    }
                });
            });


            // On Delete
            Table.delegate(".action-delete", "click", function (e) {
                e.stopPropagation();
                let id = $(this).data('id');
                Swal.fire({
                    title: "{{ __('locale.labels.are_you_sure') }}",
                    text: "{{ __('locale.labels.able_to_revert') }}",
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonText: "{{ __('locale.labels.delete_it') }}",
                    customClass: {
                        confirmButton: 'btn btn-primary',
                        cancelButton: 'btn btn-outline-danger ms-1'
                    },
                    buttonsStyling: false,
                }).then(function (result) {
                    if (result.value) {
                        $.ajax({
                            url: "{{ url(config('app.admin_path').'/invoices')}}" + '/' + id,
                            type: "POST",
                            data: {
                                _method: 'DELETE',
                                _token: "{{csrf_token()}}"
                            },
                            success: function (data) {
                                showResponseMessage(data);
                            },
                            error: function (reject) {
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
                            }
                        })
                    }
                })
            });


            //Bulk Delete
            $(".bulk-delete").on('click', function (e) {

                e.preventDefault();

                Swal.fire({
                    title: "{{__('locale.labels.are_you_sure')}}",
                    text: "{{__('locale.labels.able_to_revert')}}",
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonText: "{{__('locale.labels.delete_selected')}}",
                    customClass: {
                        confirmButton: 'btn btn-primary',
                        cancelButton: 'btn btn-outline-danger ms-1'
                    },
                    buttonsStyling: false,
                }).then(function (result) {
                    if (result.value) {
                        let invoice_ids = [];
                        let rows_selected = dataListView.column(1).checkboxes.selected();

                        $.each(rows_selected, function (index, rowId) {
                            invoice_ids.push(rowId)
                        });

                        if (invoice_ids.length > 0) {

                            $.ajax({
                                url: "{{ route('admin.invoices.batch_action') }}",
                                type: "POST",
                                data: {
                                    _token: "{{csrf_token()}}",
                                    action: 'destroy',
                                    ids: invoice_ids
                                },
                                success: function (data) {
                                    showResponseMessage(data);
                                },
                                error: function (reject) {
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
                                            closeButton: true,
                                            positionClass: 'toast-top-right',
                                            progressBar: true,
                                            newestOnTop: true,
                                            rtl: isRtl
                                        });
                                    }
                                }
                            })
                        } else {
                            toastr['warning']("{{__('locale.labels.at_least_one_data')}}", "{{__('locale.labels.attention')}}", {
                                closeButton: true,
                                positionClass: 'toast-top-right',
                                progressBar: true,
                                newestOnTop: true,
                                rtl: isRtl
                            });
                        }

                    }
                })
            });


        });

</script>
@endsection