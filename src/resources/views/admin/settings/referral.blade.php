@extends('layouts/contentLayoutMaster')

@section('title', __('referral::locale.labels.referral_settings'))


@section('vendor-style')
  <link rel="stylesheet" href="{{ asset(mix('vendors/css/tables/datatable/dataTables.bootstrap5.min.css')) }}">
  <link rel="stylesheet" href="{{ asset(mix('vendors/css/tables/datatable/responsive.bootstrap5.min.css')) }}">
  <link rel="stylesheet" href="{{ asset(mix('vendors/css/tables/datatable/buttons.bootstrap5.min.css')) }}">
  <link rel="stylesheet" href="{{ asset(mix('vendors/css/extensions/sweetalert2.min.css')) }}">
@endsection


@section('content')
  <section id="nav-justified">
    <div class="row">
      <div class="col-sm-12">
        <div class="card overflow-hidden">
          <div class="card-content">
            <div class="card-body">
              <div class="col-md-6 col-12">
                <div class="form-body">
                  <form class="form form-vertical" action="{{ route('referral.admin.setting.store') }}" method="post">
                    @csrf
                    <div class="row">
                      <div class="col-12">
                        <div class="mb-1">
                          <label for="status"
                            class="form-label required">{{ __('referral::locale.labels.status') }}</label>
                          <select class="form-select" id="status" name="status">
                            <option value="1" @if ((bool) config('referral.status')) selected @endif>
                              {{ (bool) config('referral.status') ? __('referral::locale.labels.enabled') : __('referral::locale.labels.enable') }}
                            </option>
                            <option value="0" @if ((bool) !config('referral.status')) selected @endif>
                              {{ (bool) !config('referral.status') ? __('referral::locale.labels.disabled') : __('referral::locale.labels.disable') }}
                            </option>
                          </select>
                        </div>
                        @error('status')
                          <p><small class="text-danger">{{ $message }}</small></p>
                        @enderror
                      </div>

                      <div class="col-12">
                        <div class="mb-1">
                          <label for="bonus" class="form-label required">{{ __('referral::locale.referrals.bonus') }} (%)</label>
                          <input type="number" id="bonus" name="bonus" class="form-control"
                            value="{{ config('referral.bonus') }}">
                          @error('bonus')
                            <p><small class="text-danger">{{ $message }}</small></p>
                          @enderror
                        </div>
                      </div>

                      <div class="col-12">
                        <div class="mb-1">
                          <label for="email_notification"
                            class="form-label required">{{ __('referral::locale.referrals.email_notification') }}</label>
                          <select class="form-select" id="email_notification" name="email_notification">
                            <option value="1" @if ((bool) config('referral.email_notification')) selected @endif>
                              {{ (bool) config('referral.email_notification') ? __('referral::locale.labels.enabled') : __('referral::locale.labels.enable') }}
                            </option>
                            <option value="0" @if ((bool) !config('referral.email_notification')) selected @endif>
                              {{ (bool) !config('referral.email_notification') ? __('referral::locale.labels.disabled') : __('referral::locale.labels.disable') }}
                            </option>
                          </select>
                        </div>
                        @error('email_notification')
                          <p><small class="text-danger">{{ $message }}</small></p>
                        @enderror
                      </div>

                      <div class="col-12">
                        <div class="mb-1">
                          <label for="sms_notification"
                            class="form-label required">{{ __('referral::locale.referrals.sms_notification') }}</label>
                          <select class="form-select" id="sms_notification" name="sms_notification">
                            <option value="1" @if ((bool) config('referral.sms_notification')) selected @endif>
                              {{ (bool) config('referral.sms_notification') ? __('referral::locale.labels.enabled') : __('referral::locale.labels.enable') }}
                            </option>
                            <option value="0" @if ((bool) !config('referral.email_notification')) selected @endif>
                              {{ (bool) !config('referral.email_notification') ? __('referral::locale.labels.disabled') : __('referral::locale.labels.disable') }}
                            </option>
                          </select>
                        </div>
                        @error('sms_notification')
                          <p><small class="text-danger">{{ $message }}</small></p>
                        @enderror
                      </div>

                      <div class="col-12">
                        <div class="mb-1">
                          <label for="default_senderid" class="form-label required">{{ __('referral::locale.referrals.default_senderid') }}</label>
                          <input type="text" id="default_senderid" name="default_senderid" class="form-control" value="{{ config('referral.default_senderid') }}">
                          @error('default_senderid')
                            <p><small class="text-danger">{{ $message }}</small></p>
                          @enderror
                        </div>
                      </div>

                      <div class="col-12 mt-2">
                        <button type="submit" class="btn btn-primary mb-1">
                          <i data-feather="save"></i> {{ __('referral::locale.buttons.save') }}
                        </button>
                      </div>

                    </div>
                  </form>
                </div>
              </div>


            </div>
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
    // $(document).ready(function() {
    //   "use strict"

    //   // init table dom
    //   let Table = $("table[id=downliners-table]");

    //   // init list view datatable
    //   let dataListView = $('.datatables-basic-downliners').DataTable({
    //     "processing": true,
    //     "serverSide": true,
    //     "ajax": {
    //       "url": "{{ route('referral.customer.downliners.search') }}",
    //       "dataType": "json",
    //       "type": "POST",
    //       "data": {
    //         _token: "{{ csrf_token() }}"
    //       }
    //     },
    //     "columns": [{
    //         "data": 'responsive_id',
    //         orderable: false,
    //         searchable: false
    //       },
    //       {
    //         "data": "uid"
    //       },
    //       {
    //         "data": "uid"
    //       },
    //       {
    //         "data": "name"
    //       },
    //       {
    //         "data": "email",
    //         orderable: false,
    //         searchable: true
    //       },
    //       {
    //         "data": "balance",
    //         orderable: false,
    //         searchable: false
    //       },
    //       {
    //         "data": "status",
    //         orderable: false,
    //         searchable: false
    //       },
    //       {
    //         "data": "action",
    //         orderable: false,
    //         searchable: false
    //       }
    //     ],
    //     searchDelay: 500,
    //     columnDefs: [{
    //         // For Responsive
    //         className: 'control',
    //         orderable: false,
    //         responsivePriority: 2,
    //         targets: 0
    //       },
    //       {
    //         // For Checkboxes
    //         targets: 1,
    //         orderable: false,
    //         responsivePriority: 3,
    //         render: function(data) {
    //           return (
    //             '<div class="form-check"> <input class="form-check-input dt-checkboxes" type="checkbox" value="" id="' +
    //             data +
    //             '" /><label class="form-check-label" for="' +
    //             data +
    //             '"></label></div>'
    //           );
    //         },
    //         checkboxes: {
    //           selectAllRender: '<div class="form-check"> <input class="form-check-input" type="checkbox" value="" id="checkboxSelectAll" /><label class="form-check-label" for="checkboxSelectAll"></label></div>',
    //           selectRow: true
    //         }
    //       },
    //       {
    //         targets: 2,
    //         visible: false
    //       },
    //       {
    //         // Avatar image/badge, Name and post
    //         targets: 3,
    //         responsivePriority: 1,
    //         render: function(data, type, full) {
    //           var $user_img = full['avatar'],
    //             $name = full['name'],
    //             $created_at = full['created_at'],
    //             $email = full['email'];
    //           if ($user_img) {
    //             // For Avatar image
    //             var $output =
    //               '<img src="' + $user_img + '" alt="Avatar" width="32" height="32">';
    //           } else {
    //             // For Avatar badge
    //             var stateNum = full['status'];
    //             var states = ['success', 'danger', 'warning', 'info', 'dark', 'primary', 'secondary'];
    //             var $state = states[stateNum],
    //               $name = full['name'],
    //               $initials = $name.match(/\b\w/g) || [];
    //             $initials = (($initials.shift() || '') + ($initials.pop() || '')).toUpperCase();
    //             $output = '<span class="avatar-content">' + $initials + '</span>';
    //           }
    //           var colorClass = $user_img === '' ? ' bg-light-' + $state + ' ' : '';
    //           // Creates full output for row
    //           return '<div class="d-flex justify-content-left align-items-center">' +
    //             '<div class="avatar ' +
    //             colorClass +
    //             ' me-1">' +
    //             $output +
    //             '</div>' +
    //             '<div class="d-flex flex-column">' +
    //             '<span class="emp_name text-truncate fw-bold">' +
    //             $name +
    //             '</span>' +
    //             '<small class="emp_post text-truncate text-muted">' +
    //             $created_at +
    //             '</small>' +
    //             '</div>' +
    //             '</div>';
    //         }
    //       },
    //       {
    //         // Status
    //         targets: 6,
    //         responsivePriority: 4,
    //         render: function(data, type, full) {
    //           var $status = full['status'],
    //             $status_color = full['status_color'],
    //             $status_label = full['status_label'];

    //           // Creates full output for row
    //           return '<a nohref class="' + $status_color +
    //             ' px-1" data-bs-toggle="tooltip" data-bs-placement="top" title="' + $status_label + '">' +
    //             feather.icons[$status].toSvg({
    //               class: 'font-medium-5'
    //             }) +
    //             '</a>';
    //         }
    //       },
    //       {
    //         // Actions
    //         targets: -1,
    //         title: '{{ __('referral::locale.labels.actions') }}',
    //         orderable: false,
    //         render: function(data, type, full) {
    //           var $super_user = '';

    //           return (
    //             $super_user +
    //             '<a class="copy text-primary pe-1" data-text="' + full['copy'] +
    //             '" data-bs-toggle="tooltip" data-bs-placement="top" title="' + full['copy_label'] + '" >' +
    //             feather.icons['copy'].toSvg({
    //               class: 'font-medium-4'
    //             }) +
    //             '</a>' //+
    //             // '<a nohref="' + full['top_up'] + '" class="text-success pe-1" data-bs-toggle="tooltip" data-bs-placement="top" title="' + full['top_up_label'] + '" onclick="alert(\'Under construction\')">' +
    //             // feather.icons['trending-up'].toSvg({class: 'font-medium-4'}) +
    //             // '</a>'+
    //             // '<a nohref="' + full['report'] + '" class="text-danger" data-bs-toggle="tooltip" data-bs-placement="top" title="' + full['report_label'] + '" onclick="alert(\'Under construction\')">' +
    //             // feather.icons['flag'].toSvg({class: 'font-medium-4'}) +
    //             // '</a>'
    //           );
    //         }
    //       }
    //     ],
    //     dom: '<"d-flex justify-content-between align-items-center mx-0 row"<"col-sm-12 col-md-6"l><"col-sm-12 col-md-6"f>>t<"d-flex justify-content-between mx-0 row"<"col-sm-12 col-md-6"i><"col-sm-12 col-md-6"p>>',
    //     language: {
    //       paginate: {
    //         // remove previous & next text from pagination
    //         previous: '&nbsp;',
    //         next: '&nbsp;'
    //       },
    //       sLengthMenu: "_MENU_",
    //       sZeroRecords: "{{ __('referral::locale.datatables.no_results') }}",
    //       sSearch: "{{ __('referral::locale.datatables.search') }}",
    //       sProcessing: "{{ __('referral::locale.datatables.processing') }}",
    //       sInfo: "{{ __('referral::locale.datatables.showing_entries', ['start' => '_START_', 'end' => '_END_', 'total' => '_TOTAL_']) }}"
    //     },
    //     responsive: {
    //       details: {
    //         display: $.fn.dataTable.Responsive.display.modal({
    //           header: function(row) {
    //             let data = row.data();
    //             return 'Details of ' + data['name'];
    //           }
    //         }),
    //         type: 'column',
    //         renderer: function(api, rowIdx, columns) {
    //           let data = $.map(columns, function(col) {
    //             return col.title !==
    //               '' // ? Do not show row in modal popup if title is blank (for check box)
    //               ?
    //               '<tr data-dt-row="' +
    //               col.rowIdx +
    //               '" data-dt-column="' +
    //               col.columnIndex +
    //               '">' +
    //               '<td>' +
    //               col.title +
    //               ':' +
    //               '</td> ' +
    //               '<td>' +
    //               col.data +
    //               '</td>' +
    //               '</tr>' :
    //               '';
    //           }).join('');
    //           return data ? $('<table class="table"/>').append('<tbody>' + data + '</tbody>') : false;
    //         }
    //       }
    //     },
    //     aLengthMenu: [
    //       [10, 20, 50, 100],
    //       [10, 20, 50, 100]
    //     ],
    //     select: {
    //       style: "multi"
    //     },
    //     order: [
    //       [2, "desc"]
    //     ],
    //     displayLength: 10,
    //   });

    //   $("body").on('click', '.copy', function(e) {
    //     e.preventDefault()
    //     copyToClipboard($(this).data('text'));
    //   })
    // });
  </script>
@endsection
