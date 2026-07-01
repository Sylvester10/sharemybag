<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Invoice extends MY_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->model('booking_read_model');
        $this->load->model('user_read_model');
        $this->load->library('booking_presenter');
    }

    public function booking($id)
    {
        $this->user_restricted();
        $booking = $this->getUserBooking($id);
        if (!$booking) {
            redirect('history');
            return;
        }

        $data = $this->buildInvoiceViewData($booking, 'user');
        $this->load->view('invoices/booking_invoice', $data);
    }

    public function download($id)
    {
        $this->user_restricted();
        $booking = $this->getUserBooking($id);
        if (!$booking) {
            redirect('history');
            return;
        }

        $this->streamPdf($booking, 'user');
    }

    public function admin_booking($id)
    {
        $this->admin_restricted();
        $this->admin_role_restricted(['super_admin', 'customer_support']);
        $booking = $this->getAdminBooking($id);
        if (!$booking) {
            redirect('admin_bookings');
            return;
        }

        $data = $this->buildInvoiceViewData($booking, 'admin');
        $this->load->view('invoices/booking_invoice', $data);
    }

    public function admin_download($id)
    {
        $this->admin_restricted();
        $this->admin_role_restricted(['super_admin', 'customer_support']);
        $booking = $this->getAdminBooking($id);
        if (!$booking) {
            redirect('admin_bookings');
            return;
        }

        $this->streamPdf($booking, 'admin');
    }

    private function getUserBooking($id)
    {
        $booking = $this->booking_read_model->get_booking_details_by_id($id);
        if (!$booking || payment_status_normalize($booking->payment_status) !== 'completed') {
            return null;
        }

        $user = $this->user_read_model->get_user_details($this->session->email);
        if (!$user || (int) $booking->user_id !== (int) $user->id) {
            return null;
        }

        return $booking;
    }

    private function getAdminBooking($id)
    {
        $booking = $this->booking_read_model->get_booking_details_by_id($id);
        if (!$booking || payment_status_normalize($booking->payment_status) !== 'completed') {
            return null;
        }

        return $booking;
    }

    private function buildInvoiceViewData($booking, $viewer, $showActions = true, $forPdf = false)
    {
        $currency = currency_code_normalize($booking->currency ?: 'GBP');
        $symbol = currency_symbol($currency);
        $invoiceNumber = $booking->tracking_id ?: ('SMB-' . str_pad((int) $booking->id, 4, '0', STR_PAD_LEFT));
        $isAdmin = $viewer === 'admin';
        $selectedSpace = (float) $booking->selected_space;
        $travellerPayout = booking_stored_traveller_commission($booking);
        $serviceCharge = (float) $booking->service_charge;
        $insurance = (float) $booking->insurance;
        $paymentMethod = payment_method_normalize($booking->payment_method);
        $storedVat = (float) $booking->vat;
        $baseTotal = max(0, (float) $booking->total_amount - $storedVat);
        $shareMyBagCommission = booking_platform_commission_amount($baseTotal, $travellerPayout, $serviceCharge, $insurance);
        $vat = booking_vat_amount($paymentMethod, $shareMyBagCommission, $serviceCharge);
        $travellerRate = $selectedSpace > 0 ? $travellerPayout / $selectedSpace : 0;
        $commissionRate = $selectedSpace > 0 ? $shareMyBagCommission / $selectedSpace : 0;
        $platformChargeTotal = $shareMyBagCommission + $serviceCharge;
        $paymentMethodLabel = $this->paymentMethodLabel($booking->payment_method);
        $routeLabel = $this->buildRouteLabel($booking);

        return array(
            'title' => 'Invoice ' . $invoiceNumber,
            'booking' => $booking,
            'invoice_number' => $invoiceNumber,
            'invoice_filename' => $this->invoiceFilename($booking),
            'currency' => $currency,
            'currency_symbol' => $symbol,
            'show_actions' => $showActions,
            'download_url' => $isAdmin ? base_url('admin/invoice/download/' . $booking->id) : base_url('invoice/download/' . $booking->id),
            'back_url' => $isAdmin ? base_url('admin_bookings/completed_bookings') : base_url('history'),
            'back_label' => $isAdmin ? 'Back to Bookings' : 'Back to History',
            'payment_method_label' => $paymentMethodLabel,
            'payment_method_key' => $paymentMethod,
            'payment_status_label' => ucfirst(payment_status_normalize($booking->payment_status)),
            'route_label' => $routeLabel,
            'pickup_address' => $booking->agent_address . ', ' . $booking->agent_locality . ', ' . $booking->agent_postcode,
            'dropoff_address' => $booking->receiver_address . ', ' . $booking->receiver_locality . ', ' . $booking->receiver_postcode,
            'viewer' => $viewer,
            'invoice_css' => $this->getInvoiceCss($forPdf),
            'invoice_logo_uri' => $this->getInvoiceLogoUri(),
            'invoice_paid_stamp_uri' => $this->getInvoicePaidStampUri(),
            'delivery_fee_total' => $travellerPayout,
            'delivery_fee_rate' => $travellerRate,
            'sharemybag_commission_total' => $shareMyBagCommission,
            'sharemybag_commission_rate' => $commissionRate,
            'service_fee_total' => $serviceCharge,
            'vat_total' => $vat,
            'insurance_total' => $insurance,
            'platform_charge_total' => $platformChargeTotal,
            'vat_applies' => booking_payment_requires_vat($paymentMethod),
            'weight_label' => rtrim(rtrim(number_format($selectedSpace, 2), '0'), '.') . ' kg',
        );
    }

    private function streamPdf($booking, $viewer)
    {
        $data = $this->buildInvoiceViewData($booking, $viewer, false, true);
        $html = $this->load->view('invoices/booking_invoice', $data, true);

        $options = new \Dompdf\Options();
        $options->set('isRemoteEnabled', false);
        $options->set('isHtml5ParserEnabled', true);
        $options->setDefaultFont('Helvetica');

        $dompdf = new \Dompdf\Dompdf($options);
        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();
        $dompdf->stream($this->invoicePdfFilename($booking), array('Attachment' => 1));
    }

    private function paymentMethodLabel($paymentMethod)
    {
        switch ($paymentMethod) {
            case 'stripe':
                return 'Stripe';
            case 'paystack':
                return 'Paystack';
            case 'offline':
                return 'Offline';
            default:
                return 'Bank';
        }
    }

    private function invoiceFilename($booking)
    {
        $reference = $booking->tracking_id ?: ('booking-' . $booking->id);
        return 'invoice-' . strtolower($reference) . '.html';
    }

    private function invoicePdfFilename($booking)
    {
        $reference = $booking->tracking_id ?: ('booking-' . $booking->id);
        return 'invoice-' . strtolower($reference) . '.pdf';
    }

    private function getInvoiceCss($forPdf = false)
    {
        $cssPath = FCPATH . 'assets/general/css/invoice.css';
        if (!file_exists($cssPath)) {
            return '';
        }

        $css = file_get_contents($cssPath);

        if ($forPdf) {
            $css = preg_replace('/@import\s+url\([^)]+\);\s*/i', '', $css);
            $css = preg_replace('/font-family\s*:\s*[^;]+;/i', 'font-family: Helvetica, Arial, sans-serif;', $css);
        }

        return $css;
    }

    private function getInvoiceLogoUri()
    {
        $logoPath = FCPATH . 'assets/general/logo/favicon/web-app-manifest-192x192.png';
        if (!file_exists($logoPath)) {
            return '';
        }

        $mimeType = function_exists('mime_content_type') ? mime_content_type($logoPath) : 'image/png';
        $encoded = base64_encode(file_get_contents($logoPath));

        return 'data:' . $mimeType . ';base64,' . $encoded;
    }

    private function getInvoicePaidStampUri()
    {
        $stampPath = FCPATH . 'assets/general/paid-stamp.png';
        if (!file_exists($stampPath)) {
            return '';
        }

        $mimeType = function_exists('mime_content_type') ? mime_content_type($stampPath) : 'image/png';
        $encoded = base64_encode(file_get_contents($stampPath));

        return 'data:' . $mimeType . ';base64,' . $encoded;
    }

    private function buildRouteLabel($booking)
    {
        $origin = trim((string) ($booking->traveller_current_state ?: $booking->traveller_departure_state));
        $destination = trim((string) ($booking->traveller_destination ?: $booking->traveller_arrival_state ?: $booking->receiver_locality));

        if ($origin !== '' && $destination !== '') {
            return $origin . ' to ' . $destination;
        }

        return $destination !== '' ? $destination : 'Route not specified';
    }
}
