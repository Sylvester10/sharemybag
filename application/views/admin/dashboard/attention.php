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
