    <?php

    if (isset($_GET['refer']) and $_GET['refer'] != "") {
        $refer = $_GET['refer'];
        $refer_state = "readonly";
    } else {
        $refer = "";
        $refer_state = "";
    }

    ?>

    <!-- Breadcrumb Area -->
    <div class="breadcrumb-area about-bg">
        <div class="overlay"></div>
        <div class="container">
            <div class="row">
                <div class="col-lg-6 col-md-12">
                    <div class="breadcrumb-title">
                        <h1>Travellers</h1>
                        <h6>We Offer Market Standard Rates and <span class="tw-text-color-primary">No Extra Charges</span></h6>
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
                        <p>It’s simple. ShareMyBag connects verified senders to you.</p>
                        <h2>How it Works</h2>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-lg-3 col-md-6 col-sm-12 wow fadeInUp animated" data-wow-delay="100ms">
                    <div class="single-process-area text-center">
                        <div class="process-icon">
                            <img src="<?php echo base_url(); ?>assets/website/icons/apply.png" alt="">
                        </div>
                        <h4>Register with us</h4>
                        <p>We’ll ask you about your upcoming trip, the drop off address and account where we will pay your money.</p>
                        <span class="count-big">01</span>
                    </div>
                </div>
                <div class="col-lg-3 col-md-6 col-sm-12 wow fadeInUp animated" data-wow-delay="200ms">
                    <div class="single-process-area text-center">
                        <div class="process-icon">
                            <img src="<?php echo base_url(); ?>assets/website/icons/conversation.png" alt="">
                        </div>
                        <h4>Receive connections</h4>
                        <p>We will connect vetted senders to you. Senders will drop off their parcels at your chosen drop off point.</p>
                        <span class="count-big">02</span>
                    </div>
                </div>
                <div class="col-lg-3 col-md-6 col-sm-12 wow fadeInUp animated" data-wow-delay="300ms">
                    <div class="single-process-area text-center">
                        <div class="process-icon">
                            <img src="<?php echo base_url(); ?>assets/website/icons/package.png" alt="">
                        </div>
                        <h4>Check packages</h4>
                        <p>Check parcel content and weight to ensure you are happy to travel with them.</p>
                        <span class="count-big">03</span>
                    </div>
                </div>
                <div class="col-lg-3 col-md-6 col-sm-12 wow fadeInUp animated" data-wow-delay="400ms">
                    <div class="single-process-area text-center">
                        <div class="process-icon">
                            <img src="<?php echo base_url(); ?>assets/website/icons/delivery-man.png" alt="">
                        </div>
                        <h4>Delivery</h4>
                        <p>Once you arrive at your destination, we will arrange local couriers to collect the parcels from you and deliver to the receiver. We will release payment to your chosen account.</p>
                        <span class="count-big">04</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Travellers Form  -->
    <div class="quotation-area section-padding">
        <div class="container">
            <div class="row">
                <div class="col-xl-12 col-lg-12 col-12">
                    <div class="contact-wrap text-center">
                        <div class="section-title">
                            <p>Please fill the form and an agent will contact you shortly.</p>
                            <h2 class="text-white">Traveller's Form</h2>
                        </div>
                    </div>
                </div>
                <div class="col-xl-12 col-lg-12 col-12 wow fadeInUp animated" data-wow-delay="200ms">
                    <div class="quotation-wrap">
                        <div class="quotation-inner">

                            <div class="contactForm">

                                <?php
                                $form_attributes = array("id" => "traveller_form");
                                echo form_open_multipart('home/add_traveller_ajax', $form_attributes); ?>

                                <div class="row">

                                    <div class="col-12 col-md-6 display_flag_on_select">
                                        <label class="form-label"> Location
                                            <span class="location-flag cf-16 cf-ng me-1 d-none"></span>
                                            <span class="location-flag cf-16 cf-gb me-1 d-none"></span>
                                        </label>
                                        <select class="nice-select form-control" name="location">
                                            <option value="">Select</option>
                                            <option value="Nigeria">Nigeria</option>
                                            <option value="United Kingdom">United Kingdom</option>
                                        </select>
                                    </div>

                                    <div class="col-12 col-md-6 display_flag_on_select">
                                        <label class="form-label"> Destination
                                            <span class="destination-flag cf-16 cf-ng me-1 d-none"></span>
                                            <span class="destination-flag cf-16 cf-gb me-1 d-none"></span>
                                        </label>
                                        <select class="nice-select form-control" name="destination">
                                            <option value="">Select</option>
                                            <option value="Nigeria">Nigeria</option>
                                            <option value="United Kingdom">United Kingdom</option>
                                        </select>
                                    </div>


                                    <div class="col-12 col-md-6">
                                        <label class="form-label">Full Name </label>
                                        <input class="form-control" type="text" name="fullname" placeholder="John Doe" required>
                                    </div>

                                    <div class="col-12 col-md-6">
                                        <label class="form-label">Travel Date </label>
                                        <input type="date" name="travel_date" id="travelDate" value="<?php echo set_value('travel_date'); ?>" required>
                                    </div>

                                    <div class="col-12 col-md-6">
                                        <label class="form-label">E-mail </label>
                                        <input class="form-control" type="email" name="email" placeholder="xyz@gmail.com" required>
                                    </div>

                                    <div class="col-12 col-md-6">
                                        <label class="form-label"> Available Bag Space </label>
                                        <select class="nice-select form-control" name="available_space">
                                            <option value="">Select</option>
                                            <option value="1">1 KG</option>
                                            <option value="2">2 KG</option>
                                            <option value="3">3 KG</option>
                                            <option value="4">4 KG</option>
                                            <option value="5">5 KG</option>
                                            <option value="6">6 KG</option>
                                            <option value="7">7 KG</option>
                                            <option value="8">8 KG</option>
                                            <option value="9">9 KG</option>
                                            <option value="10">10 KG</option>
                                            <option value="11">11 KG</option>
                                            <option value="12">12 KG</option>
                                            <option value="13">13 KG</option>
                                            <option value="14">14 KG</option>
                                            <option value="15">15 KG</option>
                                            <option value="16">16 KG</option>
                                            <option value="17">17 KG</option>
                                            <option value="18">18 KG</option>
                                            <option value="19">19 KG</option>
                                            <option value="20">20 KG</option>
                                            <option value="21">21 KG</option>
                                            <option value="22">22 KG</option>
                                            <option value="23">23 KG</option>
                                        </select>
                                    </div>

                                    <div class="col-12 col-md-6">
                                        <label class="form-label">Contact Number </label>
                                        <div class="input-group">
                                            <div class=" p-0">
                                                <select id="country_code" class="nice-select form-control bg-transparent" name="c_code1">
                                                    <option value="+234" data-flag="cf-16 cf-ng ms-1">+234 </option>
                                                    <option value="+44" data-flag="cf-16 cf-gb ms-1">+44 </option>
                                                </select>
                                            </div>
                                            <input class="form-control" type="tel" name="phone" placeholder="8011140017" required maxlength="10" pattern="\d{10}" title="Enter a valid 10-digit phone number">
                                        </div>
                                    </div>

                                    <div class="col-12 col-md-6">
                                        <label class="form-label">Alternative Number (optional) </label>
                                        <div class="input-group">
                                            <div class=" p-0">
                                                <select id="country_code2" class="nice-select form-control bg-transparent" name="c_code2">
                                                    <option value="+234" data-flag="cf-16 cf-ng ms-1">+234 </option>
                                                    <option value="+44" data-flag="cf-16 cf-gb ms-1">+44 </option>
                                                </select>
                                            </div>
                                            <input class="form-control" type="tel" name="alt_phone" placeholder="8011140017" maxlength="10" pattern="\d{10}" title="Enter a valid 10-digit phone number">
                                        </div>
                                    </div>

                                    <div class="col-12 col-md-6">
                                        <label class="form-label">Upload Itinerary <small>(The itinerary should show the traveller's full name)</small></label>
                                        <input type="file" class="form-control align-contents-center" name="itinerary_photo" accept=".jpeg,.jpg,.png,.pdf" required>
                                    </div>

                                    <div class="col-12 col-md-6">
                                        <label class="form-label">Referral Code (optional)</label>
                                        <input type="text" class="form-control" name="referred_by" <?php echo $refer_state; ?> value="<?php echo $refer; ?>" placeholder="example1234">
                                    </div>

                                    <!-- <div class="col-12 col-md-6 payment_types">
                                        <label class="form-label">How do you want to be paid? (Applies to NG to UK route only)</label>
                                        <div class="d-flex">
                                            <div>
                                                <input type="radio" id="option1" name="payment_type" value="£5_per_kg">
                                                <label for="option1">£5 per kg</label>
                                            </div>
                                            <div>
                                                <input type="radio" id="option2" name="payment_type" value="guaranteed_£115">
                                                <label for="option2">Guaranteed £115 for 23kg</label>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="col-12 col-md-6 payment_types2">
                                        <div class="description d-none" id="description_card">
                                            <label id="description_1" class="form-label d-none">
                                                <b>Your commitment to us:</b> You can tell us beforehand what you’d like to carry.
                                                <br>
                                                <br>
                                                <b>Our commitment to you:</b> We can’t guarantee a full bag.
                                            </label>
                                            <label id="description_2" class="form-label d-none">
                                                <b>Your commitment to us:</b> You’ll guarantee 23kg and are happy to carry food items including fish.
                                                <br>
                                                <br>
                                                <b>Our commitment to you:</b> We’ll guarantee you this payment even if we are unable to fill up your bag.
                                            </label>
                                        </div>
                                    </div> -->

                                    <div class="col-12">
                                        <div class="form-check d-flex justify-content-center mb-2">
                                            <input class=" me-2" type="checkbox" value="" id="flexCheckChecked" required>
                                            <label class="form-label" for="flexCheckChecked">
                                                I have read and agree to the
                                                <a href="<?php echo base_url('traveller-agreement'); ?>" target="_blank" class="text-decoration-none">Traveller Agreement</a>
                                            </label>
                                        </div>
                                    </div>

                                    <div class="mb-0 mt-20" id="status_msg"></div>

                                    <div class="col-12 mt-3">
                                        <button class="main-btn primary" type="submit" id="submit">
                                            Submit <span class="spinner-border spinner-border-sm text-light ms-2 d-none" id="search-spinner" role="status" aria-hidden="true"></span>
                                        </button>
                                    </div>

                                </div>

                                <?php echo form_close(); ?>
                            </div>

                        </div>
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
                                        <p>We’ll advertise your space on our website. Once a verified customer buys space in your bag, they’ll receive your drop off address in their email. They are required to drop their items at the address no later than 24hrs before your flight.</p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="card">
                            <div class="card-header" id="heading2">
                                <h5 class="mb-0 subtitle">
                                    <button class="btn btn-link collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapse2" aria-expanded="false" aria-controls="collapse2">
                                        How do you ensure traveller’s security?
                                    </button>
                                </h5>
                            </div>
                            <div id="collapse2" class="collapse" aria-labelledby="heading2" data-parent="#accordionFaq">
                                <div class="card-body">
                                    <div class="content">
                                        <p>We have 2 ways of keeping our traveller’s secure: Deter and Prevent.</p>
                                        <ul class="project-solutions-list">
                                            <b>Deterrence:</b>
                                            <li><i class="las la-minus"></i>We collect proof of ID from customers buying space from a traveller on the Nigeria-UK route. We only accept UK government-issued ID.</li>
                                            <li><i class="las la-minus"></i>We confirm the name on their payment method with the name on their ID.</li>
                                            <li><i class="las la-minus"></i>Customers are informed that the address they provide during drop-off is where their parcel will be sent. They won’t be allowed to change this without our approval.</li>
                                            <b>Prevent:</b>
                                            <li><i class="las la-minus"></i>We’ll send you a content list from each parcel.</li>
                                            <li><i class="las la-minus"></i>In the content list you receive, there’ll be checking guidelines for each item. Please follow these guidelines closely.</li>
                                            <li><i class="las la-minus"></i>We can send you a traveller’s security pack containing cocaine detection wipes. These cost £2.99 and will be deducted from your payout.</li>
                                        </ul>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="card">
                            <div class="card-header" id="heading3">
                                <h5 class="mb-0 subtitle">
                                    <button class="btn btn-link collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapse3" aria-expanded="false" aria-controls="collapse3">
                                        When the parcel gets to the UK, how do you collect it?
                                    </button>
                                </h5>
                            </div>
                            <div id="collapse3" class="collapse" aria-labelledby="heading3" data-parent="#accordionFaq">
                                <div class="card-body">
                                    <div class="content">
                                        <p>We’ll arrange Royal Mail or another courier service to collect the parcel from your address and deliver it to the parcel owner.</p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="card">
                            <div class="card-header" id="heading4">
                                <h5 class="mb-0 subtitle">
                                    <button class="btn btn-link collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapse4" aria-expanded="false" aria-controls="collapse4">
                                        How do you pay?
                                    </button>
                                </h5>
                            </div>
                            <div id="collapse4" class="collapse" aria-labelledby="heading4" data-parent="#accordionFaq">
                                <div class="card-body">
                                    <div class="content">
                                        <p>There are two payment options:</p>
                                        <ul class="project-solutions-list">
                                            <li><i class="las la-minus"></i><b>£5 per kilo:</b> You can select in advance the type of things you are happy to travel with. We can’t guarantee we’ll fill up the space.</li>
                                            <li><i class="las la-minus"></i><b>Guaranteed £115 per 23kg bag:</b> You guarantee us 23kg space and agree to carry all legal items, including food items. In turn, we guarantee you £115 even if we can’t fill up your space.</li>
                                        </ul>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="card">
                            <div class="card-header" id="heading5">
                                <h5 class="mb-0 subtitle">
                                    <button class="btn btn-link collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapse5" aria-expanded="false" aria-controls="collapse5">
                                        When do I expect to get paid?
                                    </button>
                                </h5>
                            </div>
                            <div id="collapse5" class="collapse" aria-labelledby="heading5" data-parent="#accordionFaq">
                                <div class="card-body">
                                    <div class="content">
                                        <p>You’ll get paid 24 hours after your arrival. This gives us time to ensure all parcels arrived intact and book collections by local courier services.</p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="card">
                            <div class="card-header" id="heading6">
                                <h5 class="mb-0 subtitle">
                                    <button class="btn btn-link collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapse6" aria-expanded="false" aria-controls="collapse6">
                                        What do you expect from travellers?
                                    </button>
                                </h5>
                            </div>
                            <div id="collapse6" class="collapse" aria-labelledby="heading6" data-parent="#accordionFaq">
                                <div class="card-body">
                                    <div class="content">
                                        <ul class="project-solutions-list">
                                            <li><i class="las la-minus"></i>We expect that when you tell us you want to share a certain number of kg, you don’t change from this unless necessary. Inform us well in advance if you need to change.</li>
                                            <li><i class="las la-minus"></i>Treat every parcel with respect.</li>
                                        </ul>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="card">
                            <div class="card-header" id="heading7">
                                <h5 class="mb-0 subtitle">
                                    <button class="btn btn-link collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapse7" aria-expanded="false" aria-controls="collapse7">
                                        What kind of items will I be carrying to the UK?
                                    </button>
                                </h5>
                            </div>
                            <div id="collapse7" class="collapse" aria-labelledby="heading7" data-parent="#accordionFaq">
                                <div class="card-body">
                                    <div class="content">
                                        <p>Here’s a list of items customers have sent to the UK through other travellers:</p>
                                        <ul class="project-solutions-list">
                                            <li><i class="las la-minus"></i>Wigs</li>
                                            <li><i class="las la-minus"></i>Tailored clothing</li>
                                            <li><i class="las la-minus"></i>Snail (frozen and fried)</li>
                                            <li><i class="las la-minus"></i>Fruits</li>
                                            <li><i class="las la-minus"></i>Guinea fowl</li>
                                            <li><i class="las la-minus"></i>False nails</li>
                                            <li><i class="las la-minus"></i>Lip gloss</li>
                                            <li><i class="las la-minus"></i>Batteries</li>
                                            <li><i class="las la-minus"></i>Mobile phones</li>
                                            <li><i class="las la-minus"></i>Laptops</li>
                                            <li><i class="las la-minus"></i>Medication</li>
                                            <li><i class="las la-minus"></i>Garri</li>
                                            <li><i class="las la-minus"></i>Cigarettes</li>
                                            <li><i class="las la-minus"></i>Dried fish/crayfish</li>
                                            <li><i class="las la-minus"></i>Black soap</li>
                                            <li><i class="las la-minus"></i>Agbo</li>
                                            <li><i class="las la-minus"></i>Oil</li>
                                            <li><i class="las la-minus"></i>Banga puree</li>
                                            <li><i class="las la-minus"></i>Perfumes</li>
                                        </ul>
                                    </div>
                                </div>
                            </div>
                        </div>

                    </div>

                </div>
            </div>
        </div>
    </div>