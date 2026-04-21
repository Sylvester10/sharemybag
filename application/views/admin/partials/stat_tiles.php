<?php if (!empty($tiles)) { ?>
<div class="admin-stat-grid">
    <div class="row">
    <?php foreach ($tiles as $tile) { ?>
        <div class="animated flipInY col-lg-<?php echo isset($tile['col']) ? (int) $tile['col'] : 3; ?> col-md-6 col-sm-6 col-xs-12">
            <div class="tile-stats <?php echo isset($tile['class']) ? $tile['class'] : 'custom-bg-blue'; ?>">
                <div class="icon"><i class="<?php echo html_escape($tile['icon']); ?>"></i></div>
                <div class="count"><?php echo $tile['value']; ?></div>
                <h3 class="stats-title"><?php echo html_escape($tile['label']); ?></h3>
            </div>
        </div>
    <?php } ?>
    </div>
</div>
<?php } ?>
