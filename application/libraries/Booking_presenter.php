<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Booking_presenter
{
    public function decode_items($items_json)
    {
        $items = json_decode($items_json);
        return (is_array($items) || is_object($items)) ? $items : [];
    }

    public function collect_item_metrics($items_json)
    {
        $items = $this->decode_items($items_json);
        $metrics = [
            'items' => $items,
            'total_item_size' => 0.0,
            'special_fee' => 0.0,
            'is_special' => false,
            'is_premium' => false,
        ];

        foreach ($items as $item) {
            $category = isset($item->category) ? $item->category : '';
            $size = isset($item->size) ? (float) $item->size : 0.0;

            if (booking_category_price_type($category) === 'premium_small') {
                $metrics['is_premium'] = true;
            } elseif (booking_category_price_type($category) === 'premium_laptop') {
                $metrics['is_premium'] = true;
            } elseif (booking_category_price_type($category) === 'special') {
                $metrics['special_fee'] += 10.00;
                $metrics['is_special'] = true;
            }

            $metrics['total_item_size'] += $size;
        }

        return $metrics;
    }

    public function render_item_table($items_json, $currency_code, $parcel_actions_html = '')
    {
        $metrics = $this->collect_item_metrics($items_json);
        $items = $metrics['items'];
        $currency = currency_symbol($currency_code);

        $html = '<table class="table text-nowrap fs-2">';
        $html .= '<thead><tr><th>Item</th><th>Category</th><th>Price</th></tr></thead>';
        $html .= '<tbody>';

        if (!empty($items)) {
            foreach ($items as $item) {
                $html .= '<tr>';
                $html .= '<td>' . ($item->item_name ?? '') . '</td>';
                $html .= '<td>' . ($item->category ?? '') . '</td>';
                $html .= '<td>' . $currency . number_format((float) ($item->price ?? 0), 2) . '</td>';
                $html .= '</tr>';
            }
        } else {
            $html .= '<tr><td colspan="3">No items found</td></tr>';
        }

        $html .= '</tbody>';
        $html .= '</table>';

        if ($parcel_actions_html !== '') {
            $html .= $parcel_actions_html;
        }

        return [$html, $metrics];
    }

    public function format_payment_status_badge($payment_status)
    {
        $status = payment_status_normalize($payment_status);
        if ($status === 'completed') {
            return '<span class="badge badge-success"><b>Paid</b></span>';
        }
        if ($status === 'canceled') {
            return '<span class="badge badge-danger"><b>Canceled</b></span>';
        }
        return '<span class="badge badge-warning"><b>Pending</b></span>';
    }

    public function format_payment_status_text($payment_status)
    {
        return payment_status_normalize($payment_status) === 'completed'
            ? '<span class="text-success"><b>Paid</b></span>'
            : '<span class="text-danger"><b>Canceled</b></span>';
    }

    public function format_payment_method($payment_method, $default_label = 'Offline')
    {
        switch ($payment_method) {
            case 'stripe':
                return '<img src="' . base_url('assets/general/stripe.svg') . '" alt="Stripe" width="40" height="20">';
            case 'paystack':
                return '<img src="' . base_url('assets/general/paystack.svg') . '" alt="Paystack" width="80" height="20">';
            default:
                return $default_label;
        }
    }

    public function format_money($currency_code, $amount)
    {
        return currency_symbol($currency_code) . number_format((float) $amount, 2);
    }

    public function format_money_with_sign($sign, $amount)
    {
        return $sign . number_format((float) $amount, 2);
    }

    public function format_item_size($metrics, $fallback_selected_space)
    {
        $size = $metrics['total_item_size'] > 0 ? $metrics['total_item_size'] : (float) $fallback_selected_space;
        return $size . ' KG';
    }

    public function calculate_booking_commission($booking, $traveller, $metrics)
    {
        return round((float) $booking->traveller_commission, 2);
    }

    public function format_commission($currency_code, $payment_status, $commission)
    {
        return payment_status_normalize($payment_status) === 'completed'
            ? $this->format_money($currency_code, $commission)
            : 'N/A';
    }

    public function format_total_amount_summary($currency_code, $total_amount, $payment_method_html)
    {
        return 'Total amount: ' . $this->format_money($currency_code, $total_amount) . ' <br /> Payment method: ' . $payment_method_html;
    }

    public function build_price_estimate($origin, $destination, $category, $weight)
    {
        $valid_destinations = ['Nigeria', 'United Kingdom', 'Canada'];
        $valid_categories = ['Normal', 'Duty Free', 'Fish/Medicine', 'Fish/Meat', 'Medication', 'Documents/Electronics', 'Gold', 'Documents/Small Electronics', 'Laptop'];

        if (!in_array($destination, $valid_destinations) || !in_array($origin, $valid_destinations)) {
            return ['status' => false, 'msg' => 'Please select a valid origin and destination.'];
        }

        if (!in_array($category, $valid_categories)) {
            return ['status' => false, 'msg' => 'Please select a valid category.'];
        }

        if ($weight <= 0 || $weight > 50) {
            return ['status' => false, 'msg' => 'Please enter a weight between 1 and 50.'];
        }

        $is_ng_uk_route = (
            ($origin === 'Nigeria' && $destination === 'United Kingdom') ||
            ($origin === 'United Kingdom' && $destination === 'Nigeria')
        );
        $is_ng_ca_route = (
            ($origin === 'Nigeria' && $destination === 'Canada') ||
            ($origin === 'Canada' && $destination === 'Nigeria')
        );

        if (!$is_ng_uk_route && !$is_ng_ca_route) {
            return ['status' => false, 'msg' => 'We currently only support Nigeria ↔ UK and Nigeria ↔ Canada routes.'];
        }

        if ($category === 'Duty Free' && $destination !== 'Nigeria') {
            return ['status' => false, 'msg' => 'Duty Free is only available on routes to Nigeria.'];
        }

        $currency = $is_ng_ca_route ? 'CAD' : 'GBP';
        $symbol = currency_symbol_text($currency);

        $route_pricing = booking_route_pricing($origin, $destination);
        $price_per_unit = booking_category_rate($route_pricing, $category);
        $category_label = booking_category_label($category);
        $weight_unit = booking_category_unit($category);
        $item_price = round($price_per_unit * $weight, 2);
        $service_charge = $route_pricing['service_charge'];
        $special_fee = booking_category_price_type($category) === 'special' ? 10.00 : 0.00;
        $sub_total = round($item_price + $service_charge, 2);
        $total = round($sub_total + $special_fee, 2);

        return [
            'status' => true,
            'currency' => $currency,
            'symbol' => $symbol,
            'route' => $origin . ' → ' . $destination,
            'category' => $category_label,
            'weight' => $weight . ' ' . $weight_unit,
            'price_per_unit' => $symbol . number_format($price_per_unit, 2) . ' per ' . $weight_unit,
            'item_price' => $symbol . number_format($item_price, 2),
            'service_charge' => $symbol . number_format($service_charge, 2),
            'special_fee' => $special_fee > 0 ? $symbol . number_format($special_fee, 2) : null,
            'sub_total' => $symbol . number_format($sub_total, 2),
            'total' => $symbol . number_format($total, 2),
            'disclaimer' => 'This is an estimate only. Final price depends on traveller availability and insurance options selected at checkout.',
        ];
    }
}
