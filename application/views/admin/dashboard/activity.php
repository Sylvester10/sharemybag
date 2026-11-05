<?php
$series = array();
$labels = array('users' => 'New users', 'bookings' => 'Completed bookings', 'travellers' => 'Traveller submissions');
$colors = array('users' => '#00b6a0', 'bookings' => '#087cff', 'travellers' => '#8153c6');
foreach ($overview['sources'] as $type => $source) {
    $points = array();
    foreach (($overview['chart_daily'] ?? $overview['daily']) as $date => $day) $points[] = array('date' => $date, 'value' => (int) $day['values'][$type]);
    $series[] = array('name' => $labels[$type], 'color' => $colors[$type], 'points' => $points);
}
$relativeTime = function ($date) {
    $timestamp = strtotime((string) $date);
    if (!$timestamp) return 'Not available';
    $seconds = max(0, time() - $timestamp);
    foreach (array(86400 => 'day', 3600 => 'hour', 60 => 'minute') as $unit => $label) {
        $count = (int) floor($seconds / $unit);
        if ($count > 0) return $count . ' ' . $label . ($count === 1 ? '' : 's') . ' ago';
    }
    return 'Just now';
};
?>
<div class="row admin-dashboard-row">
    <div class="col-md-8 col-sm-12">
        <section class="panel panel-default admin-dashboard-panel">
            <div class="panel-heading admin-dashboard-heading">
                <h4>Activity Overview</h4>
                <div class="btn-group" role="group" aria-label="Activity period">
                    <?php foreach (array(7, 30) as $days): ?>
                    <button type="button" class="btn btn-sm <?= $overview['days'] === $days ? 'btn-primary' : 'btn-default' ?>" data-chart-days="<?= $days ?>" aria-pressed="<?= $overview['days'] === $days ? 'true' : 'false' ?>"><?= $days ?> days</button>
                    <?php endforeach; ?>
                </div>
            </div>
            <div class="panel-body admin-dashboard-chart-body">
                <?php if (isset($overview['sources']['bookings'])): ?><p class="text-muted">Completed bookings are grouped by booking creation date.</p><?php endif; ?>
                <div id="admin-activity-chart" aria-label="Activity from <?= html_escape(array_key_first($overview['daily'])) ?> to <?= html_escape(array_key_last($overview['daily'])) ?>. Daily counts follow in an accessible table."></div>
                <table class="sr-only">
                    <caption>Daily Activity</caption>
                    <thead><tr><th scope="col">Date</th><?php foreach ($overview['sources'] as $type => $source): ?><th scope="col"><?= html_escape($labels[$type]) ?></th><?php endforeach; ?></tr></thead>
                    <tbody><?php foreach (($overview['chart_daily'] ?? $overview['daily']) as $date => $day): ?><tr data-chart-date="<?= html_escape($date) ?>" <?= !isset($overview['daily'][$date]) ? 'hidden' : '' ?>><th scope="row"><?= html_escape($date) ?></th><?php foreach ($day['values'] as $count): ?><td><?= (int) $count ?></td><?php endforeach; ?></tr><?php endforeach; ?></tbody>
                </table>
            </div>
        </section>
    </div>
    <div class="col-md-4 col-sm-12">
        <section class="panel panel-default admin-dashboard-panel">
            <div class="panel-heading admin-dashboard-heading"><h4>Needs Attention</h4></div>
            <div class="admin-attention-list">
                <?php foreach ($overview['attention'] as $item):
                    $icon = strpos($item['url'], 'shipping') === 0 ? 'la-box' : (strpos($item['url'], 'admin_travellers') === 0 ? 'la-plane' : 'la-user'); ?>
                <div class="admin-attention-item">
                    <i class="las <?= $icon ?> f-s-24" aria-hidden="true"></i>
                    <span><?= html_escape($item['label']) ?></span>
                    <strong><?= number_format($item['count']) ?></strong>
                    <a href="<?= base_url($item['url']) ?>" aria-label="<?= html_escape('View ' . $item['label']) ?>">View</a>
                </div>
                <?php endforeach; ?>
                <?php if (empty($overview['attention'])): ?><div class="admin-dashboard-empty"><i class="las la-inbox f-s-30" aria-hidden="true"></i><span>Nothing to show</span></div><?php endif; ?>
            </div>
        </section>
    </div>
</div>
<section class="panel panel-default admin-dashboard-panel admin-recent-panel">
    <div class="panel-heading admin-dashboard-heading admin-recent-heading">
        <h4>Recent Activity</h4>
        <div class="btn-group" role="group" aria-label="Recent activity filter">
            <button type="button" class="btn btn-sm btn-primary" data-activity-filter="all" aria-pressed="true">All</button>
            <?php foreach ($overview['sources'] as $type => $source): ?>
            <button type="button" class="btn btn-sm btn-default" data-activity-filter="<?= html_escape($type) ?>" aria-pressed="false"><?= html_escape(ucfirst($type)) ?></button>
            <?php endforeach; ?>
        </div>
    </div>
    <div class="admin-recent-body">
        <div class="admin-recent-columns" aria-hidden="true"><strong>Activity</strong><strong>Details</strong><strong>Time</strong><strong>Action</strong></div>
        <div role="list" aria-label="Recent activity" aria-live="polite">
            <?php foreach ($overview['recent'] as $index => $activity): ?>
            <div class="admin-recent-row" role="listitem" data-activity-type="<?= html_escape($activity['type']) ?>" <?= $index >= 10 ? 'hidden' : '' ?>>
                <span><?= html_escape($activity['activity']) ?></span>
                <span><?= html_escape($activity['details']) ?></span>
                <time datetime="<?= html_escape($activity['occurred_at']) ?>" title="<?= html_escape($activity['occurred_at']) ?>"><?= html_escape($relativeTime($activity['occurred_at'])) ?></time>
                <a href="<?= base_url($activity['url']) ?>"><?= html_escape(array('users' => 'View user', 'bookings' => 'View booking', 'travellers' => 'View traveller')[$activity['type']]) ?></a>
            </div>
            <?php endforeach; ?>
        </div>
        <div class="admin-dashboard-empty admin-recent-empty" <?= !empty($overview['recent']) ? 'hidden' : '' ?>><i class="las la-history f-s-30" aria-hidden="true"></i><span>No recent activity</span></div>
    </div>
</section>
<script type="application/json" id="admin-activity-data"><?= json_encode(array('series' => $series, 'days' => $overview['days']), JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) ?></script>
<script defer src="<?= base_url('assets/users/libs/apexcharts/dist/apexcharts.min.js') ?>"></script>
<script defer src="<?= base_url('assets/admin/custom/js/dashboard.js?v=' . filemtime(FCPATH . 'assets/admin/custom/js/dashboard.js')) ?>"></script>
