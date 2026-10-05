<?php
defined('BASEPATH') or exit('Direct access to script not allowed');

class Auth_challenge_model extends CI_Model
{
    private const MAX_ATTEMPTS = 5;

    public function createChallenge($userId, $purpose, $channel, $destination, $code = null, $ttlSeconds = 600)
    {
        $token = bin2hex(random_bytes(32));
        $sessionHash = $this->currentSessionHash();

		$this->db->trans_start();
		$this->db->where('expires_at <', date('Y-m-d H:i:s', time() - 86400));
		$this->db->delete('auth_login_challenges');

        $this->db->where('user_id', (int) $userId);
        $this->db->where('purpose', $purpose);
        $this->db->where('session_hash', $sessionHash);
        $this->db->where('consumed_at IS NULL', null, false);
        $this->db->update('auth_login_challenges', array('consumed_at' => date('Y-m-d H:i:s')));

        $data = array(
            'token_hash' => hash('sha256', $token),
            'session_hash' => $sessionHash,
            'user_id' => (int) $userId,
            'purpose' => $purpose,
            'delivery_channel' => $channel,
            'destination_hash' => hash('sha256', strtolower(trim((string) $destination))),
            'code_hash' => $code === null ? null : password_hash((string) $code, PASSWORD_DEFAULT),
            'attempts' => 0,
            'expires_at' => date('Y-m-d H:i:s', time() + max(120, (int) $ttlSeconds)),
        );

        $this->db->insert('auth_login_challenges', $data);
		$this->db->trans_complete();

        if (!$this->db->trans_status()) {
            return null;
        }

        return $token;
    }

    public function getActiveChallenge($token, $purpose)
    {
        $this->db->where('token_hash', hash('sha256', (string) $token));
        $this->db->where('purpose', $purpose);
        $this->db->where('consumed_at IS NULL', null, false);
        $this->db->where('expires_at >', date('Y-m-d H:i:s'));
        $challenge = $this->db->get('auth_login_challenges')->row();

        if (!$challenge || !hash_equals((string) $challenge->session_hash, $this->currentSessionHash())) {
            return null;
        }

        return $challenge;
    }

    public function codeMatches($challenge, $code)
    {
        return $challenge && !empty($challenge->code_hash)
            && password_verify((string) $code, (string) $challenge->code_hash);
    }

	public function destinationMatches($challenge, $destination)
	{
		if (!$challenge || empty($challenge->destination_hash)) {
			return false;
		}

		$destinationHash = hash('sha256', strtolower(trim((string) $destination)));
		return hash_equals((string) $challenge->destination_hash, $destinationHash);
	}

    public function recordFailure($challengeId)
    {
        $this->db->set('attempts', 'attempts + 1', false);
        $this->db->where('id', (int) $challengeId);
        $this->db->update('auth_login_challenges');

        $challenge = $this->db->where('id', (int) $challengeId)->get('auth_login_challenges')->row();
        if ($challenge && (int) $challenge->attempts >= self::MAX_ATTEMPTS) {
            $this->consume($challengeId);
        }
    }

    public function consume($challengeId)
    {
        $this->db->where('id', (int) $challengeId);
        return $this->db->update('auth_login_challenges', array(
            'consumed_at' => date('Y-m-d H:i:s'),
        ));
    }

    /** Recheck account/challenge after external verification, under the same lock as admin resets. */
    public function consumeLoginChallenge($challengeId, $userId)
    {
        $this->db->trans_begin();
        $user = $this->db->query('SELECT * FROM users WHERE id = ? FOR UPDATE', array((int) $userId))->row();
        $challenge = $this->db->query('SELECT * FROM auth_login_challenges WHERE id = ? FOR UPDATE', array((int) $challengeId))->row();
        $phoneChannel = $challenge && in_array($challenge->delivery_channel, array('whatsapp', 'sms'), true);
        $destination = $user ? ($phoneChannel ? ($user->verified_phone_e164 ?? '') : $user->email) : '';
        if (!$user || !$challenge || (int) $challenge->user_id !== (int) $userId
            || $challenge->purpose !== 'login' || $challenge->consumed_at !== null
            || !in_array($challenge->delivery_channel, array('email', 'whatsapp', 'sms'), true)
            || strtotime($challenge->expires_at) <= time() || (int) $user->account_status === 0
            || !empty($user->deleted_at) || !$this->destinationMatches($challenge, $destination)
            || ($phoneChannel && (empty($user->phone_signin_enabled) || empty($user->phone_verified_at)))) {
            $this->db->trans_rollback();
            return false;
        }
        if (!$this->consume($challengeId) || !$this->db->trans_status()) {
            $this->db->trans_rollback();
            return false;
        }
        return $this->db->trans_commit() ? $user : false;
    }

    public function getPhoneOtpChannel()
    {
        $setting = $this->db->where('id', 1)->get('auth_settings')->row();
        return $setting && strtolower((string) $setting->phone_otp_channel) === 'sms' ? 'sms' : 'whatsapp';
    }

    public function updatePhoneOtpChannel($channel, $adminId)
    {
        $channel = strtolower((string) $channel) === 'sms' ? 'sms' : 'whatsapp';
        return $this->db->replace('auth_settings', array(
            'id' => 1,
            'phone_otp_channel' => $channel,
            'updated_by' => (int) $adminId,
        ));
    }

    private function currentSessionHash()
    {
        return hash('sha256', (string) session_id());
    }
}
