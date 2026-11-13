<?php
$tiles = array();
foreach (array('payments' => array('Total Revenue', 'las la-money-bill'), 'commission' => array('Gross Profit', 'las la-chart-line'), 'payouts' => array('Traveller Payouts', 'las la-money-check')) as $key => $card) {
    $todayKey = $key . '_today';
    $tiles[] = array('icon' => $card[1], 'value' => $sign . number_format((float) $summary->$key, 2), 'label' => $card[0], 'col' => 3, 'detail' => 'Bookings created today: ' . html_entity_decode($sign, ENT_QUOTES, 'UTF-8') . number_format((float) $summary->$todayKey, 2));
}
$currency = $currency ?? 'GBP';
$payout = $payout_summary['currencies'][$currency] ?? null;
$payoutReady = ($payout_summary['opening_balance_status'] ?? '') === 'applied'
    && isset($payout['outstanding']) && is_numeric($payout['outstanding']);
$tiles[] = array(
    'icon' => 'las la-wallet',
    'value' => $payoutReady ? $sign . number_format((float) $payout['outstanding'], 2) : 'N/A',
    'label' => 'Outstanding Traveller Payouts',
    'col' => 3,
    'detail' => $payoutReady ? 'Unpaid bookings: ' . (int) ($payout['unpaid_booking_count'] ?? 0) : 'Payout opening balance needs setup.',
);
?>
<div class="admin-finance-summary"><?php $this->load->view('admin/partials/stat_tiles', array('tiles' => $tiles)); ?></div>
