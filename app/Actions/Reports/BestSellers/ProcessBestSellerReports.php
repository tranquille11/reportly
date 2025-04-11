<?php

namespace App\Actions\Reports\BestSellers;

use App\Imports\BestSellersImport;
use Exception;
use Illuminate\Database\Eloquent\Collection;
use Maatwebsite\Excel\Facades\Excel;

class ProcessBestSellerReports
{
    public function handle(array $files, Collection $collections, Collection $categories)
    {
        $newProducts = collect([]);
        $oldProducts = collect([]);

        $names = $collections->pluck('name')->push('All Products', 'Best Sellers - All Categories');

        foreach ($files as $file) {
            $fileName = $file->getClientOriginalName();

            if (! $collection = $names->first(fn ($value) => str_contains($fileName, $value))) {
                throw new Exception('Upload must contain files with appropriate names by convention');
            }

            if ($collection == 'Best Sellers - All Categories') {
                $newProducts->push(Excel::toCollection(new BestSellersImport, $file)->collapse()->toArray());
            } else {
                $oldProducts->put($collection, Excel::toCollection(new BestSellersImport, $file)->collapse()->pluck('title')->toArray());
            }

        }

        if ($newProducts->isEmpty() || ($oldProducts->count() !== $collections->count() + 1)) {
            throw new Exception('Upload must contain files with appropriate names by convention');
        }

        $newProducts = $newProducts->collapse()
            ->filter(fn ($product) => ! str_ends_with($product['product_variant_price'], '.99') && $product['quantity_ordered'] >= $categories->firstWhere('name', $product['product_type'])->collections->first()->minimum_quantity)
            ->groupBy(fn ($product) => $categories->firstWhere('name', $product['product_type'])->collections->first()->name)
            ->map(fn ($products, $collection) => $products->sortByDesc('quantity_ordered')->pluck('product_title_at_time_of_sale')->unique()->take($collections->firstWhere('name', $collection)->threshold + 15))
            ->map(function ($products, $collection) use ($collections) {
                $extraProducts = $products->splice($collections->firstWhere('name', $collection)->threshold);

                return collect(['new' => $products, 'extra' => $extraProducts])->map(fn ($p, $key) => $key == 'new' ? $p->sort() : $p);
            });

        $allProducts = $newProducts->map(fn ($item, $key) => $item['new'])->flatten(1)->sort();

        $newProducts->put('All Products', $allProducts);

        $finalData = collect([]);

        $oldProducts->each(function ($current, $category) use ($newProducts, $finalData) {

            $current = collect($current);

            $new = $category == 'All Products' ? collect($newProducts->get($category)) : $newProducts->get($category)['new'];
            $finalData->put($category, [
                'add' => $new->diff($current)->toArray(),
                'remove' => $current->diff($new)->sort()->toArray(),
                'extra' => isset($newProducts->get($category)['extra']) ? $newProducts->get($category)['extra']->toArray() : [],
            ]);

        });

        $finalData = $finalData->sortKeysDesc()->toArray();

        foreach ($finalData as &$data) {
            $data = array_map(null, ...array_values($data));
        }

        return $finalData;
    }
}
