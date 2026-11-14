<?php
namespace Astound\Affirm\Test\Unit\Model\Express;

use Astound\Affirm\Model\Express\ExpressException;
use Astound\Affirm\Model\Express\SignatureValidator;
use PHPUnit\Framework\TestCase;

class SignatureValidatorTest extends TestCase
{
    private const KEY = 'private-key';
    private const BODY = '{"order_id":"abc","currency":"USD"}';
    private const NOW = 1700000000;

    private function header(string $body = self::BODY, string $key = self::KEY, int $t = self::NOW): string
    {
        return 't=' . $t . ',v1=' . hash_hmac('sha512', $t . '.' . $body, $key);
    }

    private function assertRejected(string $header, string $body = self::BODY, string $key = self::KEY): void
    {
        try {
            (new SignatureValidator())->validate($header, $body, $key, self::NOW);
            $this->fail('Expected rejection');
        } catch (ExpressException $e) {
            $this->assertSame(401, $e->getHttpStatus());
        }
    }

    public function testValidSignaturePasses()
    {
        (new SignatureValidator())->validate($this->header(), self::BODY, self::KEY, self::NOW);
        $this->addToAssertionCount(1);
    }

    public function testTamperedBodyRejected()
    {
        $this->assertRejected($this->header(), self::BODY . ' ');
    }

    public function testWrongKeyRejected()
    {
        $this->assertRejected($this->header(self::BODY, 'other-key'));
    }

    public function testMissingHeaderRejected()
    {
        $this->assertRejected('');
    }

    public function testMalformedHeaderRejected()
    {
        $this->assertRejected('garbage');
    }

    public function testStaleTimestampRejected()
    {
        $this->assertRejected($this->header(self::BODY, self::KEY, self::NOW - 301));
    }

    public function testEmptyKeyRejected()
    {
        $this->assertRejected($this->header(), self::BODY, '');
    }
}
