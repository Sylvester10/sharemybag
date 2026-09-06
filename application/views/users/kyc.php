<?php
$is_nigeria = ($user_details->country == 'Nigeria');
$id_options = $is_nigeria
    ? ["Driver's License", 'International Passport', 'NIN Slip']
    : ['Biometric Card', 'Passport', "Driver's License"];
$id_input_class = $is_nigeria ? 'visible_image_input' : 'visible_image_input2';
$id_input_id = $is_nigeria ? 'the_image' : 'the_images';
$id_holder_id = $is_nigeria ? 'idHolder' : 'idHolder2';
$selfie_input_id = $is_nigeria ? 'image-input' : 'image-input2';
$selfie_holder_id = $is_nigeria ? 'selfie_holder' : 'selfie_holder2';
?>

<div class="container-fluid kyc-verification-page">
    <div class="row justify-content-center">
        <div class="col-xl-8 col-lg-10">
            <div class="card kyc-form-card">
                <div class="card-body">
                    <form action="<?= base_url('kyc/verify_ajax') ?>" class="form-wizard-ajax kyc-wizard-form" method="POST" enctype="multipart/form-data" target="_blank" redirect="<?= base_url('dashboard') ?>">
                        <input type="hidden" name="<?= $this->security->get_csrf_token_name(); ?>" value="<?= $this->security->get_csrf_hash(); ?>">

                        <h3><i class="ti ti-id"></i> ID Card</h3>
                        <fieldset>
                            <div class="kyc-step-heading">
                                <span class="kyc-step-icon"><i class="ti ti-id"></i></span>
                                <div>
                                    <h5>ID Card</h5>
                                    <p>Select your ID type and upload a clear photo of the document.</p>
                                </div>
                            </div>

                            <div class="mb-3">
                                <label class="form-label" for="kyc_id_type">ID Type *</label>
                                <select name="id_type" id="kyc_id_type" required class="required form-select border border-primary">
                                    <option value="">Select</option>
                                    <?php foreach ($id_options as $option) { ?>
                                        <option value="<?php echo html_escape($option); ?>"><?php echo html_escape($option); ?></option>
                                    <?php } ?>
                                </select>
                            </div>

                            <div class="">
                                <label class="form-label" for="<?php echo $id_input_id; ?>">Upload ID *</label>
                                <input class="required form-control border border-primary mb-3 <?php echo $id_input_class; ?>" type="file" name="id_photo" id="<?php echo $id_input_id; ?>" holder="<?php echo $id_holder_id; ?>" accept=".jpg,.jpeg,.png,image/jpeg,image/png">
                                <div class="image_container kyc-image-preview">
                                    <img src="<?= base_url('assets/general/id-card.png') ?>" alt="ID preview" id="<?php echo $id_holder_id; ?>">
                                    <span class="reset_img_input inside_button">Remove ID</span>
                                </div>
                            </div>
                        </fieldset>

                        <h3><i class="ti ti-user-shield"></i> Before You Start</h3>
                        <fieldset>
                            <div class="kyc-guide-card kyc-guide-panel">
                                <h4 class="fw-semibold fs-7 mb-1">Before you start</h4>
                                <p class="card-subtitle mb-4">Use clear, current documents. This helps the review team approve your verification faster.</p>

                                <div class="kyc-guide-list">
                                    <div class="kyc-guide-item">
                                        <i class="ti ti-circle-check"></i>
                                        <span>Take your ID and selfie in good lighting.</span>
                                    </div>
                                    <div class="kyc-guide-item">
                                        <i class="ti ti-circle-check"></i>
                                        <span>Make sure your face and ID details are not blocked.</span>
                                    </div>
                                    <div class="kyc-guide-item">
                                        <i class="ti ti-circle-check"></i>
                                        <span>Upload an accepted ID type for your country.</span>
                                    </div>
                                    <div class="kyc-guide-item">
                                        <i class="ti ti-circle-check"></i>
                                        <span>Use an ID document that has not expired.</span>
                                    </div>
                                    <div class="kyc-guide-item">
                                        <i class="ti ti-circle-check"></i>
                                        <span>Files should be JPG or PNG. Proof of address can also be PDF where required.</span>
                                    </div>
                                </div>
                            </div>
                        </fieldset>

                        <?php if ($is_nigeria) { ?>
                            <h3><i class="ti ti-file-description"></i> Proof of Address</h3>
                            <fieldset>
                                <div class="kyc-step-heading">
                                    <span class="kyc-step-icon"><i class="ti ti-file-description"></i></span>
                                    <div>
                                        <h5>Proof of Address</h5>
                                        <p>Upload a utility bill, bank statement, or driver's license. Utility bills and bank statements should be within the last 3 months.</p>
                                    </div>
                                </div>

                                <div class="kyc-upload-card">
                                    <label class="form-label" for="kyc_utility">Upload Document *</label>
                                    <input class="required form-control border border-primary mb-3" type="file" name="utility" id="kyc_utility" accept=".jpg,.jpeg,.png,.pdf,image/jpeg,image/png,application/pdf">
                                    <div class="kyc-file-hint">
                                        <i class="ti ti-file-upload"></i>
                                        <span>Accepted formats: JPG, PNG, or PDF.</span>
                                    </div>
                                </div>
                            </fieldset>
                        <?php } ?>

                        <h3><i class="ti ti-social"></i> Socials</h3>
                        <fieldset>
                            <div class="kyc-step-heading">
                                <span class="kyc-step-icon"><i class="ti ti-social"></i></span>
                                <div>
                                    <h5>Socials</h5>
                                    <p>Add one social media account so the review team can complete their checks.</p>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-lg-4 mb-3">
                                    <label class="form-label" for="kyc_platform">Platform *</label>
                                    <select name="platform" id="kyc_platform" required class="required form-select border border-primary">
                                        <option value="">Platform</option>
                                        <option value="facebook">Facebook</option>
                                        <option value="instagram">Instagram</option>
                                        <option value="twitter">Twitter (X)</option>
                                    </select>
                                </div>

                                <div class="col-lg-8 mb-3">
                                    <label class="form-label" for="kyc_socials">Handle *</label>
                                    <input class="required form-control border border-primary mb-3" type="text" name="socials" id="kyc_socials" placeholder="@johndoe">
                                </div>
                            </div>
                        </fieldset>

                        <h3><i class="ti ti-face-id"></i> Selfie</h3>
                        <fieldset>
                            <div class="kyc-step-heading">
                                <span class="kyc-step-icon"><i class="ti ti-face-id"></i></span>
                                <div>
                                    <h5>Selfie</h5>
                                    <p>Take a clear selfie so we can match your face to your ID.</p>
                                </div>
                            </div>

                            <button type="button" class="take-selfie selfie-paragraph kyc-selfie-button mb-4" target-input="<?php echo $selfie_input_id; ?>" target-img="<?php echo $selfie_holder_id; ?>">
                                <i class="ti ti-camera"></i>
                                <span>Take Selfie</span>
                            </button>

                            <div class="mb-3 image_container kyc-image-preview">
                                <img src="<?= base_url('assets/general/selfie2.png') ?>" alt="Selfie preview" id="<?php echo $selfie_holder_id; ?>">
                                <span class="reset_img_input inside_button">Remove Selfie</span>
                            </div>

                            <input type="text" class="form-control required" name="selfie" id="<?php echo $selfie_input_id; ?>" holder="<?php echo $selfie_holder_id; ?>" hidden>

                            <div id="status_msg"></div>
                        </fieldset>
                    </form>
                </div>
            </div>
        </div>

        <div class="modal fade show" id="capture-video" tabindex="-1" aria-labelledby="vertical-center-modal" aria-modal="true" role="dialog">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content">
                    <div class="modal-header d-flex align-items-center">
                        <h6 class="modal-title" id="myLargeModalLabel">
                            Face the camera. Ensure your face is within the frame.
                        </h6>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div id="video-container">
                            <video id="video-preview" autoplay></video>
                            <img id="image-preview" alt="Selfie Preview">
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn bg-success-subtle text-success waves-effect text-start" id="snap-icon">
                            Take Selfie <i class="ti ti-camera"></i>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
