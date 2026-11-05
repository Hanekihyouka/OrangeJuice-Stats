/* global jQuery, moment */
(function ($) {
    'use strict';

    // Sortable stat tables. Cells carry their sort value in data-order.
    $('.js-stat-table').each(function () {
        var $table = $(this);
        $table.DataTable({
            paging: false,
            searching: false,
            info: false,
            autoWidth: false,
            order: [[Number($table.data('order-column') || 0), $table.data('order-dir') || 'desc']],
            columnDefs: [{ targets: 0, className: 'col-icon' }]
        });
    });

    // Tables inside hidden tabs need a re-layout once they become visible.
    $('a[data-toggle="tab"]').on('shown.bs.tab', function () {
        $.fn.dataTable.tables({ visible: true, api: true }).columns.adjust();
    });

    $('.js-select-on-focus').on('focus click', function () {
        this.select();
    });

    // Date range picker of the global page. Steam buckets global stats by UTC day.
    var $range = $('.js-daterange');
    if ($range.length && $.fn.daterangepicker) {
        var today = moment.utc($range.data('max'));
        var format = 'YYYY-MM-DD';
        $range.daterangepicker({
            opens: 'center',
            autoUpdateInput: false, // keep the server rendered label ("全部" for all time)
            startDate: moment.utc($range.data('start')),
            endDate: moment.utc($range.data('end')),
            minDate: moment.utc($range.data('min')),
            maxDate: today,
            locale: { format: format, separator: ' ~ ', applyLabel: '确定', cancelLabel: '取消', customRangeLabel: '自定义' },
            ranges: {
                '昨天': [today.clone().subtract(1, 'days'), today.clone().subtract(1, 'days')],
                '最近 7 天': [today.clone().subtract(6, 'days'), today],
                '最近 30 天': [today.clone().subtract(29, 'days'), today],
                '本月': [today.clone().startOf('month'), today],
                '上个月': [today.clone().subtract(1, 'month').startOf('month'), today.clone().subtract(1, 'month').endOf('month')],
                '今年': [today.clone().startOf('year'), today]
            }
        });
        // "apply" also fires when the chosen range equals the initial one (unlike the change callback).
        $range.on('apply.daterangepicker', function (event, picker) {
            window.location.search = $.param({ start: picker.startDate.format(format), end: picker.endDate.format(format) });
        });
    }
})(jQuery);
