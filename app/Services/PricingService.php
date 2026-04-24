<?php

namespace App\Services;

/**
 * Central pricing math for line items (orders, quotations, purchase orders).
 * Single source of truth for tax + total calculation.
 */
class PricingService
{
    /**
     * Compute tax and line total for a single line item.
     *
     * Returns array with 4 keys:
     *  - subtotal  : qty * unit_price (before tax/discount)
     *  - tax       : subtotal * tax_rate / 100
     *  - discount  : discount amount (flat or from percentage, via $discountType)
     *  - total     : subtotal - discount + tax
     */
    public static function lineItem(float $qty, float $unitPrice, float $taxRate = 0.0, float $discount = 0.0, string $discountType = 'fixed'): array
    {
        $subtotal = $qty * $unitPrice;

        $discountAmount = $discount;
        if ($discountType === 'percent') {
            $discountAmount = $subtotal * $discount / 100;
        }
        $discountAmount = min($subtotal, max(0.0, $discountAmount));

        $taxable = $subtotal - $discountAmount;
        $taxAmount = $taxable * $taxRate / 100;
        $total = $taxable + $taxAmount;

        return [
            'subtotal' => $subtotal,
            'tax' => $taxAmount,
            'discount' => $discountAmount,
            'total' => $total,
        ];
    }

    /**
     * Sum up multiple line items into document-level totals.
     * Each $lines item should have keys: qty, unit_price, tax_rate, discount, discount_type
     */
    public static function documentTotal(array $lines, float $docDiscount = 0.0, string $docDiscountType = 'fixed'): array
    {
        $subtotal = 0.0;
        $tax = 0.0;
        $lineDiscounts = 0.0;
        foreach ($lines as $l) {
            $calc = self::lineItem(
                (float)($l['qty'] ?? 1),
                (float)($l['unit_price'] ?? 0),
                (float)($l['tax_rate'] ?? 0),
                (float)($l['discount'] ?? 0),
                (string)($l['discount_type'] ?? 'fixed')
            );
            $subtotal += $calc['subtotal'];
            $tax += $calc['tax'];
            $lineDiscounts += $calc['discount'];
        }

        $preDocSubtotal = $subtotal - $lineDiscounts;
        $docDiscountAmount = $docDiscount;
        if ($docDiscountType === 'percent') {
            $docDiscountAmount = $preDocSubtotal * $docDiscount / 100;
        }
        $docDiscountAmount = min($preDocSubtotal, max(0.0, $docDiscountAmount));

        $total = $preDocSubtotal - $docDiscountAmount + $tax;

        return [
            'subtotal' => $subtotal,
            'line_discount_total' => $lineDiscounts,
            'doc_discount' => $docDiscountAmount,
            'tax' => $tax,
            'total' => $total,
        ];
    }
}
