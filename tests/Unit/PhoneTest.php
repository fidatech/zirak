<?php

namespace Tests\Unit;

use App\Support\Phone;
use PHPUnit\Framework\TestCase;

class PhoneTest extends TestCase
{
    public function test_normalizes_afghan_and_international_numbers(): void
    {
        $this->assertSame('+93791234567', Phone::normalize('0791234567'));
        $this->assertSame('+93791234567', Phone::normalize('۰۷۹ ۱۲۳ ۴۵۶۷'));
        $this->assertSame('+93791234567', Phone::normalize('0093791234567'));
        $this->assertSame('+93791234567', Phone::normalize('93791234567'));
        $this->assertSame('+12025550123', Phone::normalize('+1 (202) 555-0123'));
        $this->assertNull(Phone::normalize('12345'));
        $this->assertNull(Phone::normalize('abc'));
    }
}
