<?php

namespace App\Services;

use App\Models\Product;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class ProductService
{
    public function create(array $data): Product
    {
        return DB::transaction(function () use ($data) {
            $image = $data['image'] ?? null;
            unset($data['image']);

            if ($image instanceof UploadedFile) {
                $data['image_path'] = $image->store('products', 'public');
            }

            return Product::create($data);
        });
    }

    public function update(Product $product, array $data): Product
    {
        return DB::transaction(function () use ($product, $data) {
            $image = $data['image'] ?? null;
            unset($data['image']);

            if ($image instanceof UploadedFile) {
                if ($product->image_path) {
                    Storage::disk('public')->delete($product->image_path);
                }

                $data['image_path'] = $image->store('products', 'public');
            }

            $product->update($data);

            return $product->fresh();
        });
    }

    public function delete(Product $product): void
    {
        $product->update(['status' => 'inactive']);
    }
}
