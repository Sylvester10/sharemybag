<?php
$tiles = array();
foreach (array('payments' => array('Total Revenue', 'las la-money-bill'), 'commission' => array('Gross Profit', 'las la-chart-line'), 'payouts' => array('Traveller Payouts', 'las la-money-check')) as $key => $card) {
    $todayKey = $key . '_today';
    $tiles[] = array('icon' => $card[1], 'value' => $sign . number_format((float) $summary->$key, 2), 'label' => $card[0], 'col' => 4, 'detail' => 'Bookings created today: ' . html_entity_decode($sign, ENT_QUOTES, 'UTF-8') . number_format((float) $summary->$todayKey, 2));
}
?>
<div class="admin-finance-summary"><?php $this->load->view('admin/partials/stat_tiles', array('tiles' => $tiles)); ?></div>
