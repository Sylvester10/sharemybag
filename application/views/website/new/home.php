    <?php
    $pricing_ng_uk = booking_route_pricing('Nigeria', 'United Kingdom');
    $pricing_uk_ng = booking_route_pricing('United Kingdom', 'Nigeria');
    $pricing_ng_ca = booking_route_pricing('Nigeria', 'Canada');
    $pricing_ca_ng = booking_route_pricing('Canada', 'Nigeria');
    ?>

    <!-- Destination hero: one fixed set of controls over rotating photographs. -->
    <section class="smb-hero" aria-label="Send a parcel with a traveller" aria-roledescription="carousel">
        <div class="smb-hero-photos" aria-hidden="true">
            <img class="smb-hero-photo is-active" src="<?= base_url('assets/website/img/destinations/london.jpg'); ?>" alt="" fetchpriority="high" decoding="async">
            <img class="smb-hero-photo" src="<?= base_url('assets/website/img/destinations/toronto.jpg'); ?>" alt="" decoding="async">
            <img class="smb-hero-photo" src="<?= base_url('assets/website/img/destinations/lagos.jpg'); ?>" alt="" decoding="async">
        </div>
        <nav class="smb-hero-nav" aria-label="Main navigation">
            <a class="smb-hero-logo" href="<?= base_url(); ?>"><img src="<?= business_logo_white; ?>" width="101" height="63" alt="ShareMyBag home"></a>
            <div class="smb-hero-nav-links">
                <a href="<?= base_url('travellers'); ?>">I'm a Traveller</a>
                <a class="smb-hero-login" href="<?= base_url('signin'); ?>">Login <i class="las la-sign-in-alt" aria-hidden="true"></i></a>
            </div>
        </nav>
        <div class="smb-hero-content">
            <h1><span>Send Parcels to</span>
                <span class="smb-country-dial" aria-hidden="true"><span class="smb-country-size">United Kingdom</span><span class="smb-country-current">United Kingdom</span><span class="smb-country-next"></span></span><span class="visually-hidden">the United Kingdom, Canada and Nigeria</span>
                <span>with Ease</span>
            </h1>
            <?php
            $csrf_token_name = $this->security->get_csrf_token_name();
            $csrf_token_hash = $this->security->get_csrf_hash();
            echo form_open('home/search', array('id' => 'search_form', 'class' => 'smb-hero-search', 'novalidate' => 'novalidate')); ?>
            <input type="hidden" id="homepage_csrf_name" value="<?= html_escape($csrf_token_name); ?>">
            <input type="hidden" id="homepage_csrf_hash" value="<?= html_escape($csrf_token_hash); ?>">
            <div class="smb-hero-destination">
                <div>
                    <label class="visually-hidden" for="select_destination">Where is your parcel going?</label>
                    <select name="destination" id="select_destination" required>
                        <option value="">Where is your parcel going?</option>
                        <?php foreach (countries() as $country): ?>
                            <option value="<?= html_escape($country); ?>" <?= set_select('destination', $country); ?>><?= html_escape($country); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>
            <button class="smb-hero-submit" type="submit" id="submit" aria-label="Find a Traveller">
                <span id="smb-search-label">Find a Traveller</span>
                <span class="spinner-border spinner-border-sm text-light d-none" id="search-spinner" role="status" aria-hidden="true"></span>
            </button>
            <?= form_close(); ?>
            <div class="smb-hero-alternative"><span>Or</span><button type="button" id="openPriceChecker">Get Estimate <i class="las la-arrow-right" aria-hidden="true"></i></button></div>
        </div>
    </section>

    <!-- Feature Section
    <div class="feature-area feat-2 lg-d-none">
        <div class="container">
            <div class="feature-wrap">
                <div class="row gx-0">
                    <div class="col-xl-3 col-lg-3 col-md-3 col-sm-3 col-12">
                        <div class="feature-single">
                            <div class="feature-icon">
                                <img src="<?php echo base_url(); ?>assets/website/icons/nigeria.png" alt="">
                                <i class="la la-plane-departure"></i>
                                <img src="<?php echo base_url(); ?>assets/website/icons/united-kingdom.png" alt="">
                            </div>
                            <div class="feature-title">
                                <h5>NG - UK</h5>
                                <h4><b><?php echo currency_symbol($pricing_ng_uk['currency']) . number_format($pricing_ng_uk['normal_rate'], 2); ?> Per Kilo</b></h4>
                            </div>
                        </div>
                    </div>
                    <div class="col-xl-3 col-lg-3 col-md-4 col-sm-3 col-12">
                        <div class="feature-single">
                            <div class="feature-icon">
                                <img src="<?php echo base_url(); ?>assets/website/icons/united-kingdom.png" alt="">
                                <i class="la la-plane-departure"></i>
                                <img src="<?php echo base_url(); ?>assets/website/icons/nigeria.png" alt="">
                            </div>
                            <div class="feature-title">
                                <h5>UK - NG</h5>
                                <h4><b><?php echo currency_symbol($pricing_uk_ng['currency']) . number_format($pricing_uk_ng['normal_rate'], 2); ?> Per Kilo</b></h4>
                            </div>
                        </div>
                    </div>
                    <div class="col-xl-3 col-lg-3 col-md-3 col-sm-3 col-12">
                        <div class="feature-single">
                            <div class="feature-icon">
                                <img src="<?php echo base_url(); ?>assets/website/icons/nigeria.png" alt="">
                                <i class="la la-plane-departure"></i>
                                <img src="<?php echo base_url(); ?>assets/website/icons/canada.png" alt="">
                            </div>
                            <div class="feature-title">
                                <h5>NG - CA</h5>
                                <h4><b><?php echo currency_symbol($pricing_ng_ca['currency']) . number_format($pricing_ng_ca['normal_rate'], 2); ?> Per Kilo</b></h4>
                            </div>
                        </div>
                    </div>
                    <div class="col-xl-3 col-lg-3 col-md-3 col-sm-3 col-12">
                        <div class="feature-single">
                            <div class="feature-icon">
                                <img src="<?php echo base_url(); ?>assets/website/icons/canada.png" alt="">
                                <i class="la la-plane-departure"></i>
                                <img src="<?php echo base_url(); ?>assets/website/icons/nigeria.png" alt="">
                            </div>
                            <div class="feature-title">
                                <h5>CA - NG</h5>
                                <h4><b><?php echo currency_symbol($pricing_ca_ng['currency']) . number_format($pricing_ca_ng['normal_rate'], 2); ?> Per Kilo</b></h4>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="more-featuress text-center">
                            <p>There's a one-off fee for certain special or premium items.</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    -->

    <!-- How it works: the existing three steps, with a clearer visual sequence. -->
    <section class="smb-how-it-works" aria-labelledby="smb-how-it-works-title">
        <div class="container">
            <div class="smb-how-heading">
                <h2 id="smb-how-it-works-title">How it Works</h2>
                <p>Effortlessly send your items between Nigeria and the UK with our simple and secure process.</p>
            </div>
            <ol class="smb-how-steps">
                <li class="smb-how-step">
                    <span class="smb-how-number" aria-hidden="true">01</span>
                    <img class="smb-how-icon" src="<?= base_url('assets/website/icons/apply.png'); ?>" width="60" height="60" alt="">
                    <h3>Sign Up</h3>
                    <p>Create an account and complete your identity verification.</p>
                </li>
                <li class="smb-how-step">
                    <span class="smb-how-number" aria-hidden="true">02</span>
                    <img class="smb-how-icon" src="<?= base_url('assets/website/icons/search.png'); ?>" width="60" height="60" alt="">
                    <h3>Find a Traveller</h3>
                    <p>Search through our list of vetted travellers.</p>
                </li>
                <li class="smb-how-step">
                    <span class="smb-how-number" aria-hidden="true">03</span>
                    <img class="smb-how-icon" src="<?= base_url('assets/website/icons/package.png'); ?>" width="60" height="60" alt="">
                    <h3>Buy Bag Space</h3>
                    <p>Fill the parcel form and make payments.</p>
                </li>
            </ol>
        </div>
    </section>


    <!-- FAQs Area -->
    <div class="smb-faq-section">
        <div class="container">
            <div class="row">
                <div class="col-xl-12 col-lg-12">
                    <div class="smb-faq-heading">
                        <p>If you don't know, find out</p>
                        <h2>Frequently Asked Questions</h2>
                    </div>
                    <div class="smb-faq-list" id="accordionFaq">
                        <div class="smb-faq-column">
                        <div class="smb-faq-item">
                            <div class="smb-faq-header" id="heading1">
                                <h3 class="smb-faq-question-heading">
                                    <button class="smb-faq-question collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapse1" aria-expanded="false" aria-controls="collapse1">
                                        <span class="smb-faq-number" aria-hidden="true">01</span>
                                        <span class="smb-faq-question-text">How does it work?</span>
                                        <span class="smb-faq-toggle" aria-hidden="true"></span>
                                    </button>
                                </h3>
                            </div>
                            <div id="collapse1" class="collapse" aria-labelledby="heading1" data-bs-parent="#accordionFaq">
                                <div class="smb-faq-answer">
                                    <div class="smb-faq-answer-content">
                                        <p>It’s simple. ShareMyBag is a person-2-person luggage sharing service. We connect your parcel to a person traveling from Nigeria or going to Nigeria.</p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="smb-faq-item">
                            <div class="smb-faq-header" id="heading2">
                                <h3 class="smb-faq-question-heading">
                                    <button class="smb-faq-question collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapse2" aria-expanded="false" aria-controls="collapse2">
                                        <span class="smb-faq-number" aria-hidden="true">02</span>
                                        <span class="smb-faq-question-text">How do I find a traveller?</span>
                                        <span class="smb-faq-toggle" aria-hidden="true"></span>
                                    </button>
                                </h3>
                            </div>
                            <div id="collapse2" class="collapse" aria-labelledby="heading2" data-bs-parent="#accordionFaq">
                                <div class="smb-faq-answer">
                                    <div class="smb-faq-answer-content">
                                        <p>Here’s what you’ll need to do:</p>
                                        <ul class="project-solutions-list">
                                            <li><i class="las la-minus"></i>Create an account</li>
                                            <li><i class="las la-minus"></i>Complete your profile and ID verification (UK and EU government issued ID cards only)</li>
                                            <li><i class="las la-minus"></i>Find travellers</li>
                                            <li><i class="las la-minus"></i>Pay and get connected (the name on your payment method should match the name on your profile)</li>
                                        </ul>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="smb-faq-item">
                            <div class="smb-faq-header" id="heading3">
                                <h3 class="smb-faq-question-heading">
                                    <button class="smb-faq-question collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapse3" aria-expanded="false" aria-controls="collapse3">
                                        <span class="smb-faq-number" aria-hidden="true">03</span>
                                        <span class="smb-faq-question-text">How much does it cost per kg?</span>
                                        <span class="smb-faq-toggle" aria-hidden="true"></span>
                                    </button>
                                </h3>
                            </div>
                            <div id="collapse3" class="collapse" aria-labelledby="heading3" data-bs-parent="#accordionFaq">
                                <div class="smb-faq-answer">
                                    <div class="smb-faq-answer-content">
                                        <p>Rates depend on route. Nigeria to the UK starts at <?php echo currency_symbol($pricing_ng_uk['currency']) . number_format($pricing_ng_uk['normal_rate'], 2); ?> per kg, while Canada to Nigeria starts at <?php echo currency_symbol($pricing_ca_ng['currency']) . number_format($pricing_ca_ng['normal_rate'], 2); ?> per kg. Premium and special items attract additional charges.</p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="smb-faq-item">
                            <div class="smb-faq-header" id="heading4">
                                <h3 class="smb-faq-question-heading">
                                    <button class="smb-faq-question collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapse4" aria-expanded="false" aria-controls="collapse4">
                                        <span class="smb-faq-number" aria-hidden="true">04</span>
                                        <span class="smb-faq-question-text">How much is the premium category?</span>
                                        <span class="smb-faq-toggle" aria-hidden="true"></span>
                                    </button>
                                </h3>
                            </div>
                            <div id="collapse4" class="collapse" aria-labelledby="heading4" data-bs-parent="#accordionFaq">
                                <div class="smb-faq-answer">
                                    <div class="smb-faq-answer-content">
                                        <p>
                                            Premium and special-item pricing depends on route. For example, Nigeria to the UK small premium items start from <?php echo currency_symbol($pricing_ng_uk['currency']) . number_format($pricing_ng_uk['premium_small_rate'], 2); ?> per piece, while laptop pricing starts from <?php echo currency_symbol($pricing_ng_uk['currency']) . number_format($pricing_ng_uk['premium_laptop_rate'], 2); ?> per piece. Canada routes follow the same two premium bands, starting from <?php echo currency_symbol($pricing_ca_ng['currency']) . number_format($pricing_ca_ng['premium_small_rate'], 2); ?> and <?php echo currency_symbol($pricing_ca_ng['currency']) . number_format($pricing_ca_ng['premium_laptop_rate'], 2); ?> per piece.
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="smb-faq-item">
                            <div class="smb-faq-header" id="heading5">
                                <h3 class="smb-faq-question-heading">
                                    <button class="smb-faq-question collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapse5" aria-expanded="false" aria-controls="collapse5">
                                        <span class="smb-faq-number" aria-hidden="true">05</span>
                                        <span class="smb-faq-question-text">I don’t see travellers going to my preferred location</span>
                                        <span class="smb-faq-toggle" aria-hidden="true"></span>
                                    </button>
                                </h3>
                            </div>
                            <div id="collapse5" class="collapse" aria-labelledby="heading5" data-bs-parent="#accordionFaq">
                                <div class="smb-faq-answer">
                                    <div class="smb-faq-answer-content">
                                        <p>
                                            We don’t always know when travellers for a specific location will become available. You can however use any available traveller and we’ll help organize and coordinate the logistics of getting your parcel from your location to the traveller. You will be responsible for the local logistics cost, but we’ll oversee the process for your convenience at no extra charge.
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="smb-faq-item">
                            <div class="smb-faq-header" id="heading6">
                                <h3 class="smb-faq-question-heading">
                                    <button class="smb-faq-question collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapse6" aria-expanded="false" aria-controls="collapse6">
                                        <span class="smb-faq-number" aria-hidden="true">06</span>
                                        <span class="smb-faq-question-text">Can I drop my parcel at your office?</span>
                                        <span class="smb-faq-toggle" aria-hidden="true"></span>
                                    </button>
                                </h3>
                            </div>
                            <div id="collapse6" class="collapse" aria-labelledby="heading6" data-bs-parent="#accordionFaq">
                                <div class="smb-faq-answer">
                                    <div class="smb-faq-answer-content">
                                        <p>
                                            As ShareMyBag is a peer-2-peer baggage sharing service, we do not have physical space to store parcels. Parcels are required to go from the parcel owner to the traveller.
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="smb-faq-item">
                            <div class="smb-faq-header" id="heading7">
                                <h3 class="smb-faq-question-heading">
                                    <button class="smb-faq-question collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapse7" aria-expanded="false" aria-controls="collapse7">
                                        <span class="smb-faq-number" aria-hidden="true">07</span>
                                        <span class="smb-faq-question-text">How will my parcel get to the traveller?</span>
                                        <span class="smb-faq-toggle" aria-hidden="true"></span>
                                    </button>
                                </h3>
                            </div>
                            <div id="collapse7" class="collapse" aria-labelledby="heading7" data-bs-parent="#accordionFaq">
                                <div class="smb-faq-answer">
                                    <div class="smb-faq-answer-content">
                                        <p>
                                            If you need help connecting your parcel to the traveller, i.e., getting a dispatch rider, reach out to us on WhatsApp and we’ll give you support. We will always ensure to use trusted local couriers; however, ShareMyBag is not affiliated with them.
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="smb-faq-item">
                            <div class="smb-faq-header" id="heading8">
                                <h3 class="smb-faq-question-heading">
                                    <button class="smb-faq-question collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapse8" aria-expanded="false" aria-controls="collapse8">
                                        <span class="smb-faq-number" aria-hidden="true">08</span>
                                        <span class="smb-faq-question-text">Where will I drop my parcel?</span>
                                        <span class="smb-faq-toggle" aria-hidden="true"></span>
                                    </button>
                                </h3>
                            </div>
                            <div id="collapse8" class="collapse" aria-labelledby="heading8" data-bs-parent="#accordionFaq">
                                <div class="smb-faq-answer">
                                    <div class="smb-faq-answer-content">
                                        <p>
                                            You’ll receive the traveller’s drop-off address after payment.
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="smb-faq-item">
                            <div class="smb-faq-header" id="heading9">
                                <h3 class="smb-faq-question-heading">
                                    <button class="smb-faq-question collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapse9" aria-expanded="false" aria-controls="collapse9">
                                        <span class="smb-faq-number" aria-hidden="true">09</span>
                                        <span class="smb-faq-question-text">Will i get the phone number of my traveller?</span>
                                        <span class="smb-faq-toggle" aria-hidden="true"></span>
                                    </button>
                                </h3>
                            </div>
                            <div id="collapse9" class="collapse" aria-labelledby="heading9" data-bs-parent="#accordionFaq">
                                <div class="smb-faq-answer">
                                    <div class="smb-faq-answer-content">
                                        <p>
                                            To ensure smooth transaction between you and the traveller, we will often restrict traveller’s phone number. If you need to communicate with the traveller, just send us a message, we’ll forward it to the traveller and send you their response as soon as we have it.
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="smb-faq-item">
                            <div class="smb-faq-header" id="heading10">
                                <h3 class="smb-faq-question-heading">
                                    <button class="smb-faq-question collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapse10" aria-expanded="false" aria-controls="collapse10">
                                        <span class="smb-faq-number" aria-hidden="true">10</span>
                                        <span class="smb-faq-question-text">What’s the minimum kg I can send?</span>
                                        <span class="smb-faq-toggle" aria-hidden="true"></span>
                                    </button>
                                </h3>
                            </div>
                            <div id="collapse10" class="collapse" aria-labelledby="heading10" data-bs-parent="#accordionFaq">
                                <div class="smb-faq-answer">
                                    <div class="smb-faq-answer-content">
                                        <p>
                                            You can send as little as you want. Current pricing starts from <?php echo currency_symbol($pricing_ng_uk['currency']) . number_format($pricing_ng_uk['normal_rate'], 2); ?> per kilo from Nigeria to the UK, <?php echo currency_symbol($pricing_uk_ng['currency']) . number_format($pricing_uk_ng['normal_rate'], 2); ?> per kilo from the UK to Nigeria, <?php echo currency_symbol($pricing_ng_ca['currency']) . number_format($pricing_ng_ca['normal_rate'], 2); ?> per kilo from Nigeria to Canada, and <?php echo currency_symbol($pricing_ca_ng['currency']) . number_format($pricing_ca_ng['normal_rate'], 2); ?> per kilo from Canada to Nigeria.
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="smb-faq-item">
                            <div class="smb-faq-header" id="heading11">
                                <h3 class="smb-faq-question-heading">
                                    <button class="smb-faq-question collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapse11" aria-expanded="false" aria-controls="collapse11">
                                        <span class="smb-faq-number" aria-hidden="true">11</span>
                                        <span class="smb-faq-question-text">What’s the maximum kg I can send?</span>
                                        <span class="smb-faq-toggle" aria-hidden="true"></span>
                                    </button>
                                </h3>
                            </div>
                            <div id="collapse11" class="collapse" aria-labelledby="heading11" data-bs-parent="#accordionFaq">
                                <div class="smb-faq-answer">
                                    <div class="smb-faq-answer-content">
                                        <p>
                                            You can send as many kg as the traveller has to offer.
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="smb-faq-item">
                            <div class="smb-faq-header" id="heading12">
                                <h3 class="smb-faq-question-heading">
                                    <button class="smb-faq-question collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapse12" aria-expanded="false" aria-controls="collapse12">
                                        <span class="smb-faq-number" aria-hidden="true">12</span>
                                        <span class="smb-faq-question-text">I don’t know the weight of my parcel</span>
                                        <span class="smb-faq-toggle" aria-hidden="true"></span>
                                    </button>
                                </h3>
                            </div>
                            <div id="collapse12" class="collapse" aria-labelledby="heading12" data-bs-parent="#accordionFaq">
                                <div class="smb-faq-answer">
                                    <div class="smb-faq-answer-content">
                                        <p>
                                            If you don’t know the weight of your parcel, it’s advisable that you pay for an underestimated weight. If your parcel weighs more when it is received, you can pay for the difference. Please note, we do not do refunds or transfer of service to another traveler.
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="smb-faq-item">
                            <div class="smb-faq-header" id="heading13">
                                <h3 class="smb-faq-question-heading">
                                    <button class="smb-faq-question collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapse13" aria-expanded="false" aria-controls="collapse13">
                                        <span class="smb-faq-number" aria-hidden="true">13</span>
                                        <span class="smb-faq-question-text">When will my parcel get to Nigeria?</span>
                                        <span class="smb-faq-toggle" aria-hidden="true"></span>
                                    </button>
                                </h3>
                            </div>
                            <div id="collapse13" class="collapse" aria-labelledby="heading13" data-bs-parent="#accordionFaq">
                                <div class="smb-faq-answer">
                                    <div class="smb-faq-answer-content">
                                        <p>
                                            Depending on the traveller’s travel itinerary, your parcel will typically arrive on the same day of departure or a day after.
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="smb-faq-item">
                            <div class="smb-faq-header" id="heading14">
                                <h3 class="smb-faq-question-heading">
                                    <button class="smb-faq-question collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapse14" aria-expanded="false" aria-controls="collapse14">
                                        <span class="smb-faq-number" aria-hidden="true">14</span>
                                        <span class="smb-faq-question-text">Can my parcel be collected the same day as the traveller’s arrival?</span>
                                        <span class="smb-faq-toggle" aria-hidden="true"></span>
                                    </button>
                                </h3>
                            </div>
                            <div id="collapse14" class="collapse" aria-labelledby="heading14" data-bs-parent="#accordionFaq">
                                <div class="smb-faq-answer">
                                    <div class="smb-faq-answer-content">
                                        <p>
                                            If the traveller will arrive in good time, we can request for same day collection for you.
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="smb-faq-item">
                            <div class="smb-faq-header" id="heading15">
                                <h3 class="smb-faq-question-heading">
                                    <button class="smb-faq-question collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapse15" aria-expanded="false" aria-controls="collapse15">
                                        <span class="smb-faq-number" aria-hidden="true">15</span>
                                        <span class="smb-faq-question-text">Where is my parcel?</span>
                                        <span class="smb-faq-toggle" aria-hidden="true"></span>
                                    </button>
                                </h3>
                            </div>
                            <div id="collapse15" class="collapse" aria-labelledby="heading15" data-bs-parent="#accordionFaq">
                                <div class="smb-faq-answer">
                                    <div class="smb-faq-answer-content">
                                        <p>
                                            Contact us using the Whatsapp widget to get updates on the status of your parcel from when it gets to the traveller to when it is released by the traveller.
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        </div>
                        <div class="smb-faq-column">
                        <div class="smb-faq-item">
                            <div class="smb-faq-header" id="heading16">
                                <h3 class="smb-faq-question-heading">
                                    <button class="smb-faq-question collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapse16" aria-expanded="false" aria-controls="collapse16">
                                        <span class="smb-faq-number" aria-hidden="true">16</span>
                                        <span class="smb-faq-question-text">Can I send parcels to other countries?</span>
                                        <span class="smb-faq-toggle" aria-hidden="true"></span>
                                    </button>
                                </h3>
                            </div>
                            <div id="collapse16" class="collapse" aria-labelledby="heading16" data-bs-parent="#accordionFaq">
                                <div class="smb-faq-answer">
                                    <div class="smb-faq-answer-content">
                                        <p>
                                            ShareMyBag currently only operates the UK-Nigeria and Nigeria-UK route. We have hopes of expanding to other countries in the nearest future.
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="smb-faq-item">
                            <div class="smb-faq-header" id="heading17">
                                <h3 class="smb-faq-question-heading">
                                    <button class="smb-faq-question collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapse17" aria-expanded="false" aria-controls="collapse17">
                                        <span class="smb-faq-number" aria-hidden="true">17</span>
                                        <span class="smb-faq-question-text">Can I pay in Naira?</span>
                                        <span class="smb-faq-toggle" aria-hidden="true"></span>
                                    </button>
                                </h3>
                            </div>
                            <div id="collapse17" class="collapse" aria-labelledby="heading17" data-bs-parent="#accordionFaq">
                                <div class="smb-faq-answer">
                                    <div class="smb-faq-answer-content">
                                        <p>
                                            You can use a Naira card to pay for transactions. The exchange rate will depend on your bank. If you have Naira and would like pounds, you can use our currency swap service to exchange your Naira for pounds, and then use the pounds to pay for baggage space.
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="smb-faq-item">
                            <div class="smb-faq-header" id="heading18">
                                <h3 class="smb-faq-question-heading">
                                    <button class="smb-faq-question collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapse18" aria-expanded="false" aria-controls="collapse18">
                                        <span class="smb-faq-number" aria-hidden="true">18</span>
                                        <span class="smb-faq-question-text">What can I not send?</span>
                                        <span class="smb-faq-toggle" aria-hidden="true"></span>
                                    </button>
                                </h3>
                            </div>
                            <div id="collapse18" class="collapse" aria-labelledby="heading18" data-bs-parent="#accordionFaq">
                                <div class="smb-faq-answer">
                                    <div class="smb-faq-answer-content">
                                        <p>
                                            You shouldn’t send items that are usually prohibited on flights. Here’s a quick list:
                                        </p>
                                        <ul class="project-solutions-list">
                                            <li><i class="las la-minus"></i>Fireworks</li>
                                            <li><i class="las la-minus"></i>Lithium batteries</li>
                                            <li><i class="las la-minus"></i>Hazardous chemicals</li>
                                        </ul>
                                        <p>
                                            Please visit our restricted items page for a detailed list.</p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="smb-faq-item">
                            <div class="smb-faq-header" id="heading19">
                                <h3 class="smb-faq-question-heading">
                                    <button class="smb-faq-question collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapse19" aria-expanded="false" aria-controls="collapse19">
                                        <span class="smb-faq-number" aria-hidden="true">19</span>
                                        <span class="smb-faq-question-text">I hope the traveller is someone you trust?</span>
                                        <span class="smb-faq-toggle" aria-hidden="true"></span>
                                    </button>
                                </h3>
                            </div>
                            <div id="collapse19" class="collapse" aria-labelledby="heading19" data-bs-parent="#accordionFaq">
                                <div class="smb-faq-answer">
                                    <div class="smb-faq-answer-content">
                                        <p>
                                            If your parcel contains something of high value, let us know and we can connect you to a highly rated traveller.
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="smb-faq-item">
                            <div class="smb-faq-header" id="heading20">
                                <h3 class="smb-faq-question-heading">
                                    <button class="smb-faq-question collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapse20" aria-expanded="false" aria-controls="collapse20">
                                        <span class="smb-faq-number" aria-hidden="true">20</span>
                                        <span class="smb-faq-question-text">Why do I need parcel protection?</span>
                                        <span class="smb-faq-toggle" aria-hidden="true"></span>
                                    </button>
                                </h3>
                            </div>
                            <div id="collapse20" class="collapse" aria-labelledby="heading20" data-bs-parent="#accordionFaq">
                                <div class="smb-faq-answer">
                                    <div class="smb-faq-answer-content">
                                        <p>
                                            As with any service, there are things that may be beyond our control, for example, delayed or lost baggage by airlines. To minimize these incidents, we don’t work with individuals traveling with airlines that are notorious for baggage loss and delays. Parcel protection is optional. You can choose not to pay this.
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="smb-faq-item">
                            <div class="smb-faq-header" id="heading21">
                                <h3 class="smb-faq-question-heading">
                                    <button class="smb-faq-question collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapse21" aria-expanded="false" aria-controls="collapse21">
                                        <span class="smb-faq-number" aria-hidden="true">21</span>
                                        <span class="smb-faq-question-text">How do you protect my parcel?</span>
                                        <span class="smb-faq-toggle" aria-hidden="true"></span>
                                    </button>
                                </h3>
                            </div>
                            <div id="collapse21" class="collapse" aria-labelledby="heading21" data-bs-parent="#accordionFaq">
                                <div class="smb-faq-answer">
                                    <div class="smb-faq-answer-content">
                                        <p>
                                            ShareMyBag offers a standard £20 parcel protection for all parcels. If you need more cover, you can purchase it from £3.99 a parcel. Parcel protection covers your parcel up to a £100. For parcel protection up to £500, choose the £13.99 protection cover.
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="smb-faq-item">
                            <div class="smb-faq-header" id="heading22">
                                <h3 class="smb-faq-question-heading">
                                    <button class="smb-faq-question collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapse22" aria-expanded="false" aria-controls="collapse22">
                                        <span class="smb-faq-number" aria-hidden="true">22</span>
                                        <span class="smb-faq-question-text">Can I cancel my purchase and get a refund?</span>
                                        <span class="smb-faq-toggle" aria-hidden="true"></span>
                                    </button>
                                </h3>
                            </div>
                            <div id="collapse22" class="collapse" aria-labelledby="heading22" data-bs-parent="#accordionFaq">
                                <div class="smb-faq-answer">
                                    <div class="smb-faq-answer-content">
                                        <p>
                                            Once you’ve received the traveller’s drop off details there is no option for refund.
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="smb-faq-item">
                            <div class="smb-faq-header" id="heading23">
                                <h3 class="smb-faq-question-heading">
                                    <button class="smb-faq-question collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapse23" aria-expanded="false" aria-controls="collapse23">
                                        <span class="smb-faq-number" aria-hidden="true">23</span>
                                        <span class="smb-faq-question-text">My parcel cannot get to the traveller before the last drop off date, what are my options?</span>
                                        <span class="smb-faq-toggle" aria-hidden="true"></span>
                                    </button>
                                </h3>
                            </div>
                            <div id="collapse23" class="collapse" aria-labelledby="heading23" data-bs-parent="#accordionFaq">
                                <div class="smb-faq-answer">
                                    <div class="smb-faq-answer-content">
                                        <p>
                                            travellers have reserved space in their bag for you. If your parcel does not get to the traveller before the last drop off date, there will be no refund or transfer of the service to another traveller.
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="smb-faq-item">
                            <div class="smb-faq-header" id="heading24">
                                <h3 class="smb-faq-question-heading">
                                    <button class="smb-faq-question collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapse24" aria-expanded="false" aria-controls="collapse24">
                                        <span class="smb-faq-number" aria-hidden="true">24</span>
                                        <span class="smb-faq-question-text">What if my parcel is lost during the traveller’s journey?</span>
                                        <span class="smb-faq-toggle" aria-hidden="true"></span>
                                    </button>
                                </h3>
                            </div>
                            <div id="collapse24" class="collapse" aria-labelledby="heading24" data-bs-parent="#accordionFaq">
                                <div class="smb-faq-answer">
                                    <div class="smb-faq-answer-content">
                                        <p>
                                            If your parcel is lost, we will give you a full refund of your SMB fees plus any taxes. We will also aim to compensate you as close to your original cost of purchase as possible and depending on the parcel protection cover you opted for.
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="smb-faq-item">
                            <div class="smb-faq-header" id="heading25">
                                <h3 class="smb-faq-question-heading">
                                    <button class="smb-faq-question collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapse25" aria-expanded="false" aria-controls="collapse25">
                                        <span class="smb-faq-number" aria-hidden="true">25</span>
                                        <span class="smb-faq-question-text">Will my parcel be safe?</span>
                                        <span class="smb-faq-toggle" aria-hidden="true"></span>
                                    </button>
                                </h3>
                            </div>
                            <div id="collapse25" class="collapse" aria-labelledby="heading25" data-bs-parent="#accordionFaq">
                                <div class="smb-faq-answer">
                                    <div class="smb-faq-answer-content">
                                        <p>
                                            Yes, your parcel will be safe. ShareMyBag ensures that your parcel is handled by a trusted traveller. Additionally, we provide parcel protection coverage in case of any unfortunate event.
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="smb-faq-item">
                            <div class="smb-faq-header" id="heading26">
                                <h3 class="smb-faq-question-heading">
                                    <button class="smb-faq-question collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapse26" aria-expanded="false" aria-controls="collapse26">
                                        <span class="smb-faq-number" aria-hidden="true">26</span>
                                        <span class="smb-faq-question-text">How do I know if my parcel has been delivered?</span>
                                        <span class="smb-faq-toggle" aria-hidden="true"></span>
                                    </button>
                                </h3>
                            </div>
                            <div id="collapse26" class="collapse" aria-labelledby="heading26" data-bs-parent="#accordionFaq">
                                <div class="smb-faq-answer">
                                    <div class="smb-faq-answer-content">
                                        <p>
                                            You can track your parcel using our tracking service. We’ll provide updates on its journey, including when it reaches the traveller and when it’s delivered to the recipient.
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="smb-faq-item">
                            <div class="smb-faq-header" id="heading27">
                                <h3 class="smb-faq-question-heading">
                                    <button class="smb-faq-question collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapse27" aria-expanded="false" aria-controls="collapse27">
                                        <span class="smb-faq-number" aria-hidden="true">27</span>
                                        <span class="smb-faq-question-text">Can I change the traveller after I’ve booked?</span>
                                        <span class="smb-faq-toggle" aria-hidden="true"></span>
                                    </button>
                                </h3>
                            </div>
                            <div id="collapse27" class="collapse" aria-labelledby="heading27" data-bs-parent="#accordionFaq">
                                <div class="smb-faq-answer">
                                    <div class="smb-faq-answer-content">
                                        <p>
                                            Once a traveller is booked, it’s not possible to change them. However, if something changes with the traveller, we’ll notify you and offer you alternatives.
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="smb-faq-item">
                            <div class="smb-faq-header" id="heading28">
                                <h3 class="smb-faq-question-heading">
                                    <button class="smb-faq-question collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapse28" aria-expanded="false" aria-controls="collapse28">
                                        <span class="smb-faq-number" aria-hidden="true">28</span>
                                        <span class="smb-faq-question-text">Can I send perishable goods?</span>
                                        <span class="smb-faq-toggle" aria-hidden="true"></span>
                                    </button>
                                </h3>
                            </div>
                            <div id="collapse28" class="collapse" aria-labelledby="heading28" data-bs-parent="#accordionFaq">
                                <div class="smb-faq-answer">
                                    <div class="smb-faq-answer-content">
                                        <p>
                                            No, perishable goods are not allowed. For safety and customs reasons, we recommend avoiding sending items that can spoil or go bad during transport.
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="smb-faq-item">
                            <div class="smb-faq-header" id="heading29">
                                <h3 class="smb-faq-question-heading">
                                    <button class="smb-faq-question collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapse29" aria-expanded="false" aria-controls="collapse29">
                                        <span class="smb-faq-number" aria-hidden="true">29</span>
                                        <span class="smb-faq-question-text">How will I receive my parcel in Nigeria?</span>
                                        <span class="smb-faq-toggle" aria-hidden="true"></span>
                                    </button>
                                </h3>
                            </div>
                            <div id="collapse29" class="collapse" aria-labelledby="heading29" data-bs-parent="#accordionFaq">
                                <div class="smb-faq-answer">
                                    <div class="smb-faq-answer-content">
                                        <p>
                                            Once your parcel reaches Nigeria, we will contact you for a pickup or delivery. You can also track your parcel to get updates on its location.
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="smb-faq-item">
                            <div class="smb-faq-header" id="heading30">
                                <h3 class="smb-faq-question-heading">
                                    <button class="smb-faq-question collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapse30" aria-expanded="false" aria-controls="collapse30">
                                        <span class="smb-faq-number" aria-hidden="true">30</span>
                                        <span class="smb-faq-question-text">Can I send documents through ShareMyBag?</span>
                                        <span class="smb-faq-toggle" aria-hidden="true"></span>
                                    </button>
                                </h3>
                            </div>
                            <div id="collapse30" class="collapse" aria-labelledby="heading30" data-bs-parent="#accordionFaq">
                                <div class="smb-faq-answer">
                                    <div class="smb-faq-answer-content">
                                        <p>
                                            Yes, you can send documents through ShareMyBag. Please ensure that the documents are properly sealed and protected for transit.
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        </div> <!-- Second FAQ column -->
                    </div>

                </div>
            </div>
        </div>
    </div>


    <!-- Price estimate: existing calculations and response bindings retained. -->
    <div class="modal fade smb-estimate-modal" id="priceCheckerModal" tabindex="-1" role="dialog" aria-labelledby="priceCheckerTitle" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <div>
                        <h5 class="modal-title" id="priceCheckerTitle">Get an estimate</h5>
                    </div>
                    <button type="button" class="smb-estimate-close" data-bs-dismiss="modal" aria-label="Close price estimate"><i class="las la-times" aria-hidden="true"></i></button>
                </div>
                <div class="modal-body">
                    <div id="priceCheckerForm">
                        <div class="smb-estimate-route">
                            <div class="smb-estimate-field">
                                <label for="pc_origin">From <span aria-hidden="true">*</span></label>
                                <select id="pc_origin" class="form-control" required>
                                    <option value="">Select origin</option>
                                    <option value="Nigeria">Nigeria</option>
                                    <option value="United Kingdom">United Kingdom</option>
                                    <option value="Canada">Canada</option>
                                </select>
                            </div>
                            <div class="smb-estimate-field">
                                <label for="pc_destination">To <span aria-hidden="true">*</span></label>
                                <select id="pc_destination" class="form-control" required>
                                    <option value="">Select destination</option>
                                    <option value="Nigeria">Nigeria</option>
                                    <option value="United Kingdom">United Kingdom</option>
                                    <option value="Canada">Canada</option>
                                </select>
                            </div>
                        </div>
                        <div class="smb-estimate-field">
                            <label for="pc_category">Category <span aria-hidden="true">*</span></label>
                            <select id="pc_category" class="form-control" aria-describedby="pc_category_hint" required>
                                <option value="">Select category</option>
                                <option value="Normal">Normal</option>
                                <option value="Fish/Meat">Fish/Meat (special)</option>
                                <option value="Medication">Medication (special)</option>
                                <option value="Documents/Small Electronics">Documents/Small Electronics (premium)</option>
                                <option value="Laptop">Laptop (premium)</option>
                            </select>
                            <small id="pc_category_hint"></small>
                        </div>
                        <div class="smb-estimate-field">
                            <label for="pc_weight" id="pc_weight_label">Weight (KG) *</label>
                            <input type="number" id="pc_weight" class="form-control" placeholder="e.g. 5" min="1" max="50" step="0.5" required>
                        </div>
                        <button type="button" class="smb-estimate-primary" id="pc_submit_btn">
                            <span id="pc_btn_text">Calculate <i class="las la-arrow-right" aria-hidden="true"></i></span>
                            <span id="pc_spinner" class="d-none"><span class="spinner-border spinner-border-sm" aria-hidden="true"></span> Calculating...</span>
                        </button>
                    </div>
                    <div id="priceCheckerResult" class="d-none" aria-live="polite">
                        <div class="smb-estimate-summary">
                            <div><span>Route</span>
                                <p id="pc_res_route"></p>
                            </div>
                            <div><span>Category</span>
                                <p id="pc_res_category"></p>
                            </div>
                        </div>
                        <div class="smb-estimate-total"><span>Your estimate</span>
                            <h3 id="pc_res_total_box"></h3>
                        </div>
                        <div class="table-responsive">
                            <table class="table table-borderless smb-estimate-breakdown">
                                <tbody>
                                    <tr>
                                        <th scope="row">Weight / Qty</th>
                                        <td id="pc_res_weight"></td>
                                    </tr>
                                    <tr>
                                        <th scope="row">Rate</th>
                                        <td id="pc_res_rate"></td>
                                    </tr>
                                    <tr>
                                        <th scope="row">Item Cost</th>
                                        <td id="pc_res_item_price"></td>
                                    </tr>
                                    <tr>
                                        <th scope="row">Service Charge</th>
                                        <td id="pc_res_service"></td>
                                    </tr>
                                    <tr id="pc_res_special_row" class="d-none">
                                        <th scope="row">Special Fee</th>
                                        <td id="pc_res_special"></td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                        <p class="smb-estimate-disclaimer" id="pc_disclaimer"></p>
                        <div class="smb-estimate-actions">
                            <button type="button" class="smb-estimate-secondary" id="pc_recalculate_btn"><i class="las la-arrow-left" aria-hidden="true"></i> Recalculate</button>
                            <a href="<?= site_url('signin'); ?>" class="smb-estimate-primary">Book Now <i class="las la-suitcase" aria-hidden="true"></i></a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- =================== PRICE CHECKER JS =================== -->
    <script>
        (function() {
            'use strict';

            var baseUrl = '<?php echo base_url(); ?>';
            var csrfTokenName = document.getElementById('homepage_csrf_name').value;
            var csrfTokenHashEl = document.getElementById('homepage_csrf_hash');

            function getCsrfHash() {
                return csrfTokenHashEl.value;
            }

            function updateCsrfHash(newHash) {
                if (!newHash) {
                    return;
                }
                csrfTokenHashEl.value = newHash;
                var searchTokenInput = document.querySelector('#search_form input[name="' + csrfTokenName + '"]');
                if (searchTokenInput) {
                    searchTokenInput.value = newHash;
                }
            }

            var categoryConfig = {
                to_nigeria: [{
                        value: 'Normal',
                        label: 'Normal'
                    },
                    {
                        value: 'Duty Free',
                        label: 'Duty Free Shopping'
                    },
                    {
                        value: 'Fish/Meat',
                        label: 'Fish/Meat (special)'
                    },
                    {
                        value: 'Medication',
                        label: 'Medication (special)'
                    },
                    {
                        value: 'Documents/Small Electronics',
                        label: 'Documents/Small Electronics (premium)'
                    },
                    {
                        value: 'Laptop',
                        label: 'Laptop (premium)'
                    }
                ],
                default: [{
                        value: 'Normal',
                        label: 'Normal'
                    },
                    {
                        value: 'Fish/Meat',
                        label: 'Fish/Meat (special)'
                    },
                    {
                        value: 'Medication',
                        label: 'Medication (special)'
                    },
                    {
                        value: 'Documents/Small Electronics',
                        label: 'Documents/Small Electronics (premium)'
                    },
                    {
                        value: 'Laptop',
                        label: 'Laptop (premium)'
                    }
                ]
            };

            var categoryHints = {
                'Normal': '',
                'Duty Free': '',
                'Fish/Meat': 'A special handling fee of £10 / $10 applies to this category.',
                'Medication': 'A special handling fee of £10 / $10 applies to this category.',
                'Documents/Small Electronics': 'Premium pricing applies. Quantity is counted in pieces (PC), not KG.',
                'Laptop': 'Laptop pricing applies. Quantity is counted in pieces (PC), not KG.'
            };

            function updateCategoryHintAndUnit() {
                var cat = document.getElementById('pc_category').value;
                var weightLabel = document.getElementById('pc_weight_label');
                var hint = document.getElementById('pc_category_hint');

                weightLabel.textContent = (cat === 'Documents/Small Electronics' || cat === 'Laptop') ? 'Quantity (PC) *' : 'Weight (KG) *';
                hint.textContent = categoryHints[cat] || '';
            }

            function populateEstimateCategories() {
                var destination = document.getElementById('pc_destination').value;
                var categorySelect = document.getElementById('pc_category');
                var currentValue = categorySelect.value;
                var options = destination === 'Nigeria' ? categoryConfig.to_nigeria : categoryConfig.default;

                categorySelect.innerHTML = '<option value="">Select category</option>';

                options.forEach(function(option) {
                    var optionEl = document.createElement('option');
                    optionEl.value = option.value;
                    optionEl.textContent = option.label;
                    categorySelect.appendChild(optionEl);
                });

                if (options.some(function(option) {
                        return option.value === currentValue;
                    })) {
                    categorySelect.value = currentValue;
                }

                updateCategoryHintAndUnit();
                if (window.jQuery && jQuery(categorySelect).next('.nice-select').length) {
                    jQuery(categorySelect).niceSelect('update');
                }
            }

            function updateEstimateDestination() {
                var origin = document.getElementById('pc_origin').value;
                var select = document.getElementById('pc_destination');
                var selected = select.value;
                select.innerHTML = '<option value="">Select destination</option>';
                ['Nigeria', 'United Kingdom', 'Canada'].filter(function(country) {
                    return country !== origin;
                }).forEach(function(country) {
                    var option = document.createElement('option');
                    option.value = country;
                    option.textContent = country;
                    select.appendChild(option);
                });
                select.value = selected === origin ? '' : selected;
                if (jQuery(select).next('.nice-select').length) jQuery(select).niceSelect('update');
                if (window.smbFieldErrors) window.smbFieldErrors.clearField(select);
                populateEstimateCategories();
            }

            // Nice Select emits jQuery change events, so use the same event system.
            document.addEventListener('DOMContentLoaded', function() {
                jQuery('#pc_origin').on('change', updateEstimateDestination);
                jQuery('#pc_destination').on('change', populateEstimateCategories);
                jQuery('#pc_category').on('change', updateCategoryHintAndUnit);
                updateEstimateDestination();
            });
            populateEstimateCategories();

            // Calculate button
            document.getElementById('pc_submit_btn').addEventListener('click', function() {
                var origin = document.getElementById('pc_origin').value;
                var destination = document.getElementById('pc_destination').value;
                var category = document.getElementById('pc_category').value;
                var weight = parseFloat(document.getElementById('pc_weight').value);
                var errors = window.smbFieldErrors;
                errors.clear(document.getElementById('priceCheckerForm'));
                var invalid = false;
                [
                    ['pc_origin', origin, 'Please select an origin.'],
                    ['pc_destination', destination, 'Please select a destination.'],
                    ['pc_category', category, 'Please select a category.']
                ].forEach(function(field) {
                    if (!field[1]) {
                        errors.show(field[0], field[2]);
                        invalid = true;
                    }
                });
                if (!Number.isFinite(weight) || weight <= 0 || weight > 50) {
                    errors.show('pc_weight', 'Please enter a weight or quantity greater than 0 and no more than 50.');
                    invalid = true;
                }

                if (origin && destination && origin === destination) {
                    errors.show('pc_destination', 'Please choose a destination different from your origin.');
                    invalid = true;
                }
                if (origin && destination && origin !== destination && origin !== 'Nigeria' && destination !== 'Nigeria') {
                    errors.show('pc_destination', 'Please select a Nigeria–UK or Nigeria–Canada route.');
                    invalid = true;
                }
                if (invalid) {
                    errors.focusFirst(document.getElementById('priceCheckerForm'));
                    return;
                }

                // Show spinner
                document.getElementById('pc_btn_text').classList.add('d-none');
                document.getElementById('pc_spinner').classList.remove('d-none');
                document.getElementById('pc_submit_btn').disabled = true;

                // AJAX call
                fetch(baseUrl + 'home/price_estimate', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/x-www-form-urlencoded'
                        },
                        body: new URLSearchParams({
                            [csrfTokenName]: getCsrfHash(),
                            origin: origin,
                            destination: destination,
                            category: category,
                            weight: weight
                        })
                    })
                    .then(function(res) {
                        return res.json();
                    })
                    .then(function(data) {
                        updateCsrfHash(data.csrf_hash);
                        document.getElementById('pc_btn_text').classList.remove('d-none');
                        document.getElementById('pc_spinner').classList.add('d-none');
                        document.getElementById('pc_submit_btn').disabled = false;

                        if (!data.status) {
                            var message = data.msg || 'We could not calculate your estimate. Please try again.';
                            var field = /weight|quantity/i.test(message) ? 'pc_weight' : /category|Duty Free/i.test(message) ? 'pc_category' : /origin|destination|route/i.test(message) ? 'pc_destination' : 'pc_submit_btn';
                            errors.show(field, message);
                            return;
                        }

                        // Populate results
                        document.getElementById('pc_res_route').textContent = data.route;
                        document.getElementById('pc_res_category').textContent = data.category;
                        document.getElementById('pc_res_weight').textContent = data.weight;
                        document.getElementById('pc_res_total_box').textContent = data.total;

                        document.getElementById('pc_res_rate').textContent = data.price_per_unit;
                        document.getElementById('pc_res_item_price').textContent = data.item_price;
                        document.getElementById('pc_res_service').textContent = data.service_charge;
                        document.getElementById('pc_disclaimer').textContent = data.disclaimer;

                        var specialRow = document.getElementById('pc_res_special_row');
                        if (data.special_fee) {
                            document.getElementById('pc_res_special').textContent = data.special_fee;
                            specialRow.classList.remove('d-none');
                        } else {
                            specialRow.classList.add('d-none');
                        }

                        // Show result, hide form
                        document.getElementById('priceCheckerForm').classList.add('d-none');
                        document.getElementById('priceCheckerResult').classList.remove('d-none');
                    })
                    .catch(function() {
                        document.getElementById('pc_btn_text').classList.remove('d-none');
                        document.getElementById('pc_spinner').classList.add('d-none');
                        document.getElementById('pc_submit_btn').disabled = false;
                        errors.show('pc_submit_btn', 'We could not connect. Please try again.');
                    });
            });

            // Recalculate — show form again
            document.getElementById('pc_recalculate_btn').addEventListener('click', function() {
                document.getElementById('priceCheckerResult').classList.add('d-none');
                document.getElementById('priceCheckerForm').classList.remove('d-none');
                window.smbFieldErrors.clear(document.getElementById('priceCheckerForm'));
            });

            // Reset modal state on close
            document.getElementById('priceCheckerModal').addEventListener('hidden.bs.modal', function() {
                document.getElementById('priceCheckerResult').classList.add('d-none');
                document.getElementById('priceCheckerForm').classList.remove('d-none');
                window.smbFieldErrors.clear(document.getElementById('priceCheckerForm'));
                document.getElementById('pc_weight').value = '';
            });

            // "Get Estimate" button hook (if you use a separate trigger outside the modal)
            var openBtn = document.getElementById('openPriceChecker');
            if (openBtn) {
                openBtn.addEventListener('click', function() {
                    $('#priceCheckerModal').modal('show');
                });
            }
        }());
    </script>
