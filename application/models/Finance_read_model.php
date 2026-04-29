<?php
defined('BASEPATH') or exit('Direct access to script not allowed');

class Finance_read_model extends \MY_Model
{
    const SUMMARY_CACHE_TTL = 300;
    const EXCHANGE_RATE_CACHE_TTL = 300;
    const SUMMARY_CACHE_VERSION = 'v3';

    public function __construct()
    {
        parent::__construct();
        $this->table = 'bookings';
        $this->primary_cols = array('id');
    }

    public function get_most_recent_cad_exchange_rate()
    {
        return $this->getMostRecentExchangeRateByCurrency('CAD');
    }

    public function get_most_recent_pound_exchange_rate()
    {
        return $this->getMostRecentExchangeRateByCurrency('GBP');
    }

    public function one_pound()
    {
        $rate = $this->get_most_recent_pound_exchange_rate();
        return $rate ? floatval($rate->rate) : 0;
    }

    public function get_all_total_amount()
    {
        return $this->rememberCache('finance.summary.' . self::SUMMARY_CACHE_VERSION . '.total_amount.all', self::SUMMARY_CACHE_TTL, function () {
            $this->db->select_sum('total_amount');
            $this->db->where('payment_status', 'completed');
            $this->applyCompletedPaymentMethodFilter();
            $this->applyNotDeleted();
            $row = $this->db->get($this->table)->row();
            return $row ? (float) $row->total_amount : 0;
        });
    }

    public function get_total_pounds_tax()
    {
        return $this->getCompletedBookingSummaryByCurrency('GBP')->vat;
    }

    public function get_total_cad_tax()
    {
        return $this->getCompletedBookingSummaryByCurrency('CAD')->vat;
    }

    public function get_total_pounds_amount()
    {
        return $this->getCompletedBookingSummaryByCurrency('GBP')->total_amount;
    }

    public function get_total_cad_amount()
    {
        return $this->getCompletedBookingSummaryByCurrency('CAD')->total_amount;
    }

    public function get_total_pounds_selected_price()
    {
        return $this->getCompletedBookingSummaryByCurrency('GBP')->selected_price;
    }

    public function get_total_cad_selected_price()
    {
        return $this->getCompletedBookingSummaryByCurrency('CAD')->selected_price;
    }

    public function get_total_pounds_commission()
    {
        return $this->getCompletedBookingSummaryByCurrency('GBP')->traveller_commission;
    }

    public function get_total_cad_commission()
    {
        return $this->getCompletedBookingSummaryByCurrency('CAD')->traveller_commission;
    }

    public function clearFinanceSummaryCaches()
    {
        foreach (array('GBP', 'CAD') as $currency) {
            $this->forgetCache($this->getCurrencySummaryCacheKey($currency));
        }

        $this->forgetCache('finance.summary.' . self::SUMMARY_CACHE_VERSION . '.total_amount.all');
    }

    public function clearExchangeRateCaches()
    {
        foreach (array('GBP', 'CAD') as $currency) {
            $this->forgetCache($this->getExchangeRateCacheKey($currency));
        }
    }

    private function getMostRecentExchangeRateByCurrency($currency)
    {
        return $this->rememberCache($this->getExchangeRateCacheKey($currency), self::EXCHANGE_RATE_CACHE_TTL, function () use ($currency) {
            $this->db->order_by('date_added', 'DESC');
            $this->db->where('currency', $currency);
            $this->db->limit(1);
            return $this->db->get('exchange_rates')->row();
        });
    }

    private function getCompletedBookingSummaryByCurrency($currency)
    {
        return $this->rememberCache($this->getCurrencySummaryCacheKey($currency), self::SUMMARY_CACHE_TTL, function () use ($currency) {
            $this->db->select('
                COALESCE(SUM(total_amount), 0) AS total_amount,
                COALESCE(SUM(selected_price), 0) AS selected_price,
                COALESCE(SUM(traveller_commission), 0) AS traveller_commission,
                COALESCE(SUM(vat), 0) AS vat
            ', false);
            $this->filterCompletedByCurrency($currency);
            $row = $this->db->get($this->table)->row();

            return (object) array(
                'total_amount' => $row ? (float) $row->total_amount : 0,
                'selected_price' => $row ? (float) $row->selected_price : 0,
                'traveller_commission' => $row ? (float) $row->traveller_commission : 0,
                'vat' => $row ? (float) $row->vat : 0,
            );
        });
    }

    private function filterCompletedByCurrency($currency)
    {
        $this->db->where('payment_status', 'completed');
        $allowed_values = currency_db_values($currency);
        $normalized_currency = currency_code_normalize($currency);
        $this->db->group_start();
        $this->db->where_in('currency', $allowed_values);
        if ($normalized_currency === 'GBP') {
            $this->db->or_where('currency IS NULL', null, false);
        }
        $this->db->group_end();
        $this->applyNotDeleted();
        $this->applyCompletedPaymentMethodFilter();
    }

    private function applyCompletedPaymentMethodFilter()
    {
        $this->db->where("(LOWER(COALESCE(payment_method, '')) IN ('paystack','stripe','offline','bank') OR payment_method IS NULL)", null, false);
    }

    private function getCurrencySummaryCacheKey($currency)
    {
        return 'finance.summary.' . self::SUMMARY_CACHE_VERSION . '.currency.' . strtoupper(currency_code_normalize($currency));
    }

    private function getExchangeRateCacheKey($currency)
    {
        return 'finance.exchange_rate.' . strtoupper($currency);
    }
}
