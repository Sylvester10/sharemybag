<div class="step">
    <h3 class="main_question"><strong>ID Information</strong></h3>
    <p>Provide ID details of someone in the UK (Sender or Receiver)</p>
    <div class="row">
        <div class="col-lg-6">
            <div class="custom_select clearfix">
                <select class="wide required" name="id_owner">
                    <option value="">Select ID Owner</option>
                    <option value="Sender">Sender</option>
                    <option value="Receiver">Receiver</option>
                </select>
            </div>
        </div>

        <div class="col-lg-6">
            <div class="form-group">
                <input type="text" name="bank_name" class="form-control required" placeholder="Bank Name">
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-6">
            <div class="form-group">
                <input type="text" name="bank_acc" class="form-control required" placeholder="Account Number">
            </div>
        </div>

        <div class="col-lg-6">
            <div class="form-group">
                <input type="text" name="sort_code" class="form-control required" placeholder="Sort Code">
            </div>
        </div>
    </div>

    <div class="icon">
        <i class="fa-solid fa-circle-exclamation"></i> Important

        <p>
            Please ensure that the name of the selected <B>ID Owner</B> matches name on the ID
            Card Uploaded. We will use these details to confirm the name on the proof of
            identity provided. We are not currently accepting Nigerian ID cards.
        </p>
    </div>

    <div class="row">
        <div class="col-lg-6">
            <div class="custom_select clearfix">
                <select class="wide required" name="id_type">
                    <option value="">Select ID Type</option>
                    <option value="Biometric Card">Biometric Card</option>
                    <option value="British Passport">British Passport</option>
                    <option value="Driver's License">Driver's License</option>
                </select>
            </div>
        </div>

        <div class="col-lg-6">
            <div class="form-group">
                <div class="file_area">
                    <input type="file" name="id_photo" id="the_image" class="form-control required"
                        accept=".jpeg,.jpg,.png,.pdf" />
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-6">
            <!-- Image preview-->
            <?php echo generate_image_preview(); ?>
        </div>
        <div class="col-lg-6">
            <div id="video-container">
                <video id="video-preview" autoplay></video>
                <img id="image-preview" alt="Selfie Preview">
            </div>

            <div id="action-buttons" style="display: none;">
                <span class="icon_p" id="snap-icon">Take Picture</span>
                <span class="icon_p" id="retake-icon">Re-take Picture</span>
            </div>
            <!-- Invisible input field to hold the captured image file -->
            <input id="image-input" type="text" />
        </div>
    </div>
    <!-- /row -->

    <p id="selfie-paragraph"><i class="fa fa-camera"></i> Click here to take a selfie!</p>

    <br>
    <br>
    <p><i class="fa-solid fa-circle-exclamation"></i> Only <b>JPG</b>, <b>JPEG</b>, <b>PDF</b>
        and <b>PNG</b> files allowed <b>(max 5MB)</b>.</p>
</div>