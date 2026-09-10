<?php

namespace App\Services;

use App\Models\Customer;
use App\Models\Product;
use App\Models\ProductPrice;
use Carbon\CarbonInterface;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class ProductPriceService
{
    public function create(Product $product, array $data): ProductPrice
    {
        return DB::transaction(function () use ($product, $data) {
            $data['product_id'] = $product->id;
            $this->ensureNoOverlap($data);
            return ProductPrice::create($data);
        });
    }

    public function update(ProductPrice $price, array $data): ProductPrice
    {
        return DB::transaction(function () use ($price, $data) {
            $payload = array_merge($price->only([
                'product_id', 'customer_id', 'pricing_type', 'min_quantity', 'max_quantity',
                'unit_price', 'effective_from', 'effective_to', 'status',
            ]), $data);

            $this->ensureNoOverlap($payload, $price->id);
            $price->update($data);
            return $price->fresh();
        });
    }

    public function deactivate(ProductPrice $price): void
    {
        $price->update(['status' => 'inactive']);
    }

    public function findApplicablePrice(
        Product $product,
        int $quantity,
        ?Customer $customer = null,
        string $pricingType = 'standard',
        ?CarbonInterface $date = null
    ): ?ProductPrice {
        $date = $date ?: now();

        return ProductPrice::query()
            ->where('product_id', $product->id)
            ->where('pricing_type', $pricingType)
            ->where('status', 'active')
            ->where('min_quantity', '<=', $quantity)
            ->where(function ($q) use ($quantity) {
                $q->whereNull('max_quantity')->orWhere('max_quantity', '>=', $quantity);
            })
            ->where(function ($q) use ($date) {
                $q->whereNull('effective_from')->orWhereDate('effective_from', '<=', $date->toDateString());
            })
            ->where(function ($q) use ($date) {
                $q->whereNull('effective_to')->orWhereDate('effective_to', '>=', $date->toDateString());
            })
            ->where(function ($q) use ($customer) {
                if ($customer) {
                    $q->where('customer_id', $customer->id)->orWhereNull('customer_id');
                } else {
                    $q->whereNull('customer_id');
                }
            })
            ->orderByRaw('CASE WHEN customer_id IS NULL THEN 1 ELSE 0 END')
            ->orderByDesc('min_quantity')
            ->first();
    }

    private function ensureNoOverlap(array $data, ?int $ignoreId = null): void
    {
        if (($data['status'] ?? 'active') !== 'active') {
            return;
        }

        $query = ProductPrice::query()
            ->where('product_id', $data['product_id'])
            ->where('pricing_type', $data['pricing_type'])
            ->where('status', 'active');

        $customerId = $data['customer_id'] ?? null;
        $customerId === null
            ? $query->whereNull('customer_id')
            : $query->where('customer_id', $customerId);

        if ($ignoreId) {
            $query->whereKeyNot($ignoreId);
        }

        foreach ($query->get() as $row) {
            $newMin = (int) $data['min_quantity'];
            $newMax = $data['max_quantity'] !== null ? (int) $data['max_quantity'] : PHP_INT_MAX;
            $oldMin = (int) $row->min_quantity;
            $oldMax = $row->max_quantity !== null ? (int) $row->max_quantity : PHP_INT_MAX;

            $quantityOverlaps = max($newMin, $oldMin) <= min($newMax, $oldMax);

            if ($quantityOverlaps && $this->datesOverlap($data, $row)) {
                throw ValidationException::withMessages([
                    'min_quantity' => 'An active price already exists for this product, pricing type, customer scope and quantity/date range.',
                ]);
            }
        }
    }

    private function datesOverlap(array $data, ProductPrice $row): bool
    {
        $newFrom = $data['effective_from'] ? \Carbon\Carbon::parse($data['effective_from'])->startOfDay() : null;
        $newTo = $data['effective_to'] ? \Carbon\Carbon::parse($data['effective_to'])->endOfDay() : null;
        $oldFrom = $row->effective_from ? $row->effective_from->copy()->startOfDay() : null;
        $oldTo = $row->effective_to ? $row->effective_to->copy()->endOfDay() : null;

        if ($newTo && $oldFrom && $newTo->lt($oldFrom)) return false;
        if ($oldTo && $newFrom && $oldTo->lt($newFrom)) return false;
        return true;
    }
}
