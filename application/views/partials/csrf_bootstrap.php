<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<script type="text/javascript">
    window.appCsrf = {
        name: "<?php echo $this->security->get_csrf_token_name(); ?>",
        hash: "<?php echo $this->security->get_csrf_hash(); ?>"
    };
</script>
