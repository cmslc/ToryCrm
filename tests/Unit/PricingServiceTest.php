<?php

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;
use App\Services\PricingService;

class PricingServiceTest extends TestCase
{
    public function testLineItemNoTaxNoDiscount(): void
    {
        $r = PricingService::lineItem(2, 100);
        $this->assertSame(200.0, $r['subtotal']);
        $this->assertSame(0.0, $r['tax']);
        $this->assertSame(0.0, $r['discount']);
        $this->assertSame(200.0, $r['total']);
    }

    public function testLineItemWithTax(): void
    {
        $r = PricingService::lineItem(2, 100, 10.0);
        $this->assertSame(200.0, $r['subtotal']);
        $this->assertSame(20.0, $r['tax']);
        $this->assertSame(220.0, $r['total']);
    }

    public function testLineItemFixedDiscount(): void
    {
        $r = PricingService::lineItem(2, 100, 0.0, 50.0, 'fixed');
        $this->assertSame(200.0, $r['subtotal']);
        $this->assertSame(50.0, $r['discount']);
        $this->assertSame(150.0, $r['total']);
    }

    public function testLineItemPercentDiscount(): void
    {
        $r = PricingService::lineItem(2, 100, 0.0, 25.0, 'percent');
        $this->assertSame(200.0, $r['subtotal']);
        $this->assertSame(50.0, $r['discount']);
        $this->assertSame(150.0, $r['total']);
    }

    public function testLineItemDiscountAppliesBeforeTax(): void
    {
        // Subtotal 200, 10% discount = 20 → taxable 180 → 10% tax = 18 → total 198
        $r = PricingService::lineItem(2, 100, 10.0, 10.0, 'percent');
        $this->assertSame(200.0, $r['subtotal']);
        $this->assertSame(20.0, $r['discount']);
        $this->assertSame(18.0, $r['tax']);
        $this->assertSame(198.0, $r['total']);
    }

    public function testLineItemDiscountCannotExceedSubtotal(): void
    {
        // 200 subtotal, discount 500 flat → clamped to 200, total 0
        $r = PricingService::lineItem(2, 100, 0.0, 500.0, 'fixed');
        $this->assertSame(200.0, $r['discount']);
        $this->assertSame(0.0, $r['total']);
    }

    public function testLineItemNegativeDiscountClampedToZero(): void
    {
        $r = PricingService::lineItem(2, 100, 0.0, -50.0, 'fixed');
        $this->assertSame(0.0, $r['discount']);
        $this->assertSame(200.0, $r['total']);
    }

    public function testLineItemZeroQty(): void
    {
        $r = PricingService::lineItem(0, 100, 10.0);
        $this->assertSame(0.0, $r['subtotal']);
        $this->assertSame(0.0, $r['tax']);
        $this->assertSame(0.0, $r['total']);
    }

    public function testDocumentTotalAggregatesLines(): void
    {
        $lines = [
            ['qty' => 2, 'unit_price' => 100, 'tax_rate' => 10],          // 200 sub, 20 tax, 220 total
            ['qty' => 1, 'unit_price' => 50,  'tax_rate' => 0],           // 50 sub, 0 tax, 50 total
            ['qty' => 3, 'unit_price' => 30,  'tax_rate' => 5, 'discount' => 10], // 90 sub, (80*5%)=4 tax, 84 total; 10 line-discount
        ];
        $r = PricingService::documentTotal($lines);

        $this->assertSame(340.0, $r['subtotal']);         // 200+50+90
        $this->assertSame(10.0, $r['line_discount_total']); // only line 3
        $this->assertSame(24.0, $r['tax']);               // 20+0+4
        $this->assertSame(0.0, $r['doc_discount']);
        // 200 + 50 + (90-10) + 24 = 354
        $this->assertSame(354.0, $r['total']);
    }

    public function testDocumentTotalWithDocLevelPercentDiscount(): void
    {
        $lines = [
            ['qty' => 10, 'unit_price' => 100, 'tax_rate' => 0],  // 1000 sub
        ];
        $r = PricingService::documentTotal($lines, 10.0, 'percent');
        $this->assertSame(1000.0, $r['subtotal']);
        $this->assertSame(100.0, $r['doc_discount']);   // 10% of 1000
        $this->assertSame(900.0, $r['total']);
    }

    public function testDocumentTotalDocDiscountCannotExceedSubtotal(): void
    {
        $lines = [
            ['qty' => 1, 'unit_price' => 100, 'tax_rate' => 0],
        ];
        $r = PricingService::documentTotal($lines, 500.0, 'fixed');
        $this->assertSame(100.0, $r['doc_discount']);   // clamped to subtotal
        $this->assertSame(0.0, $r['total']);
    }

    public function testDocumentTotalEmptyLines(): void
    {
        $r = PricingService::documentTotal([]);
        $this->assertSame(0.0, $r['subtotal']);
        $this->assertSame(0.0, $r['total']);
    }
}
