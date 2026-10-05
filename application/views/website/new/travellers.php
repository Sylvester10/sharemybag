    <?php

    if (isset($_GET['refer']) and $_GET['refer'] != "") {
        $refer = $_GET['refer'];
        $refer_state = "readonly";
    } else {
        $refer = "";
        $refer_state = "";
    }

    ?>


    <main class="smb-traveller-page">
        <section class="smb-traveller-video-hero" aria-labelledby="smb-traveller-title">
            <video id="smb-traveller-video" preload="none" playsinline poster="<?= base_url('assets/website/videos/how-it-works.jpg'); ?>" aria-label="How ShareMyBag works for travellers">
                <source src="<?= base_url('assets/website/videos/how-it-works.mp4'); ?>" type="video/mp4">
                Your browser does not support the video tag.
            </video>
            <div class="smb-traveller-video-overlay">
                <h1 id="smb-traveller-title">Travellers</h1>
                <p>We offer standard market rates and <span>No Extra Charges</span></p>
                <button type="button" id="smb-traveller-play" class="smb-traveller-play"><span class="smb-traveller-play-icon"><i class="las la-play" aria-hidden="true"></i></span><span>See how it works</span></button>
                <p id="smb-traveller-video-error" class="d-none" role="alert">The video could not play. Please try again.</p>
            </div>
        </section>
        <section class="smb-how-it-works smb-traveller-safety" aria-labelledby="smb-traveller-safety-title">
            <div class="container">
                <div class="smb-how-heading">
                    <h2 id="smb-traveller-safety-title">How We Keep You Safe</h2>
                    <p>We take two approaches to security: deterrence and preventive checks.</p>
                </div>
                <ol class="smb-safety-deck">
                    <li class="smb-safety-card">
                        <h3><button type="button" class="smb-safety-trigger" id="smb-safety-trigger-01" aria-expanded="true" aria-controls="smb-safety-panel-01"><span class="smb-safety-number" aria-hidden="true">01</span><span class="smb-safety-icon-badge"><img class="smb-safety-icon" src="<?php echo base_url(); ?>assets/website/icons/apply-transparent.png" width="60" height="60" alt=""></span><span class="smb-safety-title">Verification</span></button></h3>
                        <div class="smb-safety-body" id="smb-safety-panel-01" role="region" aria-labelledby="smb-safety-trigger-01">
                            <div class="smb-safety-body-inner">
                                <p>We verify customers on key routes. Customers sending from Nigeria must submit a proof of ID, a selfie, and a current proof of address.</p>
                            </div>
                        </div>
                    </li>
                    <li class="smb-safety-card">
                        <h3><button type="button" class="smb-safety-trigger" id="smb-safety-trigger-02" aria-expanded="true" aria-controls="smb-safety-panel-02"><span class="smb-safety-number" aria-hidden="true">02</span><span class="smb-safety-icon-badge"><img class="smb-safety-icon" src="<?php echo base_url(); ?>assets/website/icons/conversation-transparent.png" width="60" height="60" alt=""></span><span class="smb-safety-title">Content List</span></button></h3>
                        <div class="smb-safety-body" id="smb-safety-panel-02" role="region" aria-labelledby="smb-safety-trigger-02">
                            <div class="smb-safety-body-inner">
                                <p>Customers must submit a list of items in their parcel. We send it to you before the parcel arrives and tell you how to check each item on the list.</p>
                            </div>
                        </div>
                    </li>
                    <li class="smb-safety-card">
                        <h3><button type="button" class="smb-safety-trigger" id="smb-safety-trigger-03" aria-expanded="true" aria-controls="smb-safety-panel-03"><span class="smb-safety-number" aria-hidden="true">03</span><span class="smb-safety-icon-badge"><img class="smb-safety-icon" src="<?php echo base_url(); ?>assets/website/icons/search-transparent.png" width="60" height="60" alt=""></span><span class="smb-safety-title">Check</span></button></h3>
                        <div class="smb-safety-body" id="smb-safety-panel-03" role="region" aria-labelledby="smb-safety-trigger-03">
                            <div class="smb-safety-body-inner">
                                <p>Check that items on the content list match the content in the parcel. If they do not match, let us know immediately.</p>
                            </div>
                        </div>
                    </li>
                    <li class="smb-safety-card">
                        <h3><button type="button" class="smb-safety-trigger" id="smb-safety-trigger-04" aria-expanded="true" aria-controls="smb-safety-panel-04"><span class="smb-safety-number" aria-hidden="true">04</span><span class="smb-safety-icon-badge"><img class="smb-safety-icon" src="<?php echo base_url(); ?>assets/website/icons/24-7-transparent.png" width="60" height="60" alt=""></span><span class="smb-safety-title">Guidance</span></button></h3>
                        <div class="smb-safety-body" id="smb-safety-panel-04" role="region" aria-labelledby="smb-safety-trigger-04">
                            <div class="smb-safety-body-inner">
                                <p>We provide industry-level guidance on how to check parcels. If we cannot provide guidance on checking an item, we will not let you travel with it.</p>
                            </div>
                        </div>
                    </li>
                    <li class="smb-safety-card">
                        <h3><button type="button" class="smb-safety-trigger" id="smb-safety-trigger-05" aria-expanded="true" aria-controls="smb-safety-panel-05"><span class="smb-safety-number" aria-hidden="true">05</span><span class="smb-safety-icon-badge"><img class="smb-safety-icon" src="<?php echo base_url(); ?>assets/website/icons/package-transparent.png" width="60" height="60" alt=""></span><span class="smb-safety-title">Restricted Goods</span></button></h3>
                        <div class="smb-safety-body" id="smb-safety-panel-05" role="region" aria-labelledby="smb-safety-trigger-05">
                            <div class="smb-safety-body-inner">
                                <p>We only allow certain sensitive goods, such as medications, when they have been purchased from an online pharmacy and sent directly from the pharmacy to you.</p>
                            </div>
                        </div>
                    </li>
                </ol>
            </div>
        </section>
        <div class="smb-traveller-form-section section-padding" id="traveller-form-section">
            <div class="smb-traveller-form-panel">
                <div class="row align-items-start">
                    <div class="col-12 smb-traveller-form-intro">
                        <div class="contact-wrap">
                            <div class="section-title">

                                <p>Please fill the form and an agent will contact you shortly.</p>
                                <h2>Traveller's Form</h2>
                                <!-- <h2>Please fill the traveller's form, and an agent will contact you shortly.</h2> -->
                            </div>
                        </div>
                    </div>
                    <div class="col-12">
                        <div class="quotation-wrap">
                            <div class="quotation-inner">

                                <div class="contactForm">

                                    <?php
                                    $form_attributes = array("id" => "traveller_form", "novalidate" => "novalidate");
                                    echo form_open_multipart('home/add_traveller_ajax', $form_attributes); ?>

                                    <input type="hidden" id="homepage_csrf_name" value="<?php echo html_escape($this->security->get_csrf_token_name()); ?>">
                                    <input type="hidden" id="homepage_csrf_hash" name="<?php echo $this->security->get_csrf_token_name(); ?>" value="<?php echo $this->security->get_csrf_hash(); ?>">

                                    <section class="smb-traveller-field-section" aria-labelledby="smb-travel-details-title">
                                        <h3 id="smb-travel-details-title">Travel details</h3>
                                        <div class="smb-traveller-fields">
                                        <div class="smb-standard-field">
                                            <label for="traveller_location">Travelling from <span class="text-danger" aria-hidden="true">*</span></label>
                                            <select class="nice-select form-control" id="traveller_location" name="location" required>
                                                    <option value="">Select</option>
                                                    <?php
                                                    $countries = countries();
                                                    $country_flags = phone_country_options();
                                                    foreach ($countries as $country) { ?>
                                                        <option value="<?php echo $country; ?>" data-flag="<?= html_escape($country_flags[$country]['flag'] ?? ''); ?>" <?php echo set_select('location', $country); ?>><?php echo $country; ?>
                                                        </option>
                                                    <?php } ?>
                                                </select>
                                            
                                        </div>
                                        <div class="smb-standard-field">
                                            <label for="traveller_destination">Travelling to <span class="text-danger" aria-hidden="true">*</span></label>
                                            <select class="nice-select form-control" id="traveller_destination" name="destination" required>
                                                    <option value="">Select</option>
                                                    <?php
                                                    $countries = countries();
                                                    $country_flags = phone_country_options();
                                                    foreach ($countries as $country) { ?>
                                                        <option value="<?php echo $country; ?>" data-flag="<?= html_escape($country_flags[$country]['flag'] ?? ''); ?>" <?php echo set_select('destination', $country); ?>><?php echo $country; ?>
                                                        </option>
                                                    <?php } ?>
                                                </select>
                                        </div>
                                        <div class="smb-standard-field">
                                            <label for="travelDate">Travel date <span class="text-danger" aria-hidden="true">*</span></label>
                                            <input type="text" id="travelDate" placeholder="Select date" readonly aria-required="true" value="<?= set_value('travel_date') ? html_escape(date('jS \o\f F Y', strtotime(set_value('travel_date')))) : ''; ?>"><input type="hidden" name="travel_date" id="traveller_date_value" value="<?= html_escape(set_value('travel_date')); ?>">
                                        </div>
                                        <div class="smb-standard-field">
                                            <label for="traveller_available_space">Available bag space (KG) <span class="text-danger" aria-hidden="true">*</span></label>
                                            <select class="nice-select form-control" id="traveller_available_space" name="available_space" required>
                                                    <option value="">Select</option>
                                                    <?php for ($i = 1; $i <= 50; $i++) : ?>
                                                        <option value="<?= $i; ?>"><?= $i; ?> KG</option>
                                                    <?php endfor; ?>
                                                </select>
                                        </div>
                                        </div>
                                    </section>
                                    <section class="smb-traveller-field-section" aria-labelledby="smb-traveller-information-title">
                                        <h3 id="smb-traveller-information-title">Traveller information</h3>
                                        <div class="smb-traveller-fields">
                                        <div class="smb-standard-field">
                                            <label for="traveller_fullname">Full name <span class="text-danger" aria-hidden="true">*</span></label>
                                            <input class="form-control" type="text" id="traveller_fullname" name="fullname" placeholder="John Doe" required>
                                        </div>
                                        <div class="smb-standard-field">
                                            <label for="traveller_email">Email address <span class="text-danger" aria-hidden="true">*</span></label>
                                            <input class="form-control" type="email" id="traveller_email" name="email" placeholder="xyz@gmail.com" required>
                                        </div>
                                        <?php $this->load->view('partials/phone_input', array(
                                            'wrapper_class' => 'smb-standard-field',
                                            'field_name' => 'phone',
                                            'country_code_name' => 'c_code1',
                                            'country_code_id' => 'country_code',
                                            'input_id' => 'traveller_phone',
                                            'country_code' => set_value('c_code1', '+44'),
                                            'local_number' => set_value('phone'),
                                            'label' => 'Contact number',
                                            'placeholder' => '7911123456',
                                            'required' => true,
                                        )); ?>
                                        <?php $this->load->view('partials/phone_input', array(
                                            'wrapper_class' => 'smb-standard-field',
                                            'field_name' => 'alt_phone',
                                            'country_code_name' => 'c_code2',
                                            'country_code_id' => 'country_code2',
                                            'input_id' => 'traveller_alt_phone',
                                            'country_code' => set_value('c_code2', '+44'),
                                            'local_number' => set_value('alt_phone'),
                                            'label' => 'Alternative number (optional)',
                                            'placeholder' => '7911123456',
                                            'required' => false,
                                        )); ?>

                                        </div>
                                    </section>
                                    <section class="smb-traveller-documents" aria-label="Itinerary and verification">
                                        <div class="smb-itinerary-row">
                                            <div class="smb-itinerary-fields">
                                                <div><label class="form-label" for="traveller_itinerary_photo">Upload itinerary <span class="text-danger" aria-hidden="true">*</span></label><input type="file" class="form-control align-contents-center" id="traveller_itinerary_photo" name="itinerary_photo" accept=".jpeg,.jpg,.png,.pdf" required>
                                                    <p class="smb-upload-hint">Your itinerary should show your full name. JPG, PNG or PDF; maximum 5 MB.</p>
                                                </div>
                                                <div><label class="form-label" for="traveller_referred_by">Referral code (optional)</label><input type="text" class="form-control" id="traveller_referred_by" name="referred_by" <?php echo $refer_state; ?> value="<?php echo html_escape($refer); ?>" placeholder="example1234"></div>
                                                <div class="smb-document-fields">
                                                    <div><label class="form-label" for="traveller_captcha_code">Captcha code</label><input type="text" class="form-control" id="traveller_captcha_code" name="captcha_code" value="<?php echo $captcha_code; ?>" readonly></div>
                                                    <div><label class="form-label" for="traveller_c_captcha_code">Enter captcha code here <span class="text-danger" aria-hidden="true">*</span></label><input type="text" class="form-control" id="traveller_c_captcha_code" name="c_captcha_code" required placeholder=""></div>
                                                </div>
                                            </div>
                                            <div class="smb-itinerary-preview-column">
                                                <div id="smb-itinerary-preview" class="smb-itinerary-preview" aria-live="polite"><i class="las la-file-upload" aria-hidden="true"></i><span>Your itinerary preview will appear here.</span></div>
                                                <button type="button" class="smb-itinerary-remove d-none" id="smb-itinerary-remove"><i class="las la-trash-alt" aria-hidden="true"></i> Remove itinerary</button>
                                            </div>
                                        </div>
                                    </section>
                                    <div class="smb-traveller-agreement form-check d-flex">
                                        <input type="checkbox" id="flexCheckChecked" required>
                                        <label class="form-label" for="flexCheckChecked">I have read and agree to the <a href="<?= base_url('traveller-agreement'); ?>" target="_blank" rel="noopener">Traveller Agreement</a></label>
                                    </div>
                                    <div id="status_msg" role="alert"></div>
                                    <button class="smb-traveller-button" type="submit" id="submit" aria-label="Submit"><span id="smb-traveller-submit-label">Submit</span><span class="spinner-border spinner-border-sm d-none" id="search-spinner" aria-hidden="true"></span></button>

                                    <?php echo form_close(); ?>
                                </div>

                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="modal fade smb-estimate-modal" id="travellerSuccessModal" tabindex="-1" role="dialog" aria-labelledby="travellerSuccessModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered traveller-success-modal" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h4 class="modal-title" id="travellerSuccessModalLabel">Traveller Request Sent</h4>
                        <button type="button" class="smb-estimate-close" data-bs-dismiss="modal" aria-label="Close"><i class="las la-times" aria-hidden="true"></i></button>
                    </div>
                    <div class="modal-body">
                        <div class="traveller-success-modal__hero">
                            <span class="traveller-success-modal__icon"><i class="las la-check-circle"></i></span>
                            <h5>Thank you for submitting your details.</h5>
                            <p>One of our agents will contact you shortly.</p>
                        </div>

                        <div class="traveller-success-modal__tips">
                            <h6>What happens next?</h6>
                            <ul>
                                <li>If approved, We’ll advertise your space up until 24 hrs before your flight. You can request to change this at any time.</li>
                                <li>After payment, customers will drop their parcels at your specified location no later than 24hrs before your flight.</li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>


        <div class="smb-faq-section">
            <div class="container">
                <div class="row">
                    <div class="col-xl-12 col-lg-12">
                        <div class="smb-faq-heading">
                            <p>If you don't know, find out</p>
                            <h2>Frequently Asked Questions</h2>
                        </div>
                        <div class="smb-faq-list" id="accordionFaq">

                            <div class="smb-faq-item">
                                <div class="smb-faq-header" id="heading1">
                                    <h3 class="smb-faq-question-heading">
                                        <button class="smb-faq-question collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapse1" aria-expanded="false" aria-controls="collapse1"><span class="smb-faq-question-text">How does it work?</span><span class="smb-faq-toggle" aria-hidden="true"></span></button>
                                    </h3>
                                </div>
                                <div id="collapse1" class="collapse" aria-labelledby="heading1" data-bs-parent="#accordionFaq">
                                    <div class="smb-faq-answer">
                                        <div class="smb-faq-answer-content">
                                            <p>We’ll advertise your space on our website. Once a verified customer buys space in your bag, they’ll receive your drop off address in their email. They are required to drop their items at the address no later than 24hrs before your flight.</p>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="smb-faq-item">
                                <div class="smb-faq-header" id="heading2">
                                    <h3 class="smb-faq-question-heading">
                                        <button class="smb-faq-question collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapse2" aria-expanded="false" aria-controls="collapse2"><span class="smb-faq-question-text">How do you ensure traveller’s security?</span><span class="smb-faq-toggle" aria-hidden="true"></span></button>
                                    </h3>
                                </div>
                                <div id="collapse2" class="collapse" aria-labelledby="heading2" data-bs-parent="#accordionFaq">
                                    <div class="smb-faq-answer">
                                        <div class="smb-faq-answer-content">
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

                            <div class="smb-faq-item">
                                <div class="smb-faq-header" id="heading3">
                                    <h3 class="smb-faq-question-heading">
                                        <button class="smb-faq-question collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapse3" aria-expanded="false" aria-controls="collapse3"><span class="smb-faq-question-text">When the parcel gets to the UK, how do you collect it?</span><span class="smb-faq-toggle" aria-hidden="true"></span></button>
                                    </h3>
                                </div>
                                <div id="collapse3" class="collapse" aria-labelledby="heading3" data-bs-parent="#accordionFaq">
                                    <div class="smb-faq-answer">
                                        <div class="smb-faq-answer-content">
                                            <p>We’ll arrange Royal Mail or another courier service to collect the parcel from your address and deliver it to the parcel owner.</p>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="smb-faq-item">
                                <div class="smb-faq-header" id="heading4">
                                    <h3 class="smb-faq-question-heading">
                                        <button class="smb-faq-question collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapse4" aria-expanded="false" aria-controls="collapse4"><span class="smb-faq-question-text">How do you pay?</span><span class="smb-faq-toggle" aria-hidden="true"></span></button>
                                    </h3>
                                </div>
                                <div id="collapse4" class="collapse" aria-labelledby="heading4" data-bs-parent="#accordionFaq">
                                    <div class="smb-faq-answer">
                                        <div class="smb-faq-answer-content">
                                            <p>There are two payment options:</p>
                                            <ul class="project-solutions-list">
                                                <li><i class="las la-minus"></i><b>£5 per kilo:</b> You can select in advance the type of things you are happy to travel with. We can’t guarantee we’ll fill up the space.</li>
                                                <li><i class="las la-minus"></i><b>Guaranteed £115 per 23kg bag:</b> You guarantee us 23kg space and agree to carry all legal items, including food items. In turn, we guarantee you £115 even if we can’t fill up your space.</li>
                                            </ul>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="smb-faq-item">
                                <div class="smb-faq-header" id="heading5">
                                    <h3 class="smb-faq-question-heading">
                                        <button class="smb-faq-question collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapse5" aria-expanded="false" aria-controls="collapse5"><span class="smb-faq-question-text">When do I expect to get paid?</span><span class="smb-faq-toggle" aria-hidden="true"></span></button>
                                    </h3>
                                </div>
                                <div id="collapse5" class="collapse" aria-labelledby="heading5" data-bs-parent="#accordionFaq">
                                    <div class="smb-faq-answer">
                                        <div class="smb-faq-answer-content">
                                            <p>You’ll get paid 24 hours after your arrival. This gives us time to ensure all parcels arrived intact and book collections by local courier services.</p>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="smb-faq-item">
                                <div class="smb-faq-header" id="heading6">
                                    <h3 class="smb-faq-question-heading">
                                        <button class="smb-faq-question collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapse6" aria-expanded="false" aria-controls="collapse6"><span class="smb-faq-question-text">What do you expect from travellers?</span><span class="smb-faq-toggle" aria-hidden="true"></span></button>
                                    </h3>
                                </div>
                                <div id="collapse6" class="collapse" aria-labelledby="heading6" data-bs-parent="#accordionFaq">
                                    <div class="smb-faq-answer">
                                        <div class="smb-faq-answer-content">
                                            <ul class="project-solutions-list">
                                                <li><i class="las la-minus"></i>We expect that when you tell us you want to share a certain number of kg, you don’t change from this unless necessary. Inform us well in advance if you need to change.</li>
                                                <li><i class="las la-minus"></i>Treat every parcel with respect.</li>
                                            </ul>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="smb-faq-item">
                                <div class="smb-faq-header" id="heading7">
                                    <h3 class="smb-faq-question-heading">
                                        <button class="smb-faq-question collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapse7" aria-expanded="false" aria-controls="collapse7"><span class="smb-faq-question-text">What kind of items will I be carrying to the UK?</span><span class="smb-faq-toggle" aria-hidden="true"></span></button>
                                    </h3>
                                </div>
                                <div id="collapse7" class="collapse" aria-labelledby="heading7" data-bs-parent="#accordionFaq">
                                    <div class="smb-faq-answer">
                                        <div class="smb-faq-answer-content">
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

    </main>
