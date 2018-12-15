<?php
/**
 * Created by PhpStorm.
 * User: root
 * Date: 15.12.18
 * Time: 1:11
 */

namespace rikudou\EuQrPayment\Tests;

use PHPUnit\Framework\TestCase;
use rikudou\EuQrPayment\QrPayment;
use rikudou\EuQrPayment\Sepa\CharacterSet;
use rikudou\EuQrPayment\Sepa\Purpose;

class QrPaymentTest extends TestCase
{

    public function testSetSwift()
    {
        $shortSwifts = [
            "A",
            "AB",
            "ABC",
            "ABCD",
            "ABCDE",
            "ABCDEF",
            "ABCDEFG"
        ];

        $validLengthSwifts = [
            "ABCDEFGH",
            "ABCDEFGHI",
            "ABCDEFGHIJ",
            "ABCDEFGHIJK",
        ];

        $tooLongSwifts = [
            "ABCDEFGHIJKL",
            "ABCDEFGHIJKLM",
            "ABCDEFGHIJKLMN",
            "ABCDEFGHIJKLMNO"
        ];

        foreach ($shortSwifts as $shortSwift) {
            try {
                $this->getDefaultPayment()->setSwift($shortSwift);
                $this->fail("Expected " . \InvalidArgumentException::class . " for swift {$shortSwift}");
            } catch (\InvalidArgumentException $e) {
                // do nothing
            }
        }

        foreach ($validLengthSwifts as $validLengthSwift) {
            $this->getDefaultPayment()->setSwift($validLengthSwift);
        }

        foreach ($tooLongSwifts as $tooLongSwift) {
            try {
                $this->getDefaultPayment()->setSwift($tooLongSwift);
                $this->fail("Expected " . \InvalidArgumentException::class . " for swift {$tooLongSwift}");
            } catch (\InvalidArgumentException $e) {
                // do nothing
            }
        }

        $this->assertTrue(true); // dumb assertion for PHPUnit to stop complaining about no assertions
    }

    public function testSetBeneficiaryName()
    {
        $beneficiaries = [
            $this->getRandomString(10),
            $this->getRandomString(70),
            $this->getRandomString(71)
        ];

        $this->assertEquals($beneficiaries[0], $this->getDefaultPayment()->setBeneficiaryName($beneficiaries[0])->getBeneficiaryName());
        $this->assertEquals($beneficiaries[1], $this->getDefaultPayment()->setBeneficiaryName($beneficiaries[1])->getBeneficiaryName());

        $this->expectException(\InvalidArgumentException::class);
        $this->getDefaultPayment()->setBeneficiaryName($beneficiaries[2]);
    }

    public function testSetPurpose()
    {
        $purposes = [
            Purpose::TRUST_FUND, // valid purpose constant
            "ABCD", // can be 0 - 4 characters long
            "ABC",
            "AB",
            "A",
        ];

        foreach ($purposes as $purpose) {
            $this->assertEquals($purpose, $this->getDefaultPayment()->setPurpose($purpose)->getPurpose());
        }

        $this->expectException(\InvalidArgumentException::class);
        $this->getDefaultPayment()->setPurpose("ABCDE"); // 5 characters long
    }

    public function testGetQrString()
    {
        $payment = $this->getDefaultPayment();
        $payment
            ->setPurpose(Purpose::ACCOUNT_MANAGEMENT)
            ->setBeneficiaryName("My Company")
            ->setSwift("AIRACZPP")
            ->setAmount(10)
            ->setCurrency("EUR")
            ->setComment("Random comment")
            ->setRemittanceText("Invoice ID: 1")
            ->setCharacterSet(CharacterSet::UTF_8);

        $this->assertEquals("BCD\n002\n1\nSCT\nAIRACZPP\nMy Company\nCZ5530300000001325090010\nEUR10\nACCT\nInvoice ID: 1\nRandom comment", $payment->getQrString());

        $payment = $this->getDefaultPayment();
        $payment// no unnecessary parameters
        ->setBeneficiaryName("My Company");

        $this->assertEquals("BCD\n002\n1\nSCT\n\nMy Company\nCZ5530300000001325090010\n\n\n\n", $payment->getQrString());
    }

    public function testGetQrStringNoBeneficiary()
    {
        $this->expectException(\LogicException::class);
        $this->getDefaultPayment()->getQrString();
    }

    public function testGetQrStringInvalidCharacterSet()
    {
        do {
            $randomInt = rand(0, 100);
        } while (in_array($randomInt, (new \ReflectionClass(CharacterSet::class))->getConstants()));

        $this->expectException(\LogicException::class);
        $this->getDefaultPayment()->setBeneficiaryName($this->getRandomString(1))->setCharacterSet($randomInt)->getQrString();
    }

