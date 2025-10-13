<div class="form-body">
    <div class="row">
        <div class="form-holder">
            <div class="form-content">
                <div class="form-items">
                    <div class="logo">
                        <img class="logo-size" src="<?= business_logo_white; ?>" alt="">
                    </div>
                    <div class="page-links">
                    </div>

                    <?php
                    $form_attributes = array("id" => "admin_login_form");
                    echo form_open('admin_login/login_ajax', $form_attributes);

                    if ($this->session->is_requested) { ?>
                        <input type="hidden" id="requested_page" value="<?php echo $this->session->requested_page; ?>" />
                    <?php } else { ?>
                        <input type="hidden" id="requested_page" value="<?php echo base_url('admin'); ?>" />
                    <?php } ?>

                    <input class="form-control" type="text" name="email" placeholder="E-mail Address">
                    <input class="form-control" type="password" name="password" placeholder="Password">
                    <div class="row">
                        <div class="col-md-6">
                            <input class="form-control" type="text" name="captcha_code" id="captcha_code" value="<?php echo $captcha_code; ?>" readonly />
                        </div>
                        <div class="col-md-6">
                            <input class="form-control" type="text" name="c_captcha_code" value="<?php echo set_value('c_captcha_code'); ?>" placeholder="Enter captcha code here*" />
                        </div>
                    </div>
                    <div class="form-button">
                        <button id="submit" type="submit" class="ibtn login-btn">LOGIN</button>
                    </div>

                    <div class="form-messege mb-0 mt-20" id="status_msg"></div>

                    <?php echo form_close(); ?>

                </div>
            </div>
        </div>
    </div>
</div>