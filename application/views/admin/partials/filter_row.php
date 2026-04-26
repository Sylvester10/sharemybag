<?php if (!empty($filters)) { ?>
    <?php if (!empty($label)) { ?>
        <label class="admin-filter-label"><?php echo html_escape($label); ?></label>
    <?php } ?>
    <div class="row admin-filter-row">
        <?php foreach ($filters as $filter) { ?>
            <div class="<?php echo isset($filter['wrapper_class']) ? $filter['wrapper_class'] : 'col-lg-4 col-sm-12'; ?>">
                <?php if (!empty($filter['label'])) { ?>
                    <label for="<?php echo html_escape($filter['id']); ?>"><?php echo html_escape($filter['label']); ?></label>
                <?php } ?>
                <select id="<?php echo html_escape($filter['id']); ?>" class="form-control">
                    <?php foreach ($filter['options'] as $value => $text) { ?>
                        <option value="<?php echo html_escape($value); ?>"><?php echo html_escape($text); ?></option>
                    <?php } ?>
                </select>
            </div>
        <?php } ?>
    </div>
<?php } ?>
