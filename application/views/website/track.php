<!-- Hero -->
<section class="hero-wrap hero-wrap-custom">
    <div class="hero-mask opacity-7 bg-dark"></div>
    <div class="hero-bg" style="background-image:url('<?php echo base_url(); ?>assets/website/images/bg/map.jpeg');"></div>
    <div class="hero-content d-flex flex-column fullscreen-with-header">
        <div class="container my-auto py-5">
            <div class="row align-items-center text-center tw-pb-80">
                <div class="col-12 max-[460px]:tw-pt-20 mb-3">
                    <h2 class="text-14 text-white">Enter your <span class="tw-text-color-primary">Tracking ID</span></h2>
                </div>
                <div class="col-md-10 col-lg-8 col-xl-6 mx-auto">

                    <?php
                    $form_attributes = array("id" => "tracking_form");
                    echo form_open('home/track_parcel', $form_attributes); ?>

                    <div class="input-group">
                        <input class="form-control shadow-none border-0" type="text" name="parcel" id="parcel-input" placeholder="SMB839549346347">
                        <span class="input-group-text p-0">
                            <div class="dropdown bootstrap-select form-control bg-transparent">
                                <button data-bs-toggle="modal" data-bs-target="#tracking-detail" class="btn bg-primary tw-text-white" type="submit" id="submit-me">
                                    Search <span class="spinner-border spinner-border-sm text-light ms-2 d-none"
                                        id="search-spinner" role="status" aria-hidden="true"></span>
                                </button>
                            </div>
                        </span>
                    </div>

                    <?php echo form_close(); ?>

                </div>
            </div>
        </div>
    </div>
    <div class="wave hero"></div>
</section>
<!-- Hero End -->

<!-- Adverts (Carousel) -->
<section class="section bg-white">
    <div class="container">
        <h2 class="text-9 text-center">Ads and <span class="text-primary text-10 fw-600">Promotions</span></h2>
        <p class="lead text-center text-4 mb-4 mb-sm-3">Take advantage of our exclusive deals and limited-time promotions.</p>

        <?php if (!empty($adverts)) : ?>
            <?php if (count($adverts) < 5) : ?>
                <div class="row justify-content-center">
                    <?php foreach ($adverts as $a) : ?>
                        <div class="col-6 col-md-4 col-lg-3 mb-4">
                            <div class="item">
                                <a href="<?= base_url('assets/adverts/' . $a->photo); ?>" target="_blank">
                                    <img class="img-fluid" src="<?= base_url('assets/adverts/' . $a->photo); ?>" width="370" height="370" alt="<?= $a->alt_text ?? 'Advert' ?>" />
                                </a>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php else : ?>
                <div class="owl-carousel owl-theme" data-autoplay="true" data-nav="true" data-loop="true" data-margin="30" data-slideby="2" data-stagepadding="5" data-items-xs="2" data-items-sm="3" data-items-md="4" data-items-lg="6">
                    <?php foreach ($adverts as $a) : ?>
                        <div class="item">
                            <a href="<?= base_url('assets/adverts/' . $a->photo); ?>" target="_blank">
                                <img class="img-fluid" src="<?= base_url('assets/adverts/' . $a->photo); ?>" width="370" height="370" alt="<?= $a->alt_text ?? 'Advert' ?>" />
                            </a>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        <?php else : ?>
            <p class="text-center">No ads or promotions available at the moment.</p>
        <?php endif; ?>
    </div>
</section>
<!-- Adverts (Carousel) end -->


<!-- Tracking Item Details Modal -->
<div id="tracking-detail" class="modal fade" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered tracking-details" role="document">
        <div class="modal-content" id="trackingIdContainer">
            <h5 class="text-5 fw-400 m-3">Tracking History
                <button type="button" class="btn-close text-2 float-end" data-bs-dismiss="modal" aria-label="Close"></button>
            </h5>
            <div class="modal-body">
                <div class="row g-0">
                    <div class="col-sm-12 d-flex justify-content-center bg-primary rounded-start py-4">
                        <div class="my-auto text-center">
                            <h3 class="text-4 text-white fw-400 my-3"></h3>
                            <div class="text-4 fw-500 text-white my-4" id="m-trackingNumber">
                                <p class="text-white"></p>
                            </div>
                        </div>
                    </div>
                    <div class="col-sm-12">
                        <hr>
                        <div class="px-3" id="trackingHistory">

                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<!-- Tracking Item Details Modal End -->