 
    <script src="assets/admin/login/js/jquery.min.js"></script>
    <script src="assets/admin/login/js/popper.min.js"></script>
    <script src="assets/admin/login/js/bootstrap.min.js"></script>
    <script src="assets/admin/login/js/main.js"></script>

    <!-- Shared helpers (must load before custom_script.js) -->
    <script src="<?php echo base_url('assets/general/js/my_functions.js'); ?>"></script>

    <!-- Custom JS -->
    <script src="<?php echo base_url('assets/admin/login/js/custom_script.js'); ?>"></script>

    <script type="text/javascript">
        //pass base_url to js
        var base_url = "<?php echo base_url(); ?>";
    </script>
    <?php $this->load->view('partials/csrf_bootstrap'); ?>
</body>

</html>