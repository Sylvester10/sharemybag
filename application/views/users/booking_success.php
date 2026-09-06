<div class="container-fluid">

    <div class="card">
        <div class="card-body">
            <div class="d-flex justify-content-center lottie_success">
                <dotlottie-wc src="https://lottie.host/3f6b6bf3-6917-4f30-b234-da901f7c8f40/yFd7ZAKz3H.lottie"
                    style="width: 300px;height: 300px" autoplay></dotlottie-wc>
            </div>

            <div class="text-center lottie_success">
                <h4 class="card-title fs-8 mb-10">Thank you for your purchase!</h4>

                <h5>Here’s what you need to do next:</h5>

                <div class="the_list mt-3">
                    <div class="list-item">
                        <i class="ti ti-brand-chrome fs-4 text-primary flex-shrink-0"></i>
                        <span class="list-text">You can also find your traveller's details by clicking on the history button on your profile</span>
                    </div>

                    <div class="list-item">
                        <i class="ti ti-brand-chrome fs-4 text-primary flex-shrink-0"></i>
                        <span class="list-text">Check your email for the traveller’s drop off address</span>
                    </div>

                    <div class="list-item">
                        <i class="ti ti-brand-chrome fs-4 text-primary flex-shrink-0"></i>
                        <span class="list-text">If you are posting your items, label your parcel like this: name of traveller + SMB[sender’s name].</span>
                    </div>

                    <div class="list-item">
                        <i class="ti ti-brand-chrome fs-4 text-primary flex-shrink-0"></i>
                        <span class="list-text">Remember to include a return address.</span>
                    </div>

                    <div class="list-item">
                        <i class="ti ti-brand-chrome fs-4 text-primary flex-shrink-0"></i>
                        <span class="list-text">Send us tracking details if you are sending your items via Royal Mail or Evri</span>
                    </div>
                </div>

                <?php if (!empty($booking)) {
                    $support_url = booking_support_whatsapp_url($booking);
                    if ($support_url !== '') { ?>
                        <div class="alert alert-success mt-4 mb-0" role="region" aria-label="Booking support">
                            <p class="mb-2 fw-semibold text-dark">Need assistance with your completed booking?</p>
                            <a class="btn btn-success btn-sm"
                                href="<?= html_escape($support_url) ?>"
                                target="_blank"
                                rel="noopener noreferrer">
                                <i class="ti ti-brand-whatsapp me-1" aria-hidden="true"></i>
                                Need help with this parcel?
                            </a>
                        </div>
                    <?php }
                } ?>

                <h4 class="card-title fs-5 mt-3 mb-10" style="color: red;"> There is no refund or transfer of service to another traveler </h4>
            </div>

        </div>
    </div>
</div>
