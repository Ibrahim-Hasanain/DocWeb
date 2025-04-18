@extends('admin.layouts.app')

@section('panel')
<div class="row gy-4">
    <div class="col-xxl-3 col-sm-6">
        <x-widget link="{{ route('admin.department.index') }}" style="6" icon="las la-layer-group f-size--56"
            title="Total Departments" value="{{ $widget['total_departments'] }}" bg="primary" />
    </div><!-- dashboard-w1 end -->
    <div class="col-xxl-3 col-sm-6">
        <x-widget link="{{ route('admin.department.location') }}"   style="6" icon="las la-street-view f-size--56"
            title="Total Department Locations" value="{{ $widget['total_locations'] }}" bg="success" />
    </div><!-- dashboard-w1 end -->
    <div class="col-xxl-3 col-sm-6">
        <x-widget link="{{ route('admin.appointment.new') }}"  style="6" icon="las la-hands-helping f-size--56"
            title="Total New Appointments" value="{{ $widget['total_new_appointments'] }}" bg="red" />
    </div><!-- dashboard-w1 end -->
    <div class="col-xxl-3 col-sm-6">
        <x-widget link="{{ route('admin.appointment.done') }}"  style="6" icon="las la-handshake f-size--56"
            title="Total Done Appointments" value="{{ $widget['total_done_appointments'] }}" bg="12" />
    </div><!-- dashboard-w1 end -->
</div><!-- row end-->

<div class="row gy-4 mt-2">
    <div class="col-xxl-3 col-sm-6">
        <x-widget style="2" link="{{ route('admin.doctor.index') }}" style="7" type="2"  icon="las la-stethoscope" title="Total Doctors"
            value="{{ $widget['total_doctors'] }}" color="success" />
    </div>
    <div class="col-xxl-3 col-sm-6">
        <x-widget style="2" link="{{ route('admin.staff.index') }}"  style="7" type="2" icon="las la-users" title="Total Staff"
            value="{{ $widget['total_staff'] }}" color="warning" />
    </div>
    <div class="col-xxl-3 col-sm-6">
        <x-widget style="2" link="{{ route('admin.assistant.index') }}"  style="7" type="2" icon="las la-user-friends"
            title="Total Assistants" value="{{ $widget['total_assistants'] }}" color="danger" />
    </div>
    <div class="col-xxl-3 col-sm-6">
        <x-widget style="2" link="{{ route('admin.ticket.pending') }}" style="7" type="2" icon="la la-ticket"
            title="Pending Support Tickets" value="{{ $widget['total_pending_support_tickets'] }}" color="primary" />
    </div>
</div><!-- row end-->

<div class="row gy-4 mt-2">
    <div class="col-xxl-3 col-sm-6">
        <x-widget style="2" link="{{ route('admin.deposit.list') }}" style="7" icon="fas fa-hand-holding-usd" icon_style="false"
            title="Total Deposited" value="{{ showAmount($deposit['total_deposit_amount']) }}"
            color="success" />
    </div><!-- dashboard-w1 end -->
    <div class="col-xxl-3 col-sm-6">
        <x-widget style="2" link="{{ route('admin.deposit.pending') }}" style="7" icon="fas fa-spinner" icon_style="false"
            title="Pending Deposits" value="{{ $deposit['total_deposit_pending'] }}" color="warning" />
    </div><!-- dashboard-w1 end -->
    <div class="col-xxl-3 col-sm-6">
        <x-widget style="2" link="{{ route('admin.deposit.rejected') }}" style="7" icon="fas fa-ban" icon_style="false"
            title="Rejected Deposits" value="{{ $deposit['total_deposit_rejected'] }}" color="warning" />
    </div><!-- dashboard-w1 end -->
    <div class="col-xxl-3 col-sm-6">
        <x-widget style="2" link="{{ route('admin.deposit.list') }}" style="7" icon="fas fa-percentage" icon_style="false"
            title="Deposited Charge" value="{{ showAmount($deposit['total_deposit_charge']) }}"
            color="primary" />
    </div><!-- dashboard-w1 end -->
</div><!-- row end-->



    <div class="row mb-none-30 mt-30">
        <div class="col-xl-6 mb-30">
            <div class="card">
              <div class="card-body">
                <div class="d-flex flex-wrap justify-content-between">
                    <h5 class="card-title">@lang('Doctor Online Payment Report')</h5>

                    <div id="dwDatePicker" class="border p-1 cursor-pointer rounded">
                        <i class="la la-calendar"></i>&nbsp;
                        <span></span> <i class="la la-caret-down"></i>
                    </div>
                </div>
                <div id="dwChartArea"> </div>
              </div>
            </div>
          </div>
        <div class="col-xl-6 mb-30">
            <div class="card">
              <div class="card-body">
                <div class="d-flex flex-wrap justify-content-between">
                    <h5 class="card-title">@lang('Appointments Report')</h5>

                    <div id="trxDatePicker" class="border p-1 cursor-pointer rounded">
                        <i class="la la-calendar"></i>&nbsp;
                        <span></span> <i class="la la-caret-down"></i>
                    </div>
                </div>

                <div id="transactionChartArea"></div>
              </div>
            </div>
        </div>
    </div>

  


