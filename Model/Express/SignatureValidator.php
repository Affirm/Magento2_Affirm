<?php
namespace Astound\Affirm\Model\Express;

/**
 * Validates the X-Affirm-Signature header of Express Checkout callbacks.
 *
 * ASSUMPTION (Affirm's docs do not specify the format; confirm against the Affirm signature
 * docs / sandbox): header is "t=<unix timestamp>,v1=<hex HMAC-SHA512>" where the HMAC is computed
 * over "<timestamp>.<raw request body>" with the merchant's Private API Key. All of that
 * format knowledge lives in this class.
 */
class SignatureValidator
{
    const HEADER = 'X-Affirm-Signature';

    /** Maximum allowed clock skew / age of a signed request, in seconds (replay protection). */
    const TOLERANCE_SECONDS = 300;

    /**
     * @param string $header Raw header value ('' if missing)
     * @param string $rawBody Exact request body bytes as received
     * @param string $privateKey Merchant Private API Key
     * @param int|null $now Current unix time (injectable for tests)
     * @return void
     * @throws ExpressException HTTP 401 when invalid
     */
    public function validate($header, $rawBody, $privateKey, $now = null)
    {
        $now = $now ?? time();
        if ($header === '' || $header === null || (string)$privateKey === '') {
            throw $this->unauthorized();
        }

        $parts = [];
        foreach (explode(',', $header) as $piece) {
            $kv = explode('=', trim($piece), 2);
            if (count($kv) === 2) {
                $parts[$kv[0]] = $kv[1];
            }
        }
        $timestamp = $parts['t'] ?? '';
        $signature = $parts['v1'] ?? '';
        if (!ctype_digit($timestamp) || $signature === '') {
            throw $this->unauthorized();
        }

        $expected = hash_hmac('sha512', $timestamp . '.' . $rawBody, (string)$privateKey);
        if (!hash_equals($expected, strtolower($signature))) {
            throw $this->unauthorized();
        }
        if (abs($now - (int)$timestamp) > self::TOLERANCE_SECONDS) {
            throw $this->unauthorized();
        }
    }

    /**
     * @return ExpressException
     */
    private function unauthorized()
    {
        return new ExpressException('UNAUTHORIZED', 'Invalid signature', [], 401);
    }
}
