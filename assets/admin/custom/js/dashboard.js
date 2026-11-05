(function () {
    'use strict';
    function init() {
        var element = document.getElementById('admin-activity-chart');
        var dataElement = document.getElementById('admin-activity-data');
        if (element && dataElement && window.ApexCharts) {
            var data = JSON.parse(dataElement.textContent);
            var typography = window.getComputedStyle(element);
            var reducedMotion = window.matchMedia && window.matchMedia('(prefers-reduced-motion: reduce)').matches;
            var series, dates, step, maximum;
            function selectDays(days) {
                data.days = days;
                series = data.series.map(function (item) {
                    return { name: item.name, data: item.points.slice(-days).map(function (point) {
                        return { x: point.date, y: point.value };
                    }) };
                });
                dates = series.length ? series[0].data.map(function (point) { return point.x; }) : [];
                var peak = 0;
                series.forEach(function (item) { item.data.forEach(function (point) { peak = Math.max(peak, point.y); }); });
                step = Math.max(1, Math.ceil(peak / 5));
                maximum = Math.max(step, Math.ceil(peak / step) * step);
            }
            selectDays(data.days);
            var chart = new window.ApexCharts(element, {
                chart: { type: 'line', height: 235, fontFamily: typography.fontFamily, foreColor: typography.color,
                    toolbar: { show: false }, zoom: { enabled: false }, animations: { enabled: !reducedMotion, speed: 400, animateGradually: { enabled: false }, dynamicAnimation: { enabled: true, speed: 450 } }, parentHeightOffset: 0 },
                series: series,
                colors: data.series.map(function (item) { return item.color; }),
                stroke: { curve: 'smooth', width: 2.5 },
                markers: { size: data.days === 30 ? 2 : 3, strokeWidth: 2, hover: { sizeOffset: 2 } },
                legend: { position: 'top', horizontalAlign: 'center', fontFamily: typography.fontFamily, fontSize: typography.fontSize, itemMargin: { horizontal: 10, vertical: 6 } },
                grid: { borderColor: '#e6e9ed', padding: { left: 8, right: 12, top: 0, bottom: 0 } },
                xaxis: { type: 'category', tickPlacement: 'on',
                    labels: { rotate: 0, hideOverlappingLabels: false, formatter: function (value) {
                        var index = dates.indexOf(value);
                        var step = Math.ceil((dates.length - 1) / (window.innerWidth < 768 ? 3 : 5));
                        if (index !== 0 && index !== dates.length - 1 && index % step !== 0) return '';
                        return new Date(value + 'T00:00:00Z').toLocaleDateString('en-GB', { day: 'numeric', month: 'short', timeZone: 'UTC' });
                    }, style: { fontFamily: typography.fontFamily, fontSize: typography.fontSize } },
                    axisTicks: { show: false }, axisBorder: { color: '#d8dfe8' }, tooltip: { enabled: false } },
                yaxis: { min: 0, max: maximum, tickAmount: maximum / step, forceNiceScale: false, decimalsInFloat: 0, labels: { style: { fontFamily: typography.fontFamily, fontSize: typography.fontSize }, formatter: function (value) { return Math.round(value).toString(); } } },
                tooltip: { shared: true, intersect: false, x: { formatter: function (value, options) {
                    return new Date(dates[options.dataPointIndex] + 'T00:00:00Z').toLocaleDateString('en-GB', { day: 'numeric', month: 'short', year: 'numeric', timeZone: 'UTC' });
                } }, style: { fontFamily: typography.fontFamily, fontSize: typography.fontSize } },
                noData: { text: 'No activity in this period.' },
                responsive: [{ breakpoint: 768, options: { chart: { height: 260 }, legend: { itemMargin: { horizontal: 6, vertical: 4 } } } }]
            });
            chart.render();
            var periods = Array.prototype.slice.call(document.querySelectorAll('[data-chart-days]'));
            periods.forEach(function (button) {
                button.addEventListener('click', function () {
                    var days = Number(button.getAttribute('data-chart-days'));
                    if (days === data.days || (days !== 7 && days !== 30)) return;
                    selectDays(days);
                    periods.forEach(function (other) {
                        var active = other === button;
                        other.disabled = true;
                        other.classList.toggle('btn-primary', active);
                        other.classList.toggle('btn-default', !active);
                        other.setAttribute('aria-pressed', active ? 'true' : 'false');
                    });
                    element.setAttribute('aria-busy', 'true');
                    element.setAttribute('aria-label', 'Activity from ' + dates[0] + ' to ' + dates[dates.length - 1]);
                    document.querySelectorAll('[data-chart-date]').forEach(function (row) {
                        row.hidden = dates.indexOf(row.getAttribute('data-chart-date')) === -1;
                    });
                    Promise.resolve(chart.updateOptions({ series: series, markers: { size: days === 30 ? 2 : 3 }, yaxis: { max: maximum, tickAmount: maximum / step } }, false, !reducedMotion)).finally(function () {
                        periods.forEach(function (other) { other.disabled = false; });
                        element.removeAttribute('aria-busy');
                    });
                });
            });
        }
        var rows = Array.prototype.slice.call(document.querySelectorAll('[data-activity-type]'));
        var buttons = Array.prototype.slice.call(document.querySelectorAll('[data-activity-filter]'));
        var empty = document.querySelector('.admin-recent-empty');
        buttons.forEach(function (button) {
            button.addEventListener('click', function () {
                var filter = button.getAttribute('data-activity-filter');
                var shown = 0;
                rows.forEach(function (row) {
                    var match = filter === 'all' || row.getAttribute('data-activity-type') === filter;
                    row.hidden = !match || shown >= 10;
                    if (!row.hidden) shown++;
                });
                buttons.forEach(function (other) {
                    var active = other === button;
                    other.classList.toggle('btn-primary', active);
                    other.classList.toggle('btn-default', !active);
                    other.setAttribute('aria-pressed', active ? 'true' : 'false');
                });
                if (empty) empty.hidden = shown > 0;
            });
        });
    }
    if (document.readyState === 'loading') document.addEventListener('DOMContentLoaded', init);
    else init();
}());