@endsection


@push('script-lib')
    <script src="{{ asset('assets/admin/js/vendor/apexcharts.min.js') }}"></script>
    <script src="{{ asset('assets/admin/js/vendor/chart.js.2.8.0.js') }}"></script>
    <script src="{{ asset('assets/admin/js/moment.min.js') }}"></script>
    <script src="{{ asset('assets/admin/js/daterangepicker.min.js') }}"></script>
    <script src="{{ asset('assets/admin/js/charts.js') }}"></script>
@endpush

@push('style-lib')
    <link rel="stylesheet" type="text/css" href="{{ asset('assets/admin/css/daterangepicker.css') }}">
@endpush

@push('script')
    <script>
        "use strict";

        const start = moment().subtract(14, 'days');
        const end = moment();

        const dateRangeOptions = {
            startDate: start,
            endDate: end,
            ranges: {
                'Today': [moment(), moment()],
                'Yesterday': [moment().subtract(1, 'days'), moment().subtract(1, 'days')],
                'Last 7 Days': [moment().subtract(6, 'days'), moment()],
                'Last 15 Days': [moment().subtract(14, 'days'), moment()],
                'Last 30 Days': [moment().subtract(30, 'days'), moment()],
                'This Month': [moment().startOf('month'), moment().endOf('month')],
                'Last Month': [moment().subtract(1, 'month').startOf('month'), moment().subtract(1, 'month').endOf('month')],
                'Last 6 Months': [moment().subtract(6, 'months').startOf('month'), moment().endOf('month')],
                'This Year': [moment().startOf('year'), moment().endOf('year')],
            },
            maxDate: moment()
        }

        const changeDatePickerText = (element, startDate, endDate) => {
            $(element).html(startDate.format('MMMM D, YYYY') + ' - ' + endDate.format('MMMM D, YYYY'));
        }

        let dwChart = barChart(
            document.querySelector("#dwChartArea"),
            @json(__(gs('cur_text'))),
            [{
                    name: 'Deposited',
                    data: []
                }
              
            ],
            [],
        );

        let trxChart = lineChart(
            document.querySelector("#transactionChartArea"),
            [{
                    name: "Total Appointments",
                    data: []
                }
              
            ],
            []
        );


        const depositWithdrawChart = (startDate, endDate) => {

            const data = {
                start_date: startDate.format('YYYY-MM-DD'),
                end_date: endDate.format('YYYY-MM-DD')
            }

            const url = @json(route('admin.chart.deposit.withdraw'));

            $.get(url, data,
                function(data, status) {
                    if (status == 'success') {
                        dwChart.updateSeries(data.data);
                        dwChart.updateOptions({
                            xaxis: {
                                categories: data.created_on,
                            }
                        });
                    }
                }
            );
        }

        const transactionChart = (startDate, endDate) => {

            const data = {
                start_date: startDate.format('YYYY-MM-DD'),
                end_date: endDate.format('YYYY-MM-DD')
            }

            const url = @json(route('admin.chart.transaction'));


            $.get(url, data,
                function(data, status) {
                    if (status == 'success') {

                        trxChart.updateSeries(data.data);
                        trxChart.updateOptions({
                            xaxis: {
                                categories: data.date,
                            },
                            yaxis: {
                labels: {
                    formatter: function(val) {
                        return val.toFixed(0);
                    }
                },
            },
            
                        });
                    }
                }
            );
        }



        $('#dwDatePicker').daterangepicker(dateRangeOptions, (start, end) => changeDatePickerText('#dwDatePicker span', start, end));
        $('#trxDatePicker').daterangepicker(dateRangeOptions, (start, end) => changeDatePickerText('#trxDatePicker span', start, end));

        changeDatePickerText('#dwDatePicker span', start, end);
        changeDatePickerText('#trxDatePicker span', start, end);

        depositWithdrawChart(start, end);
        transactionChart(start, end);

        $('#dwDatePicker').on('apply.daterangepicker', (event, picker) => depositWithdrawChart(picker.startDate, picker.endDate));
        $('#trxDatePicker').on('apply.daterangepicker', (event, picker) => transactionChart(picker.startDate, picker.endDate));

 
    </script>
@endpush
@push('style')
    <style>
        .apexcharts-menu {
            min-width: 120px !important;
        }
    </style>
@endpush
