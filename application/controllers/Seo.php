<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Seo extends MY_Controller
{
    public function __construct()
    {
        parent::__construct();
    }

    public function sitemap()
    {
        $pages = [
            ['loc' => base_url(), 'lastmod' => '2026-11-02'],
            ['loc' => base_url('travellers'), 'lastmod' => '2026-11-02'],
            ['loc' => base_url('track'), 'lastmod' => '2026-11-02'],
            ['loc' => base_url('investors'), 'lastmod' => '2026-11-02'],
            ['loc' => base_url('prohibited-items'), 'lastmod' => '2026-11-02'],
            ['loc' => base_url('waiver'), 'lastmod' => '2026-11-02'],
            ['loc' => base_url('traveller-agreement'), 'lastmod' => '2026-10-01'],
            ['loc' => base_url('terms-of-use'), 'lastmod' => '2026-10-01'],
            ['loc' => base_url('terms-and-conditions'), 'lastmod' => '2026-10-01'],
            ['loc' => base_url('privacy-policy'), 'lastmod' => '2026-10-01'],
            ['loc' => base_url('cookies'), 'lastmod' => '2026-10-01'],
        ];

        $this->output
            ->set_content_type('application/xml', 'utf-8')
            ->set_output($this->load->view('sitemap.xml', ['pages' => $pages], true));
    }

    public function robots()
    {
        $this->output
            ->set_content_type('text/plain', 'utf-8')
            ->set_output($this->load->view('robots.txt', null, true));
    }

    public function schema()
    {
        $this->output
            ->set_content_type('application/ld+json', 'utf-8')
            ->set_output(json_encode($this->get_schema(), JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT));
    }
}
