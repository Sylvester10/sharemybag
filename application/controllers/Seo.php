<?php
defined('BASEPATH') or exit('No direct script access allowed');


/* ===== Documentation ===== 
Name: Home
Role: Controller
Description: Controls access to SEO
Author: Sylvester Esso Nmakwe
Date Created: 125th July, 2025
*/



class Seo extends MY_Controller
{
    public function __construct()
    {
        parent::__construct();
    }


    public function sitemap()
    {
        $this->output->set_content_type('application/xml', 'utf-8')->set_output(
            $this->load->view('sitemap', [
                'pages' => [
                    base_url(),
                    base_url('travellers'),
                    base_url('investors'),
                    base_url('prohibited-items'),
                    base_url('terms-of-use'),
                    base_url('terms-and-conditions'),
                    base_url('privacy-policy'),
                    base_url('cookies'),
                    base_url('traveller-agreement'),
                    base_url('signin'),
                ],
                'lastmod' => date('Y-m-d')
            ], true)
        );
    }

    public function robots()
    {
        $this->output
            ->set_content_type('text/plain', 'utf-8')
            ->set_output($this->load->view('robots.txt', null, true));
    }

    public function schema()
    {
        $schema = [
            "@context" => "https://schema.org",
            "@type" => "DeliveryService",
            "name" => business_name,
            "url" => base_url(),
            "logo" => business_logo,
            "contactPoint" => [
                [
                    "@type" => "ContactPoint",
                    "telephone" => business_phone_number,
                    "contactType" => "Customer Service",
                    "areaServed" => "NG",
                    "availableLanguage" => ["English"]
                ],
                [
                    "@type" => "ContactPoint",
                    "telephone" => business_phone_number2,
                    "contactType" => "Customer Service",
                    "areaServed" => "NG",
                    "availableLanguage" => ["English"]
                ]
            ],
            "sameAs" => [
                business_facebook,
                business_instagram,
            ]
        ];

        $this->output->set_content_type('application/ld+json', 'utf-8')->set_output(json_encode($schema, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT));
    }
}
