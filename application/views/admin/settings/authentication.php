<div class="row">
    <div class="col-md-5 col-sm-12">
        <div class="x_panel">
            <div class="x_content">
                <p>Select how verified phone users receive sign-in and phone-verification codes.</p>

                <?php echo form_open('admin/update_authentication_settings'); ?>
                <div class="form-group">
                    <label for="phoneOtpChannel">Delivery channel</label>
                    <select class="form-control" id="phoneOtpChannel" name="phone_otp_channel" required>
                        <option value="whatsapp" <?php echo $phone_otp_channel === 'whatsapp' ? 'selected' : ''; ?>>WhatsApp (recommended)</option>
                        <option value="sms" <?php echo $phone_otp_channel === 'sms' ? 'selected' : ''; ?>>SMS</option>
                    </select>
                </div>
                <button type="submit" class="btn btn-primary">Save Authentication Settings</button>
                <?php echo form_close(); ?>
            </div>
        </div>
    </div>
</div>
