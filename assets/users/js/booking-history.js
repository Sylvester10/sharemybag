// One responsive table, one details dialog, and the existing recovery action per row.
document.addEventListener('DOMContentLoaded', function () {
    'use strict';
    const root = document.getElementById('booking-history');
    const dialog = document.getElementById('history-booking-details');
    if (!root || !dialog || !window.bootstrap || !bootstrap.Modal) return;
    root.classList.add('history-enhanced');

    // DataTables caches its search strings. Refresh the status cell after timer/AJAX
    // changes so a newly canceled booking can be found on any page.
    if (window.jQuery && jQuery.fn.dataTable && jQuery.fn.dataTable.isDataTable('#default_order')) {
        const table = jQuery('#default_order').DataTable();
        table.rows().nodes().toArray().forEach(function (row) {
            const status = row.querySelector('[data-history-status]');
            if (!status) return;
            const cell = status.closest('td');
            const reference = row.querySelector('[data-history-reference]').getAttribute('data-history-reference');
            const routeSource = row.querySelector('[data-history-route]');
            const route = routeSource.textContent.replace(/\s+/g, ' ').trim() + ' ' + routeSource.getAttribute('data-history-route-search');
            const space = row.querySelector('[data-history-space]').textContent;
            const amount = row.querySelector('.history-card-meta strong').textContent;
            new MutationObserver(function () {
                cell.setAttribute('data-search', [reference, route, space, space.replace(/\s+(kg|pc)\b/g, '$1'), amount, status.textContent].join(' '));
                table.cell(cell).invalidate('dom');
                if (table.search()) table.draw(false);
            }).observe(status, {subtree: true, childList: true, characterData: true});
        });
    }

    let statusObserver = null;
    dialog.addEventListener('show.bs.modal', function (event) {
        const trigger = event.relatedTarget;
        const row = trigger && trigger.closest('[data-history-row]');
        if (!row || !root.contains(row)) { event.preventDefault(); return; }
        if (statusObserver) statusObserver.disconnect();
        dialog.querySelector('[data-history-target="reference"]').textContent = trigger.getAttribute('data-history-reference');
        function copySection(name) {
            const source = row.querySelector('[data-history-' + name + ']');
            const target = dialog.querySelector('[data-history-target="' + name + '"]');
            target.replaceChildren();
            if (source) {
                for (const child of source.childNodes) target.appendChild(child.cloneNode(true));
            }
        }
        ['route', 'traveller', 'agent', 'items', 'status', 'date', 'invoice'].forEach(copySection);
        const footer = dialog.querySelector('.modal-footer');
        footer.hidden = !footer.querySelector('a');
        const invoice = footer.querySelector('a');
        if (invoice) invoice.classList.add('history-details-invoice');
        dialog.querySelector('.modal-body').scrollTop = 0;
        // The same timer/AJAX status remains visible when expiry occurs with details open.
        statusObserver = new MutationObserver(function () { copySection('status'); });
        statusObserver.observe(row.querySelector('[data-history-status]'), {subtree: true, childList: true, attributes: true, characterData: true});
    });
    dialog.addEventListener('hidden.bs.modal', function () {
        if (statusObserver) statusObserver.disconnect();
        statusObserver = null;
    });

    const mobile = window.matchMedia('(max-width: 767.98px)');
    root.addEventListener('click', function (event) {
        if (!mobile.matches || event.target.closest('a, button, input, select, textarea, label')) return;
        const row = event.target.closest('[data-history-row]');
        if (row) row.querySelector('[data-history-open]').click();
    });
    mobile.addEventListener('change', function () {
        if (!mobile.matches) {
            const instance = bootstrap.Modal.getInstance(dialog);
            if (instance) instance.hide();
        }
        if (window.jQuery && jQuery.fn.dataTable && jQuery.fn.dataTable.isDataTable('#default_order')) {
            jQuery('#default_order').DataTable().columns.adjust();
        }
    });
});
