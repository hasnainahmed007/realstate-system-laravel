<?php

namespace Database\Seeders;

use Botble\Language\Models\LanguageMeta;
use Botble\RealEstate\Models\Project;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Language;

class ProjectSeeder extends Seeder
{
    public function run(): void
    {
        $items = LanguageMeta::where('reference_type', Project::class)
            ->where('lang_meta_code', '!=', Language::getDefaultLocaleCode())
            ->get();

        foreach ($items as $item) {
            $originalItem = Project::find($item->reference_id);

            if (! $originalItem) {
                continue;
            }

            $originalId = LanguageMeta::where('lang_meta_origin', $item->lang_meta_origin)
                ->where('lang_meta_code', Language::getDefaultLocaleCode())
                ->value('reference_id');

            if (! $originalId) {
                continue;
            }

            DB::table('re_projects_translations')->insert([
                're_projects_id' => $originalId,
                'lang_code' => $item->lang_meta_code,
                'name' => $originalItem->name,
                'description' => $originalItem->description,
                'content' => $originalItem->content,
                'location' => $originalItem->location,
            ]);

            $originalItem->delete();

            $item->delete();
        }
    }
}