    public function testGetQrStringTooLong()
    {
        $this->expectException(\LogicException::class);
        $payment = $this->getDefaultPayment();
        $payment
            ->setSwift($this->getRandomString(11))
            ->setBeneficiaryName($this->getRandomString(70))
            ->setAmount(999999999.99)
            ->setPurpose($this->getRandomString(4))
            ->setRemittanceText($this->getRandomString(140))
            ->setComment($this->getRandomString(70));

        $payment->getQrString();
    }

    public function testSetInformation()
    {
        $strings = [
            $this->getRandomString(10),
            $this->getRandomString(70),
            $this->getRandomString(71)
        ];

        $this->assertEquals($strings[0], $this->getDefaultPayment()->setInformation($strings[0])->getInformation());
        $this->assertEquals($strings[1], $this->getDefaultPayment()->setInformation($strings[1])->getInformation());

        $this->expectException(\InvalidArgumentException::class);
        $this->getDefaultPayment()->setInformation($strings[2]);
    }

    public function testSetRemittanceText()
    {
        $strings = [
            $this->getRandomString(10),
            $this->getRandomString(140),
            $this->getRandomString(141)
        ];

        $this->assertEquals($strings[0], $this->getDefaultPayment()->setRemittanceText($strings[0])->getRemittanceText());
        $this->assertEquals($strings[1], $this->getDefaultPayment()->setRemittanceText($strings[1])->getRemittanceText());

        $this->expectException(\InvalidArgumentException::class);
        $this->getDefaultPayment()->setRemittanceText($strings[2]);
    }

    public function testSetAmountNegative()
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->getDefaultPayment()->setAmount(-1);
    }

    public function testSetAmountTooBig()
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->getDefaultPayment()->setAmount(1000000000);
    }

    public function testSetBic()
    {
        $shortBics = [
            "A",
            "AB",
            "ABC",
            "ABCD",
            "ABCDE",
            "ABCDEF",
            "ABCDEFG"
        ];

        $validLengthBics = [
            "ABCDEFGH",
            "ABCDEFGHI",
            "ABCDEFGHIJ",
            "ABCDEFGHIJK",
        ];

        $tooLongBics = [
            "ABCDEFGHIJKL",
            "ABCDEFGHIJKLM",
            "ABCDEFGHIJKLMN",
            "ABCDEFGHIJKLMNO"
        ];

        foreach ($shortBics as $shortBic) {
            try {
                $this->getDefaultPayment()->setSwift($shortBic);
                $this->fail("Expected " . \InvalidArgumentException::class . " for swift {$shortBic}");
            } catch (\InvalidArgumentException $e) {
                // do nothing
            }
        }

        foreach ($validLengthBics as $validLengthBic) {
            $this->getDefaultPayment()->setSwift($validLengthBic);
        }

        foreach ($tooLongBics as $tooLongBic) {
            try {
                $this->getDefaultPayment()->setSwift($tooLongBic);
                $this->fail("Expected " . \InvalidArgumentException::class . " for swift {$tooLongBic}");
            } catch (\InvalidArgumentException $e) {
                // do nothing
            }
        }

        $this->assertTrue(true); // dumb assertion for PHPUnit to stop complaining about no assertions
    }

    public function testSetCharacterSet()
    {
        $validSet = (new \ReflectionClass(CharacterSet::class))->getConstants();

        for ($i = 0; $i <= 20; $i++) {
            try {
                $this->getDefaultPayment()->setCharacterSet($i);
                if (!in_array($i, $validSet)) {
                    $this->fail("Expected exception " . \InvalidArgumentException::class . " for character set {$i}");
                }
            } catch (\InvalidArgumentException $exception) {
                if (in_array($i, $validSet)) {
                    $this->fail("Did not expect exception " . get_class($exception) . " for character set {$i}");
                }
            }
        }

        $this->assertTrue(true);
    }

    public function testSetComment()
    {
        $strings = [
            $this->getRandomString(10),
            $this->getRandomString(70),
            $this->getRandomString(71)
        ];

        $this->assertEquals($strings[0], $this->getDefaultPayment()->setComment($strings[0])->getComment());
        $this->assertEquals($strings[1], $this->getDefaultPayment()->setComment($strings[1])->getComment());

        $this->expectException(\InvalidArgumentException::class);
        $this->getDefaultPayment()->setComment($strings[2]);
    }

    public function testSetCurrency()
    {
        $this->assertEquals("EUR", $this->getDefaultPayment()->getCurrency());
        $this->assertEquals("CZK", $this->getDefaultPayment()->setCurrency("CZK")->getCurrency());

        $this->expectException(\InvalidArgumentException::class);
        $this->getDefaultPayment()->setCurrency("EURO");
    }

    private function getDefaultPayment(): QrPayment
    {
        return new QrPayment("CZ5530300000001325090010");
    }

    private function getRandomString(int $length): string
    {
        $result = "";
        $chars = range("A", "Z");
        for ($i = 0; $i < $length; $i++) {
            try {
                $result .= $chars[random_int(0, count($chars) - 1)];
            } catch (\Exception $e) {
                $result .= $chars[rand(0, count($chars) - 1)];
            }
        }

        return $result;
    }
}