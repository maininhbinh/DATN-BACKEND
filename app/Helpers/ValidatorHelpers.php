<?php
namespace App\Helpers;

use Illuminate\Support\Facades\Validator as UseValidator;
use function MongoDB\BSON\toJSON;

class ValidatorHelpers
{
    static function validatorName($value){
        return trim(strip_tags(
            UseValidator::make(
                [
                    'value' => $value
                ],
                [
                    'value' => ['required', 'string', 'max:255'],
                ]
            )->validated()['value']
        ));
    }

    static function validatorProductItem($productItem){
        if(!$productItem || count($productItem) == 0){
            return false;
        }

        foreach($productItem as $item){
            if((float) $item->price_sale > $item->price){
                return false;
            }

            $variantsCollect = collect($item->variants);

            $checkVariants = $variantsCollect->first(function($item){
                return strlen($item->variant) > 15 || strlen($item->attribute) > 15 || strlen(trim($item->sku ?? '')) > 20 ;
            });

            if($checkVariants){
                return false;
            }
        }

        $collection = collect($productItem);

        // Hàm để chuẩn hóa và sắp xếp các variants cho việc so sánh
        $normalizeVariants = function ($variants) {
            return collect($variants)
                ->sortBy('variant')
                ->map(function ($variant) {
                    return $variant->variant . ':' . $variant->attribute;
                })
                ->values()
                ->toJson();
        };

        // Nhóm các phần tử theo normalized variants
        $grouped = $collection->groupBy(function ($item) use ($normalizeVariants) {
            return $normalizeVariants($item->variants);
        });

        // Lọc ra các nhóm có nhiều hơn một phần tử (nghĩa là các variants trùng lặp)
        $duplicates = $grouped->filter(function ($group) {
            return $group->count() > 1;
        });

        if($duplicates->isNotEmpty()){
            return false;
        }

        return true;
    }
}
