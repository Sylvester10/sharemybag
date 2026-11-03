<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Booking_gateway_rejected extends RuntimeException {}

/** Network boundary. A transport error always means unknown, never a failed payment. */
class Booking_payment_gateway
{
    protected function stripe()
    {
        $key = get_stripe_secret_key();
        if (!$key) { throw new RuntimeException('Stripe is not configured.'); }
        return new \Stripe\StripeClient($key);
    }

    protected function paystack($method, $path, array $body = [])
    {
        $key = get_paystack_secret_key();
        if (!$key) { throw new RuntimeException('Paystack is not configured.'); }
        $curl = curl_init('https://api.paystack.co/' . $path);
        curl_setopt_array($curl,[CURLOPT_RETURNTRANSFER=>true,CURLOPT_CONNECTTIMEOUT=>8,CURLOPT_TIMEOUT=>25,
            CURLOPT_HTTPHEADER=>['Authorization: Bearer '.$key,'Content-Type: application/json','Cache-Control: no-cache']]);
        if ($method === 'POST') {
            curl_setopt($curl,CURLOPT_POST,true);
            curl_setopt($curl,CURLOPT_POSTFIELDS,json_encode($body));
        }
        $response = curl_exec($curl); $http = curl_getinfo($curl,CURLINFO_HTTP_CODE); $error = curl_errno($curl);
        curl_close($curl);
        if (!$error && $method === 'POST' && $http >= 400 && $http < 500 && !in_array($http,[408,409,429],true)) {
            throw new Booking_gateway_rejected('Gateway rejected initialization.');
        }
        if ($error || $http < 200 || $http >= 300) { throw new RuntimeException('Paystack request unavailable.'); }
        $decoded = json_decode($response,true);
        if (!is_array($decoded) || empty($decoded['status']) || !isset($decoded['data'])) {
            throw new RuntimeException('Paystack could not confirm this request.');
        }
        return $decoded['data'];
    }

    public function initialize($attempt, $booking)
    {
        if (($attempt->provider === 'stripe' && !get_stripe_secret_key())
            || ($attempt->provider === 'paystack' && !get_paystack_secret_key())) {
            return ['status'=>false,'outcome'=>'not_created'];
        }
        $reference = $attempt->reference;
        $metadata = ['reference'=>$reference,'booking_id'=>(string)$attempt->booking_id,'user_id'=>(string)$attempt->user_id];
        $success = base_url('user_bookings/payment_return/' . rawurlencode($reference));
        $cancel = base_url('user_bookings/payment_cancel/' . rawurlencode($reference));
        try {
            if ($attempt->provider === 'stripe') {
                $session = $this->stripe()->checkout->sessions->create([
                    'mode'=>'payment','payment_method_types'=>['card'],'customer_email'=>$booking->user_email,
                    'client_reference_id'=>$reference,'metadata'=>$metadata,
                    'line_items'=>[['price_data'=>['currency'=>strtolower($attempt->currency),'unit_amount'=>(int)$attempt->amount_minor,
                        'product_data'=>['name'=>'Bag space booking '.$booking->tracking_id]],'quantity'=>1]],
                    'success_url'=>$success,'cancel_url'=>$cancel,
                    // Stripe's automatic expiry has a 30-minute minimum; our worker closes the session at the 15-minute lease.
                    'expires_at'=>time()+1800,
                    'automatic_tax'=>['enabled'=>false],
                ],['idempotency_key'=>'smb-booking-'.$reference]);
                return ['status'=>true,'session_id'=>$session->id,'url'=>$session->url];
            }
            if ($attempt->provider !== 'paystack') { throw new RuntimeException('Unsupported gateway.'); }
            $metadata['cancel_action'] = $cancel;
            $data = $this->paystack('POST','transaction/initialize',[
                'email'=>$booking->user_email,'amount'=>(int)$attempt->amount_minor,'currency'=>$attempt->currency,
                'reference'=>$reference,'callback_url'=>$success,'metadata'=>$metadata,
            ]);
            if (($data['reference'] ?? '') !== $reference || empty($data['authorization_url'])) { throw new RuntimeException('Invalid gateway initialization.'); }
            return ['status'=>true,'session_id'=>null,'url'=>$data['authorization_url']];
        } catch (Booking_gateway_rejected $error) {
            return ['status'=>false,'outcome'=>'not_created'];
        } catch (\Stripe\Exception\AuthenticationException $error) {
            return ['status'=>false,'outcome'=>'not_created'];
        } catch (\Stripe\Exception\InvalidRequestException $error) {
            return ['status'=>false,'outcome'=>'not_created'];
        } catch (Throwable $error) {
            log_message('error','Booking gateway initialization unavailable for reference '.$reference);
            return ['status'=>false,'outcome'=>'unknown'];
        }
    }

