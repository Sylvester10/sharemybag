<?php
defined('BASEPATH') or exit('No direct script access allowed');

// Paste public HTTPS image URLs or project paths (assets/general/...) between the quotes.
// PNG or JPG recommended. Project paths become public HTTPS URLs in sent emails.
// Suggested size: 1200 × 480px. Images fill the card width without cropping.
// Leave a URL empty to show a blank image placeholder in that email.
return array(
    'documents_under_review' => 'assets/general/pending3.png',
    'identity_verification_successful' => 'assets/general/approve3.png',
    'identity_verification_unsuccessful' => 'assets/general/reject3.png',
    'traveller_approved' => 'assets/general/traveler_success.jpg',
);
