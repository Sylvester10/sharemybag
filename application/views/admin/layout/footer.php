</div><!--/.x_content-->
</div><!--/.x_panel-->
</div><!-- /right_col -->

<!-- footer content -->
<footer>
    <div class="pull-right">
        Powered by <a href="<?php echo software_vendor_site; ?>" target="_blank"><?php echo software_vendor; ?></a>
    </div>
    Copyright &copy; <?php echo date('Y'); ?>. <a href="<?php echo base_url(); ?>" target="_blank"><?php echo business_name; ?></a>
    <div class="clearfix"></div>
</footer>
<!-- /footer content -->

</div><!--/.main_container-->
</div><!--/.container body-->

<div class="modal fade" id="filePreviewModal" tabindex="-1" role="dialog" aria-labelledby="filePreviewModalLabel">
    <div class="modal-dialog modal-lg modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <div class="pull-right">
                    <button type="button" class="btn btn-danger btn-sm modal_close_btn" data-dismiss="modal" title="Close">&times;</button>
                </div>
                <h4 class="modal-title pull-left" id="filePreviewModalLabel">Document Preview</h4>
            </div>
            <div class="modal-body">
                <div class="admin-file-preview-frame">
                    <img id="filePreviewModalImage" class="admin-file-preview-image" src="" alt="Document preview" />
                </div>
            </div>
        </div>
    </div>
</div>

<?php
//require footer scripts
require "application/views/admin/layout/includes/footer_assets.php"; ?>

<script>
    //pass base_url to javascript
    var base_url = "<?php echo base_url(); ?>";
    var date_today = "<?php echo date('Y/m/d'); ?>";
</script>

</body>

</html>