    public function verify($attempt, $close = false)
    {
        try {
            if ($attempt->provider === 'stripe') {
                if (empty($attempt->provider_session_id)) { return ['outcome'=>'unknown']; }
                $client = $this->stripe();
                $session = $client->checkout->sessions->retrieve($attempt->provider_session_id,[]);
                if ($close && $session->status === 'open' && $session->payment_status === 'unpaid') {
                    // Expiry can race successful payment; retrieve again if the expire request loses that race.
                    try { $session = $client->checkout->sessions->expire($session->id,[]); }
                    catch (Throwable $error) { $session = $client->checkout->sessions->retrieve($session->id,[]); }
                }
                $data = $session->toArray();
                $result = $this->stripe_result($data);
            } elseif ($attempt->provider === 'paystack') {
                $data = $this->paystack('GET','transaction/verify/'.rawurlencode($attempt->reference));
                $result = $this->paystack_result($data);
            } else { return ['outcome'=>'unknown']; }
            if (!$this->matches($attempt,$result)) {
                log_message('error','Booking payment binding mismatch for reference '.$attempt->reference);
                return ['outcome'=>'unknown','binding_mismatch'=>true];
            }
            return $result;
        } catch (Throwable $error) {
            log_message('error','Booking payment verification unavailable for reference '.$attempt->reference);
            return ['outcome'=>'unknown'];
        }
    }

    public function stripe_result(array $data)
    {
        $metadata = $data['metadata'] ?? [];
        return ['outcome'=>($data['payment_status'] ?? '') === 'paid' ? 'paid'
                : (($data['status'] ?? '') === 'expired' ? 'unpaid' : 'pending'),
            'provider'=>'stripe','gateway_status'=>$data['status'] ?? '', 'reference'=>$metadata['reference'] ?? '',
            'provider_session_id'=>$data['id'] ?? '', 'booking_id'=>$metadata['booking_id'] ?? 0,
            'user_id'=>$metadata['user_id'] ?? 0,'amount_minor'=>$data['amount_total'] ?? -1,
            'currency'=>strtoupper($data['currency'] ?? '')];
    }

    public function paystack_result(array $data)
    {
        $metadata = $data['metadata'] ?? [];
        if (is_string($metadata)) { $metadata = json_decode($metadata,true); }
        $metadata = is_array($metadata) ? $metadata : [];
        $status = $data['status'] ?? '';
        return ['outcome'=>$status === 'success' ? 'paid' : (in_array($status,['abandoned','failed'],true) ? 'unpaid' : 'pending'),
            'provider'=>'paystack','reference'=>$data['reference'] ?? '', 'provider_session_id'=>null,
            'booking_id'=>$metadata['booking_id'] ?? 0,'user_id'=>$metadata['user_id'] ?? 0,
            'amount_minor'=>$data['amount'] ?? -1,'currency'=>strtoupper($data['currency'] ?? '')];
    }

    public function matches($attempt, array $result)
    {
        return ($result['reference'] ?? '') === $attempt->reference
            && ($result['provider'] ?? '') === $attempt->provider
            && (int)($result['booking_id'] ?? 0) === (int)$attempt->booking_id
            && (int)($result['user_id'] ?? 0) === (int)$attempt->user_id
            && is_numeric($result['amount_minor'] ?? null) && (int)$result['amount_minor'] === (int)$attempt->amount_minor
            && (float)$result['amount_minor'] === (float)$attempt->amount_minor
            && ($result['currency'] ?? '') === strtoupper($attempt->currency)
            && ($attempt->provider !== 'stripe' || (!empty($attempt->provider_session_id)
                && ($result['provider_session_id'] ?? '') === $attempt->provider_session_id));
    }
}
