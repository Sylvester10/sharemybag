<?php
defined('BASEPATH') or exit('No direct script access allowed');

/** Persist only editable fields needed by the existing booking wizard. */
function booking_checkout_form(array $input)
{
    $fields = ['items','category','item','size','parcel_status','receiver_status','insurance','payment_method',
        'agent_name','agent_country_code','agent_phone','agent_email','agent_address','agent_locality','agent_postcode',
        'receiver_name','receiver_country_code','receiver_phone','receiver_email','receiver_address','receiver_locality','receiver_postcode',
        '_booking_step','booking_request_id'];
    $form = [];
    foreach ($fields as $field) {
        if (isset($input[$field]) && !is_scalar($input[$field])) { throw new InvalidArgumentException('Check the booking form fields.'); }
        if (isset($input[$field]) && is_scalar($input[$field])) {
            $value = (string) $input[$field];
            if (!mb_check_encoding($value,'UTF-8') || strlen($value) > ($field === 'items' ? 32768 : 1000)) {
                throw new InvalidArgumentException('Some booking details are too long. Please shorten them.');
            }
            $form[$field] = $value;
        }
    }
    return $form;
}

/** Rebuild every charge from trusted route pricing; posted prices and totals are never authority. */
function booking_checkout_quote($traveller, array $form, $exchange_rate)
{
    $provider = $form['payment_method'] ?? '';
    if (!in_array($provider, ['stripe','paystack'], true)) { throw new InvalidArgumentException('Select a payment method.'); }
    $items = json_decode($form['items'] ?? '', true);
    if (!is_array($items) || !$items || count($items) > 100) { throw new InvalidArgumentException('Add the parcel items to continue.'); }
    $pricing = booking_route_pricing($traveller->location, $traveller->destination);
    $allowed = array_merge(['Normal','Fish/Meat','Medication','Documents/Small Electronics','Laptop'],
        $traveller->destination === 'Nigeria' ? ['Duty Free'] : []);
    $normalized = []; $space = 0; $item_total = 0; $special = false;
    foreach ($items as $item) {
        if (!is_array($item)) { throw new InvalidArgumentException('Check the parcel details.'); }
        if (!is_string($item['category'] ?? null) || !is_string($item['item_name'] ?? null)) {
            throw new InvalidArgumentException('Check the parcel category and item name.');
        }
        $category = trim((string) ($item['category'] ?? ''));
        // Older category labels are supported for existing bookings only through their current equivalent.
        $category = ['Fish/Medicine'=>'Fish/Meat','Documents/Electronics'=>'Documents/Small Electronics','Gold'=>'Documents/Small Electronics'][$category] ?? $category;
        $name = trim((string) ($item['item_name'] ?? ''));
        $size = $item['size'] ?? 0;
        if (!in_array($category,$allowed,true) || $name === '' || mb_strlen($name) > 200
            || !is_numeric($size) || !is_finite((float)$size) || (float)$size <= 0 || (float)$size > 1000
            || abs((float)$size - round((float)$size,3)) > 0.00001) {
            throw new InvalidArgumentException('Check each parcel category, item name and quantity.');
        }
        $size = (float)$size;
        $rate = booking_category_rate($pricing, $category);
        if (!is_finite($rate) || $rate <= 0) { throw new InvalidArgumentException('Pricing is unavailable for this route. Please try again later.'); }
        $price = round($size * $rate, 2);
        $normalized[] = ['category'=>$category,'item_name'=>$name,'size'=>$size,'unit'=>booking_category_unit($category),'unit_price'=>$rate,'price'=>$price];
        $space += $size; $item_total += $price;
        $special = $special || in_array($category, ['Fish/Meat','Medication'], true);
    }
    $insurance = $form['insurance'] ?? '';
    if (!in_array((string)$insurance, ['', '0', '0.00', '3.99', '13.99'], true)) { throw new InvalidArgumentException('Select a valid parcel protection option.'); }
    $insurance = (float)$insurance;
    $service = round((float)$pricing['service_charge'],2);
    $subtotal = round($item_total + $service,2);
    $base = round($subtotal + $insurance + ($special ? 10 : 0),2);
    $commission = booking_calculate_traveller_commission($pricing,$space,json_encode($normalized));
    $breakdown = booking_price_breakdown($base,$commission,$service,$insurance,$provider);
    $currency = currency_code_normalize($pricing['currency']);
    $rate = $provider === 'paystack' ? (float)$exchange_rate : 1;
    if (!is_finite($rate) || $rate <= 0) { throw new InvalidArgumentException('The exchange rate is unavailable. Please try again later.'); }
    $minor = (int)round($breakdown['total_amount'] * $rate * 100);
    if ($minor <= 0 || $minor > 100000000000) { throw new InvalidArgumentException('Check the payment total.'); }
    return array_merge($breakdown,[
        'items'=>$normalized,'selected_space'=>round($space,3),'selected_price'=>round($item_total,2),
        'sub_total'=>$subtotal,'special_charge'=>$special?10:0,'currency'=>$currency,
        'exchange_rate'=>$rate,'charge_minor'=>$minor,'charge_currency'=>$provider === 'paystack'?'NGN':$currency,
    ]);
}

function booking_checkout_quote_token($traveller_id, array $form, array $quote, $session_token)
{
    unset($form['_booking_step'],$form['category'],$form['item'],$form['size'],$form['booking_request_id']);
    ksort($form);
    return hash_hmac('sha256',json_encode([(int)$traveller_id,$form,$quote],JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES),(string)$session_token);
}
