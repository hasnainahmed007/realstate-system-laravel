<?php

namespace Database\Seeders;

use Botble\Language\Models\LanguageMeta;
use Botble\RealEstate\Models\Property;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Language;
use RealEstateHelper;

class PropertySeeder extends Seeder
{
    public function run(): void
    {
        $items = LanguageMeta::where('reference_type', Property::class)
            ->where('lang_meta_code', '!=', Language::getDefaultLocaleCode())
            ->get();

        foreach ($items as $item) {
            $originalItem = Property::find($item->reference_id);

            if (! $originalItem) {
                continue;
            }

            $originalId = LanguageMeta::where('lang_meta_origin', $item->lang_meta_origin)
                ->where('lang_meta_code', Language::getDefaultLocaleCode())
                ->value('reference_id');

            if (! $originalId) {
                continue;
            }

            DB::table('re_properties_translations')->insert([
                're_properties_id' => $originalId,
                'lang_code' => $item->lang_meta_code,
                'name' => $originalItem->name,
                'description' => $originalItem->description,
                'content' => $originalItem->content,
                'location' => $originalItem->location,
            ]);

            $originalItem->delete();

            $item->delete();
        }

        Property::query()->update(['expire_date' => now()->addDays(RealEstateHelper::propertyExpiredDays())]);

        DB::statement('UPDATE re_properties SET views = FLOOR(rand() * 10000) + 1;');
    }
}
