<div class="container-fluid">
    <!-- <div class="card">
        <div class="border-bottom title-part-padding">
            <p class="card-subtitle mb-3">At <?= business_name ?>, security is a top priority. To protect your account and prevent unauthorized activities, we have introduced a verification feature as part of our commitment to safe and reliable service. Completing the KYC process is essential to lifting any restrictions on your account and allowing uninterrupted use of our platform. This process enables us to verify our users and ensures a smooth experience in booking and coordinating parcel deliveries between the UK and Nigeria. </p>
            <h4 class="card-title">Begin your KYC</h4>
        </div>
    </div> -->

    <div class="row">

        <div class="col-lg-6">
            <!-- instructions start -->
            <div class="card">
                <div class="card-body">

                    <div class="card !tw-bg-[#020713]">
                        <div class="card-body p-4">
                            <h5 class="card-text text-white text-center fw-bolder ">
                                Identity Verification
                            </h5>
                        </div>
                    </div>

                    <p class="card-subtitle mb-3">
                        Your Identity must be verified for you to gain full access to our platform. Please follow the instructions below to complete the verification process.
                    </p>
                    <ul class="list-group list-group-flush mb-3 !tw-ml-[-15px]">
                        <li class="list-group-item"><i class="ti ti-circle-check fs-4 me-1 text-primary"></i> Be sure that the selfie and photos of your ID are taken in good lighting.</li>
                        <li class="list-group-item"><i class="ti ti-circle-check fs-4 me-1 text-primary"></i> Be sure nothing is blocking parts of your ID or your face in your selfie.</li>
                        <li class="list-group-item"><i class="ti ti-circle-check fs-4 me-1 text-primary"></i> Be sure to submit an approved identification type.</li>
                        <li class="list-group-item"><i class="ti ti-circle-check fs-4 me-1 text-primary"></i> Please be sure your ID is not expired.</li>
                    </ul>
                    <p class="card-subtitle mb-3">Failure to adhere to these guidelines is considered a violation of our Verification Policies and will result in account deactivation.</p>

                </div>
            </div>
            <!-- instructions end -->
        </div>

        <div class="col-lg-6">
            <!-- Selfie start -->
            <div class="card">
                <div class="card-body">

                    <?php
                    if ($user_details->country == 'Nigeria') {
                    ?>

                        <!-- if Nigeria -->
                        <div class="">
                            <form action="<?= base_url('kyc/verify_ajax') ?>" class="form-wizard-ajax" method="POST" enctype="multipart/form-data" target="_blank" redirect="<?= base_url('dashboard') ?>">

                                <h3> <i class="ti ti-id fs-5"></i> ID Card</h3>
                                <fieldset>

                                    <div class="mb-3">
                                        <label class="form-label"> ID Type *</label>
                                        <select name="id_type" id="select" required class="required form-select border border-primary">
                                            <option value="">Select</option>
                                            <option value="Driver's License">Driver's License</option>
                                            <option value="International Passport">International Passport</option>
                                            <option value="NIN Slip">NIN Slip</option>
                                        </select>
                                    </div>

                                    <div class="mb-4">
                                        <label class="form-label">Upload ID *</label>
                                        <input class="required form-control border border-primary mb-3 visible_image_input" type="file" name="id_photo" id="the_image" holder="idHolder">
                                    </div>

                                    <div class="mb-3 image_container">
                                        <img src="<?= base_url('assets/general/id-card.png') ?>" alt="" id="idHolder">
                                        <span class="reset_img_input inside_button">Remove ID</span>
                                    </div>

                                </fieldset>

                                <h3> <i class="ti ti-file-description fs-5"></i> Proof of Address </h3>
                                <fieldset>
                                    <p class="card-subtitle mb-3">We accept utility bills, bank statements, and driver's license. Utility bills and bank statements should be within the last 3 months.</p>
                                    <div class="mb-3">
                                        <label class="form-label">Upload Document *</label>
                                        <input class="required form-control border border-primary mb-3" type="file" name="utility">
                                    </div>
                                </fieldset>

                                <h3> <i class="ti ti-social fs-5"></i> Socials</h3>
                                <fieldset>
                                    <div class="row">
                                        <div class="col-lg-4 mb-3">
                                            <select name="platform" id="select" required class="required form-select border border-primary">
                                                <option value="">Platform</option>
                                                <option value="facebook">Facebook</option>
                                                <option value="instagram">Instagram</option>
                                                <option value="twitter">Twitter (X)</option>
                                            </select>
                                        </div>

                                        <div class="col-lg-8 mb-3">
                                            <input class="required form-control border border-primary mb-3" type="text" name="socials" placeholder="@johndoe">
                                        </div>
                                    </div>
                                </fieldset>

                                <h3> <i class="ti ti-face-id fs-5"></i> Selfie</h3>
                                <fieldset>
                                    <p class="card-subtitle mb-3">Take a photo of yourself.</p>
                                    <p class="take-selfie selfie-paragraph mb-4" target-input="image-input" target-img="selfie_holder">Click here to take a selfie. </p>

                                    <div class="mb-3 image_container">
                                        <img src="<?= base_url('assets/general/selfie.png') ?>" alt="" id="selfie_holder">
                                        <span class="reset_img_input inside_button">Remove Selfie</span>
                                    </div>

                                    <input type="text" class="form-control required" name="selfie" id="image-input" hidden>

                                    <div id="status_msg"></div>
                                </fieldset>

                            </form>
                        </div>

                    <?php
                    } else {
                    ?>

                        <!-- if UK -->
                        <div class="">
                            <form action="<?= base_url('kyc/verify_ajax') ?>" class="form-wizard-ajax" method="POST" enctype="multipart/form-data" target="_blank" redirect="<?= base_url('dashboard') ?>">

                                <h3> <i class="ti ti-id fs-5"></i> ID Card </h3>
                                <fieldset>
                                    <div class="mb-3">
                                        <label class="form-label"> ID Type *</label>
                                        <select name="id_type" id="select" required class="required form-select border border-primary">
                                            <option value="">Select</option>
                                            <option value="Biometric Card">Biometric Card</option>
                                            <option value="British Passport">British Passport</option>
                                            <option value="Driver's License">Driver's License</option>
                                        </select>
                                    </div>

                                    <div class="mb-3">
                                        <label class="form-label">Upload ID *</label>
                                        <input class="required form-control border border-primary mb-3 visible_image_input2" type="file" name="id_photo" id="the_images" holder="idHolder2">
                                    </div>

                                    <div class="mb-3 image_container">
                                        <img src="<?= base_url('assets/general/id-card.png') ?>" alt="" id="idHolder2">
                                        <span class="reset_img_input inside_button">Remove ID</span>
                                    </div>
                                </fieldset>

                                <h3> <i class="ti ti-social fs-5"></i> Socials</h3>
                                <fieldset>
                                    <div class="row">
                                        <div class="col-lg-4 mb-3">
                                            <select name="platform" id="select" required class="required form-select border border-primary">
                                                <option value="">Platform</option>
                                                <option value="facebook">Facebook</option>
                                                <option value="instagram">Instagram</option>
                                                <option value="twitter">Twitter (X)</option>
                                            </select>
                                        </div>

                                        <div class="col-lg-8 mb-3">
                                            <input class="required form-control border border-primary mb-3" type="text" name="socials" placeholder="@johndoe">
                                        </div>
                                    </div>
                                </fieldset>

                                <h3> <i class="ti ti-face-id fs-5"></i> Selfie</h3>
                                <fieldset>
                                    <p class="card-subtitle mb-3">Take a photo of yourself.</p>
                                    <p class="take-selfie selfie-paragraph mb-4" target-input="image-input2" target-img="selfie_holder2">Click here to take a selfie. </p>

                                    <div class="mb-3 image_container">
                                        <img src="<?= base_url('assets/general/selfie.png') ?>" alt="" id="selfie_holder2">
                                        <span class="reset_img_input inside_button">Remove Selfie</span>
                                    </div>

                                    <input type="text" class="form-control required" name="selfie" id="image-input2" hidden>

                                    <div id="status_msg"></div>
                                </fieldset>

                            </form>
                        </div>

                    <?php } ?>


                </div>
            </div>
            <!-- Selfie end -->
        </div>

        <!-- Selfie modal start -->
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
                        <button type="button" class="btn bg-success-subtle text-success  waves-effect text-start" id="snap-icon">
                            Take Selfie <i class="ti ti-camera"></i>
                        </button>
                        <button type="button" class="btn bg-primary-subtle text-primary  waves-effect text-start" id="retake-icon">
                            Retake Selfie <i class="ti ti-camera-rotate"></i>
                        </button>
                        <button type="button" class="btn bg-danger-subtle text-danger  waves-effect text-start" data-bs-dismiss="modal">
                            Close
                        </button>
                    </div>
                </div>
            </div>
        </div>
        <!-- Selfie modal end -->

    </div>

</div>