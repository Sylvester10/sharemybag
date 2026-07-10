<input type="hidden" id="csrf_hash" value="<?php echo html_escape($csrf_hash); ?>" />
<div class="table-scroll admin-table-shell">
    <table id="<?php echo html_escape($table_id); ?>" class="table table-bordered table-hover cell-text-middle admin-data-table<?php echo !empty($table_class) ? ' ' . $table_class : ''; ?>">
        <thead>
            <tr>
                <?php foreach ($columns as $column) { ?>
                    <th<?php echo !empty($column['class']) ? ' class="' . html_escape($column['class']) . '"' : ''; ?>>
                        <?php echo $column['label']; ?>
                    </th>
                <?php } ?>
            </tr>
        </thead>
        <tbody></tbody>
    </table>
</div>
