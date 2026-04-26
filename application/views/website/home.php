    <!-- Hero Area -->
    <div class="homepage-slides owl-carousel">
        <div class="single-slide-item hero-area-bg-3">
            <div class="overlay"></div>
            <div class="container">
                <div class="row align-items-center">
                    <div class="col-12 col-lg-8 wow fadeInUp animated" data-wow-delay=".2s">
                        <div class="hero-area-content">
                            <div class="section-title">
                                <h1>Send Parcels to the <span class="tw-text-color-primary">UK</span>, <span class="tw-text-color-primary">Canada</span> & <span class="tw-text-color-primary">Nigeria</span> with Ease</h1>
                                <h6>Use a Traveller</h6>
                            </div>
                        </div>
                    </div>
                    <div class="col-12 col-lg-4">
                        <?php
                        $csrf_token_name = $this->security->get_csrf_token_name();
                        $csrf_token_hash = $this->security->get_csrf_hash();
                        $form_attributes = array("id" => "search_form");
                        echo form_open('home/search', $form_attributes); ?>
                        <input type="hidden" id="homepage_csrf_name" value="<?php echo html_escape($csrf_token_name); ?>">
                        <input type="hidden" id="homepage_csrf_hash" value="<?php echo html_escape($csrf_token_hash); ?>">

                        <div class="the_form" style="margin-bottom: -62px;">
                            <div class="">
                                <select class="form-control" name="destination" id="select_destination" required>
                                    <option value="">Where is your parcel going?</option>
                                    <?php
                                    $countries = countries();
                                    foreach ($countries as $country) { ?>
                                        <option value="<?php echo $country; ?>" <?php echo set_select('nationality', $country); ?>><?php echo $country; ?>
                                        </option>
                                    <?php } ?>
                                </select>
                            </div>
                            <div class="">
                                <button class="main-btn primary" type="submit" id="submit">
                                    Find a Traveller <span class="spinner-border spinner-border-sm text-light ms-2 d-none" id="search-spinner" role="status" aria-hidden="true"></span>
                                </button>
                            </div>
                        </div>

                        <?php echo form_close(); ?>
                        <div class="text-center text-bold mb-4">
                            <h3 class="text-white ">Or</h3>
                        </div>
                        <div class="col-12">
                            <button type="button" class="main-btn primary mb-0" id="openPriceChecker">
                                <i class="ti ti-calculator me-1"></i> Get Estimate
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Feature Section  -->
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
                                <h4><b>£9.50 Per Kilo</b></h4>
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
                                <h4><b>£6.50 Per Kilo</b></h4>
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
                                <h4><b>$17.50 Per Kilo</b></h4>
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
                                <h4><b>$17.50 Per Kilo</b></h4>
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

    <!-- Process Area -->
    <div class="process-area bg-cover section-padding">
        <div class="container">
            <div class="row">
                <div class="offset-lg-2 col-lg-8 text-center">
                    <div class="section-title">
                        <p>Effortlessly send your items between Nigeria and the UK with our simple and secure process.</p>
                        <h2>How it Works</h2>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-lg-4 col-md-6 col-sm-12 wow fadeInUp animated" data-wow-delay="100ms">
                    <div class="single-process-area text-center">
                        <div class="process-icon">
                            <img src="<?php echo base_url(); ?>assets/website/icons/apply.png" alt="">
                        </div>
                        <h4>Sign Up Your Account</h4>
                        <p>Create an account and complete your identity verification.</p>
                        <span class="count-big">01</span>
                    </div>
                </div>
                <div class="col-lg-4 col-md-6 col-sm-12 wow fadeInUp animated" data-wow-delay="200ms">
                    <div class="single-process-area text-center">
                        <div class="process-icon">
                            <img src="<?php echo base_url(); ?>assets/website/icons/search.png" alt="">
                        </div>
                        <h4>Find a Traveller</h4>
                        <p>Search through our list of vetted travellers.</p>
                        <span class="count-big">02</span>
                    </div>
                </div>
                <div class="col-lg-4 col-md-6 col-sm-12 wow fadeInUp animated" data-wow-delay="300ms">
                    <div class="single-process-area text-center">
                        <div class="process-icon">
                            <img src="<?php echo base_url(); ?>assets/website/icons/package.png" alt="">
                        </div>
                        <h4>Buy Bag Space</h4>
                        <p>Fill the parcel form and make payments.</p>
                        <span class="count-big">03</span>
                    </div>
                </div>
            </div>
        </div>
    </div>


    <!-- FAQs Area -->
    <div class="faq-section gray-bg section-padding pb-50">
        <div class="container">
            <div class="row">
                <div class="col-xl-12 col-lg-12">
                    <div class="section-title text-center">
                        <p>If you don't know, find out</p>
                        <h2>Frequently Asked Questions</h2>
                    </div>
                    <div class="accordion faqs" id="accordionFaq">
                        <div class="card">
                            <div class="card-header" id="heading1">
                                <h5 class="mb-0 subtitle">
                                    <button class="btn btn-link collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapse1" aria-expanded="false" aria-controls="collapse1">
                                        How does it work?
                                    </button>
                                </h5>
                            </div>
                            <div id="collapse1" class="collapse" aria-labelledby="heading1" data-parent="#accordionFaq">
                                <div class="card-body">
                                    <div class="content">
                                        <p>It’s simple. ShareMyBag is a person-2-person luggage sharing service. We connect your parcel to a person traveling from Nigeria or going to Nigeria.</p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="card">
                            <div class="card-header" id="heading2">
                                <h5 class="mb-0 subtitle">
                                    <button class="btn btn-link collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapse2" aria-expanded="false" aria-controls="collapse2">
                                        How do I find a traveller?
                                    </button>
                                </h5>
                            </div>
                            <div id="collapse2" class="collapse" aria-labelledby="heading2" data-parent="#accordionFaq">
                                <div class="card-body">
                                    <div class="content">
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

                        <div class="card">
                            <div class="card-header" id="heading3">
                                <h5 class="mb-0 subtitle">
                                    <button class="btn btn-link collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapse3" aria-expanded="false" aria-controls="collapse3">
                                        How much does it cost per kg?
                                    </button>
                                </h5>
                            </div>
                            <div id="collapse3" class="collapse" aria-labelledby="heading3" data-parent="#accordionFaq">
                                <div class="card-body">
                                    <div class="content">
                                        <p>Rates depend on route. Nigeria to the UK starts at £9.50 per kg, while Canada to Nigeria starts at $17.50 per kg. Premium and special items attract additional charges.</p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="card">
                            <div class="card-header" id="heading4">
                                <h5 class="mb-0 subtitle">
                                    <button class="btn btn-link collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapse4" aria-expanded="false" aria-controls="collapse4">
                                        How much is the premium category?
                                    </button>
                                </h5>
                            </div>
                            <div id="collapse4" class="collapse" aria-labelledby="heading4" data-parent="#accordionFaq">
                                <div class="card-body">
                                    <div class="content">
                                        <p>
                                            Premium and special-item pricing depends on route. For example, Nigeria to the UK premium items start from £15 per piece, while Canada to Nigeria premium items start from $36.93 per piece.
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="card">
                            <div class="card-header" id="heading5">
                                <h5 class="mb-0 subtitle">
                                    <button class="btn btn-link collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapse5" aria-expanded="false" aria-controls="collapse5">
                                        I don’t see travellers going to my preferred location
                                    </button>
                                </h5>
                            </div>
                            <div id="collapse5" class="collapse" aria-labelledby="heading5" data-parent="#accordionFaq">
                                <div class="card-body">
                                    <div class="content">
                                        <p>
                                            We don’t always know when travellers for a specific location will become available. You can however use any available traveller and we’ll help organize and coordinate the logistics of getting your parcel from your location to the traveller. You will be responsible for the local logistics cost, but we’ll oversee the process for your convenience at no extra charge.
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="card">
                            <div class="card-header" id="heading6">
                                <h5 class="mb-0 subtitle">
                                    <button class="btn btn-link collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapse6" aria-expanded="false" aria-controls="collapse6">
                                        Can I drop my parcel at your office?
                                    </button>
                                </h5>
                            </div>
                            <div id="collapse6" class="collapse" aria-labelledby="heading6" data-parent="#accordionFaq">
                                <div class="card-body">
                                    <div class="content">
                                        <p>
                                            As ShareMyBag is a peer-2-peer baggage sharing service, we do not have physical space to store parcels. Parcels are required to go from the parcel owner to the traveller.
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="card">
                            <div class="card-header" id="heading7">
                                <h5 class="mb-0 subtitle">
                                    <button class="btn btn-link collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapse7" aria-expanded="false" aria-controls="collapse7">
                                        How will my parcel get to the traveller?
                                    </button>
                                </h5>
                            </div>
                            <div id="collapse7" class="collapse" aria-labelledby="heading7" data-parent="#accordionFaq">
                                <div class="card-body">
                                    <div class="content">
                                        <p>
                                            If you need help connecting your parcel to the traveller, i.e., getting a dispatch rider, reach out to us on WhatsApp and we’ll give you support. We will always ensure to use trusted local couriers; however, ShareMyBag is not affiliated with them.
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="card">
                            <div class="card-header" id="heading8">
                                <h5 class="mb-0 subtitle">
                                    <button class="btn btn-link collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapse8" aria-expanded="false" aria-controls="collapse8">
                                        Where will I drop my parcel?
                                    </button>
                                </h5>
                            </div>
                            <div id="collapse8" class="collapse" aria-labelledby="heading8" data-parent="#accordionFaq">
                                <div class="card-body">
                                    <div class="content">
                                        <p>
                                            You’ll receive the traveller’s drop-off address after payment.
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="card">
                            <div class="card-header" id="heading9">
                                <h5 class="mb-0 subtitle">
                                    <button class="btn btn-link collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapse9" aria-expanded="false" aria-controls="collapse9">
                                        Will i get the phone number of my traveller?
                                    </button>
                                </h5>
                            </div>
                            <div id="collapse9" class="collapse" aria-labelledby="heading9" data-parent="#accordionFaq">
                                <div class="card-body">
                                    <div class="content">
                                        <p>
                                            To ensure smooth transaction between you and the traveller, we will often restrict traveller’s phone number. If you need to communicate with the traveller, just send us a message, we’ll forward it to the traveller and send you their response as soon as we have it.
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="card">
                            <div class="card-header" id="heading10">
                                <h5 class="mb-0 subtitle">
                                    <button class="btn btn-link collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapse10" aria-expanded="false" aria-controls="collapse10">
                                        What’s the minimum kg I can send?
                                    </button>
                                </h5>
                            </div>
                            <div id="collapse10" class="collapse" aria-labelledby="heading10" data-parent="#accordionFaq">
                                <div class="card-body">
                                    <div class="content">
                                        <p>
                                            You can send as little as you want. Current pricing starts from £9.50 per kilo from Nigeria to the UK, £6.50 per kilo from the UK to Nigeria, $18.50 per kilo from Nigeria to Canada, and $17.50 per kilo from Canada to Nigeria.
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="card">
                            <div class="card-header" id="heading11">
                                <h5 class="mb-0 subtitle">
                                    <button class="btn btn-link collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapse11" aria-expanded="false" aria-controls="collapse11">
                                        What’s the maximum kg I can send?
                                    </button>
                                </h5>
                            </div>
                            <div id="collapse11" class="collapse" aria-labelledby="heading11" data-parent="#accordionFaq">
                                <div class="card-body">
                                    <div class="content">
                                        <p>
                                            You can send as many kg as the traveller has to offer.
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="card">
                            <div class="card-header" id="heading12">
                                <h5 class="mb-0 subtitle">
                                    <button class="btn btn-link collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapse12" aria-expanded="false" aria-controls="collapse12">
                                        I don’t know the weight of my parcel
                                    </button>
                                </h5>
                            </div>
                            <div id="collapse12" class="collapse" aria-labelledby="heading12" data-parent="#accordionFaq">
                                <div class="card-body">
                                    <div class="content">
                                        <p>
                                            If you don’t know the weight of your parcel, it’s advisable that you pay for an underestimated weight. If your parcel weighs more when it is received, you can pay for the difference. Please note, we do not do refunds or transfer of service to another traveler.
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="card">
                            <div class="card-header" id="heading13">
                                <h5 class="mb-0 subtitle">
                                    <button class="btn btn-link collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapse13" aria-expanded="false" aria-controls="collapse13">
                                        When will my parcel get to Nigeria?
                                    </button>
                                </h5>
                            </div>
                            <div id="collapse13" class="collapse" aria-labelledby="heading13" data-parent="#accordionFaq">
                                <div class="card-body">
                                    <div class="content">
                                        <p>
                                            Depending on the traveller’s travel itinerary, your parcel will typically arrive on the same day of departure or a day after.
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="card">
                            <div class="card-header" id="heading14">
                                <h5 class="mb-0 subtitle">
                                    <button class="btn btn-link collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapse14" aria-expanded="false" aria-controls="collapse14">
                                        Can my parcel be collected the same day as the traveller’s arrival?
                                    </button>
                                </h5>
                            </div>
                            <div id="collapse14" class="collapse" aria-labelledby="heading14" data-parent="#accordionFaq">
                                <div class="card-body">
                                    <div class="content">
                                        <p>
                                            If the traveller will arrive in good time, we can request for same day collection for you.
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="card">
                            <div class="card-header" id="heading15">
                                <h5 class="mb-0 subtitle">
                                    <button class="btn btn-link collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapse15" aria-expanded="false" aria-controls="collapse15">
                                        Where is my parcel?
                                    </button>
                                </h5>
                            </div>
                            <div id="collapse15" class="collapse" aria-labelledby="heading15" data-parent="#accordionFaq">
                                <div class="card-body">
                                    <div class="content">
                                        <p>
                                            Contact us using the Whatsapp widget to get updates on the status of your parcel from when it gets to the traveller to when it is released by the traveller.
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="card">
                            <div class="card-header" id="heading16">
                                <h5 class="mb-0 subtitle">
                                    <button class="btn btn-link collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapse16" aria-expanded="false" aria-controls="collapse16">
                                        Can I send parcels to other countries?
                                    </button>
                                </h5>
                            </div>
                            <div id="collapse16" class="collapse" aria-labelledby="heading16" data-parent="#accordionFaq">
                                <div class="card-body">
                                    <div class="content">
                                        <p>
                                            ShareMyBag currently only operates the UK-Nigeria and Nigeria-UK route. We have hopes of expanding to other countries in the nearest future.
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="card">
                            <div class="card-header" id="heading17">
                                <h5 class="mb-0 subtitle">
                                    <button class="btn btn-link collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapse17" aria-expanded="false" aria-controls="collapse17">
                                        Can I pay in Naira?
                                    </button>
                                </h5>
                            </div>
                            <div id="collapse17" class="collapse" aria-labelledby="heading17" data-parent="#accordionFaq">
                                <div class="card-body">
                                    <div class="content">
                                        <p>
                                            You can use a Naira card to pay for transactions. The exchange rate will depend on your bank. If you have Naira and would like pounds, you can use our currency swap service to exchange your Naira for pounds, and then use the pounds to pay for baggage space.
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="card">
                            <div class="card-header" id="heading18">
                                <h5 class="mb-0 subtitle">
                                    <button class="btn btn-link collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapse18" aria-expanded="false" aria-controls="collapse18">
                                        What can I not send?
                                    </button>
                                </h5>
                            </div>
                            <div id="collapse18" class="collapse" aria-labelledby="heading18" data-parent="#accordionFaq">
                                <div class="card-body">
                                    <div class="content">
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

                        <div class="card">
                            <div class="card-header" id="heading19">
                                <h5 class="mb-0 subtitle">
                                    <button class="btn btn-link collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapse19" aria-expanded="false" aria-controls="collapse19">
                                        I hope the traveller is someone you trust?
                                    </button>
                                </h5>
                            </div>
                            <div id="collapse19" class="collapse" aria-labelledby="heading19" data-parent="#accordionFaq">
                                <div class="card-body">
                                    <div class="content">
                                        <p>
                                            If your parcel contains something of high value, let us know and we can connect you to a highly rated traveller.
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="card">
                            <div class="card-header" id="heading20">
                                <h5 class="mb-0 subtitle">
                                    <button class="btn btn-link collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapse20" aria-expanded="false" aria-controls="collapse20">
                                        Why do I need parcel protection?
                                    </button>
                                </h5>
                            </div>
                            <div id="collapse20" class="collapse" aria-labelledby="heading20" data-parent="#accordionFaq">
                                <div class="card-body">
                                    <div class="content">
                                        <p>
                                            As with any service, there are things that may be beyond our control, for example, delayed or lost baggage by airlines. To minimize these incidents, we don’t work with individuals traveling with airlines that are notorious for baggage loss and delays. Parcel protection is optional. You can choose not to pay this.
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="card">
                            <div class="card-header" id="heading21">
                                <h5 class="mb-0 subtitle">
                                    <button class="btn btn-link collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapse21" aria-expanded="false" aria-controls="collapse21">
                                        How do you protect my parcel?
                                    </button>
                                </h5>
                            </div>
                            <div id="collapse21" class="collapse" aria-labelledby="heading21" data-parent="#accordionFaq">
                                <div class="card-body">
                                    <div class="content">
                                        <p>
                                            ShareMyBag offers a standard £20 parcel protection for all parcels. If you need more cover, you can purchase it from £3.99 a parcel. Parcel protection covers your parcel up to a £100. For parcel protection up to £500, choose the £13.99 protection cover.
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="card">
                            <div class="card-header" id="heading22">
                                <h5 class="mb-0 subtitle">
                                    <button class="btn btn-link collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapse22" aria-expanded="false" aria-controls="collapse22">
                                        Can I cancel my purchase and get a refund?
                                    </button>
                                </h5>
                            </div>
                            <div id="collapse22" class="collapse" aria-labelledby="heading22" data-parent="#accordionFaq">
                                <div class="card-body">
                                    <div class="content">
                                        <p>
                                            Once you’ve received the traveller’s drop off details there is no option for refund.
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="card">
                            <div class="card-header" id="heading23">
                                <h5 class="mb-0 subtitle">
                                    <button class="btn btn-link collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapse23" aria-expanded="false" aria-controls="collapse23">
                                        My parcel cannot get to the traveller before the last drop off date, what are my options?
                                    </button>
                                </h5>
                            </div>
                            <div id="collapse23" class="collapse" aria-labelledby="heading23" data-parent="#accordionFaq">
                                <div class="card-body">
                                    <div class="content">
                                        <p>
                                            travellers have reserved space in their bag for you. If your parcel does not get to the traveller before the last drop off date, there will be no refund or transfer of the service to another traveller.
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="card">
                            <div class="card-header" id="heading24">
                                <h5 class="mb-0 subtitle">
                                    <button class="btn btn-link collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapse24" aria-expanded="false" aria-controls="collapse24">
                                        What if my parcel is lost during the traveller’s journey?
                                    </button>
                                </h5>
                            </div>
                            <div id="collapse24" class="collapse" aria-labelledby="heading24" data-parent="#accordionFaq">
                                <div class="card-body">
                                    <div class="content">
                                        <p>
                                            If your parcel is lost, we will give you a full refund of your SMB fees plus any taxes. We will also aim to compensate you as close to your original cost of purchase as possible and depending on the parcel protection cover you opted for.
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="card">
                            <div class="card-header" id="heading25">
                                <h5 class="mb-0 subtitle">
                                    <button class="btn btn-link collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapse25" aria-expanded="false" aria-controls="collapse25">
                                        Will my parcel be safe?
                                    </button>
                                </h5>
                            </div>
                            <div id="collapse25" class="collapse" aria-labelledby="heading25" data-parent="#accordionFaq">
                                <div class="card-body">
                                    <div class="content">
                                        <p>
                                            Yes, your parcel will be safe. ShareMyBag ensures that your parcel is handled by a trusted traveller. Additionally, we provide parcel protection coverage in case of any unfortunate event.
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="card">
                            <div class="card-header" id="heading26">
                                <h5 class="mb-0 subtitle">
                                    <button class="btn btn-link collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapse26" aria-expanded="false" aria-controls="collapse26">
                                        How do I know if my parcel has been delivered?
                                    </button>
                                </h5>
                            </div>
                            <div id="collapse26" class="collapse" aria-labelledby="heading26" data-parent="#accordionFaq">
                                <div class="card-body">
                                    <div class="content">
                                        <p>
                                            You can track your parcel using our tracking service. We’ll provide updates on its journey, including when it reaches the traveller and when it’s delivered to the recipient.
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="card">
                            <div class="card-header" id="heading27">
                                <h5 class="mb-0 subtitle">
                                    <button class="btn btn-link collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapse27" aria-expanded="false" aria-controls="collapse27">
                                        Can I change the traveller after I’ve booked?
                                    </button>
                                </h5>
                            </div>
                            <div id="collapse27" class="collapse" aria-labelledby="heading27" data-parent="#accordionFaq">
                                <div class="card-body">
                                    <div class="content">
                                        <p>
                                            Once a traveller is booked, it’s not possible to change them. However, if something changes with the traveller, we’ll notify you and offer you alternatives.
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="card">
                            <div class="card-header" id="heading28">
                                <h5 class="mb-0 subtitle">
                                    <button class="btn btn-link collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapse28" aria-expanded="false" aria-controls="collapse28">
                                        Can I send perishable goods?
                                    </button>
                                </h5>
                            </div>
                            <div id="collapse28" class="collapse" aria-labelledby="heading28" data-parent="#accordionFaq">
                                <div class="card-body">
                                    <div class="content">
                                        <p>
                                            No, perishable goods are not allowed. For safety and customs reasons, we recommend avoiding sending items that can spoil or go bad during transport.
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="card">
                            <div class="card-header" id="heading29">
                                <h5 class="mb-0 subtitle">
                                    <button class="btn btn-link collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapse29" aria-expanded="false" aria-controls="collapse29">
                                        How will I receive my parcel in Nigeria?
                                    </button>
                                </h5>
                            </div>
                            <div id="collapse29" class="collapse" aria-labelledby="heading29" data-parent="#accordionFaq">
                                <div class="card-body">
                                    <div class="content">
                                        <p>
                                            Once your parcel reaches Nigeria, we will contact you for a pickup or delivery. You can also track your parcel to get updates on its location.
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="card">
                            <div class="card-header" id="heading30">
                                <h5 class="mb-0 subtitle">
                                    <button class="btn btn-link collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapse30" aria-expanded="false" aria-controls="collapse30">
                                        Can I send documents through ShareMyBag?
                                    </button>
                                </h5>
                            </div>
                            <div id="collapse30" class="collapse" aria-labelledby="heading30" data-parent="#accordionFaq">
                                <div class="card-body">
                                    <div class="content">
                                        <p>
                                            Yes, you can send documents through ShareMyBag. Please ensure that the documents are properly sealed and protected for transit.
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Repeat the same structure for all remaining FAQs -->
                    </div>

                </div>
            </div>
        </div>
    </div>


    <!-- =================== PRICE CHECKER MODAL =================== -->
    <div class="modal fade" id="priceCheckerModal" tabindex="-1" role="dialog" aria-labelledby="priceCheckerTitle" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content">

                <div class="modal-header text-white" style="background-image: linear-gradient(300deg, rgb(243, 107, 36) 50%, rgb(243, 107, 36) 100%);">
                    <h5 class="modal-title text-white" id="priceCheckerTitle">
                        <i class="ti ti-calculator me-2"></i> Get a Price Estimate
                    </h5>
                </div>

                <div class="modal-body">

                    <!-- INPUT FORM -->
                    <div id="priceCheckerForm">

                        <div class="mb-3">
                            <label class="form-label fw-semibold">From *</label>
                            <select id="pc_origin" class="form-control">
                                <option value="">Select origin</option>
                                <option value="Nigeria">Nigeria</option>
                                <option value="United Kingdom">United Kingdom</option>
                                <option value="Canada">Canada</option>
                            </select>
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-semibold">To *</label>
                            <select id="pc_destination" class="form-control">
                                <option value="">Select destination</option>
                                <option value="Nigeria">Nigeria</option>
                                <option value="United Kingdom">United Kingdom</option>
                                <option value="Canada">Canada</option>
                            </select>
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-semibold">Category *</label>
                            <select id="pc_category" class="form-control">
                                <option value="">Select category</option>
                                <option value="Normal">Normal</option>
                                <option value="Fish/Meat">Fish/Meat (special)</option>
                                <option value="Medication">Medication (special)</option>
                                <option value="Documents/Electronics">Documents/Electronics/Gold (premium)</option>
                            </select>
                            <small class="text-muted" id="pc_category_hint"></small>
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-semibold" id="pc_weight_label">Weight (KG) *</label>
                            <input type="number" id="pc_weight" class="form-control" placeholder="e.g. 5" min="1" max="50" step="0.5" />
                        </div>

                        <div id="pc_error" class="alert alert-danger d-none"></div>

                        <button type="button" class="main-btn primary w-100" id="pc_submit_btn">
                            <span id="pc_btn_text"><i class="ti ti-search me-1"></i> Calculate</span>
                            <span id="pc_spinner" class="d-none"><i class="ti ti-loader ti-spin me-1"></i> Calculating...</span>
                        </button>
                    </div>

                    <!-- RESULTS (hidden until response) -->
                    <div id="priceCheckerResult" class="d-none mt-3">
                        <section id="section-1" class="">
                            <div class="p-3 p-md-4">

                                <div class="estimate-box" style="display: grid; grid-template-columns: repeat(2, 1fr); gap: 15px; text-align: center; margin-bottom: 20px;">
                                    <div class="estimate_icon">
                                        <img src="<?php echo base_url(); ?>assets/website/icons/destination.png" alt="Route" style="width: 40px; margin-bottom: 10px;">
                                        <h4 style="font-size: 14px; font-weight: 600; margin-bottom: 5px;">Route</h4>
                                        <p id="pc_res_route" style="font-size: 13px; margin: 0; color: #666;"></p>
                                    </div>
                                    <div class="estimate_icon">
                                        <img src="<?php echo base_url(); ?>assets/website/icons/package.png" alt="Category" style="width: 40px; margin-bottom: 10px;">
                                        <h4 style="font-size: 14px; font-weight: 600; margin-bottom: 5px;">Category</h4>
                                        <p id="pc_res_category" style="font-size: 13px; margin: 0; color: #666; line-height: 1.2;"></p>
                                    </div>
                                </div>

                                <div class="alert alert-success mb-3 text-center">
                                    <h5 class="mb-0 fw-bold" id="pc_res_total_box"></h5>
                                    <!-- <small class="text-muted" id="pc_result_route"></small> -->
                                </div>

                                <div class="table-responsive mb-3">
                                    <table class="table table-sm table-borderless border-top mb-0 pt-2">
                                        <tbody>
                                            <tr>
                                                <td class="text-muted small">Weight / Qty</td>
                                                <td class="fw-semibold text-end small" id="pc_res_weight"></td>
                                            </tr>
                                            <tr>
                                                <td class="text-muted small">Rate</td>
                                                <td class="fw-semibold text-end small" id="pc_res_rate"></td>
                                            </tr>
                                            <tr>
                                                <td class="text-muted small">Item Cost</td>
                                                <td class="fw-semibold text-end small" id="pc_res_item_price"></td>
                                            </tr>
                                            <tr>
                                                <td class="text-muted small">Service Charge</td>
                                                <td class="fw-semibold text-end small" id="pc_res_service"></td>
                                            </tr>
                                            <tr id="pc_res_special_row" class="d-none">
                                                <td class="text-muted small">Special Fee</td>
                                                <td class="fw-semibold text-warning text-end small" id="pc_res_special"></td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>

                                <p class="text-muted" style="font-size: 13px; text-align: center;" id="pc_disclaimer"></p>

                                <div class="d-flex gap-2 mt-3">
                                    <button type="button" class="btn btn-outline-secondary btn-md flex-fill" id="pc_recalculate_btn">
                                        <i class="ti ti-arrow-left me-1"></i> Recalculate
                                    </button>
                                    <a href="<?php echo site_url('signin'); ?>" class="btn btn-primary btn-md flex-fill text-center m-0" style="line-height: 1.5; padding: 8px 15px;">
                                        <i class="ti ti-bag me-1"></i> Book Now
                                    </a>
                                </div>
                            </div>
                        </section>
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
                        value: 'Documents/Electronics',
                        label: 'Documents/Electronics/Gold (premium)'
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
                        value: 'Documents/Electronics',
                        label: 'Documents/Electronics/Gold (premium)'
                    }
                ]
            };

            var categoryHints = {
                'Normal': '',
                'Duty Free': '',
                'Fish/Meat': 'A special handling fee of £10 / $10 applies to this category.',
                'Medication': 'A special handling fee of £10 / $10 applies to this category.',
                'Documents/Electronics': 'Premium pricing applies. Weight is counted in pieces (PC), not KG.'
            };

            function updateCategoryHintAndUnit() {
                var cat = document.getElementById('pc_category').value;
                var weightLabel = document.getElementById('pc_weight_label');
                var hint = document.getElementById('pc_category_hint');

                weightLabel.textContent = (cat === 'Documents/Electronics') ? 'Quantity (PC) *' : 'Weight (KG) *';
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
            }

            document.getElementById('pc_origin').addEventListener('change', populateEstimateCategories);
            document.getElementById('pc_destination').addEventListener('change', populateEstimateCategories);
            document.getElementById('pc_category').addEventListener('change', updateCategoryHintAndUnit);
            populateEstimateCategories();

            // Calculate button
            document.getElementById('pc_submit_btn').addEventListener('click', function() {
                var origin = document.getElementById('pc_origin').value;
                var destination = document.getElementById('pc_destination').value;
                var category = document.getElementById('pc_category').value;
                var weight = parseFloat(document.getElementById('pc_weight').value);
                var errBox = document.getElementById('pc_error');

                errBox.classList.add('d-none');
                errBox.textContent = '';

                if (!origin || !destination || !category || !weight || weight <= 0) {
                    errBox.textContent = 'Please fill in all fields.';
                    errBox.classList.remove('d-none');
                    return;
                }

                if (origin === destination) {
                    errBox.textContent = 'Origin and destination cannot be the same.';
                    errBox.classList.remove('d-none');
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
                            errBox.textContent = data.msg || 'Something went wrong. Please try again.';
                            errBox.classList.remove('d-none');
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
                        errBox.textContent = 'Network error. Please try again.';
                        errBox.classList.remove('d-none');
                    });
            });

            // Recalculate — show form again
            document.getElementById('pc_recalculate_btn').addEventListener('click', function() {
                document.getElementById('priceCheckerResult').classList.add('d-none');
                document.getElementById('priceCheckerForm').classList.remove('d-none');
                document.getElementById('pc_error').classList.add('d-none');
            });

            // Reset modal state on close
            document.getElementById('priceCheckerModal').addEventListener('hidden.bs.modal', function() {
                document.getElementById('priceCheckerResult').classList.add('d-none');
                document.getElementById('priceCheckerForm').classList.remove('d-none');
                document.getElementById('pc_error').classList.add('d-none');
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
