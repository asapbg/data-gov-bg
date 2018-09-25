<?php

use App\Category;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddUncategorizedCategory extends Migration
{
    public function __construct()
    {
        $this->categories = [
            [
                'name'  => [
                    'bg'    => 'Некатегоризирани',
                    'en'    => 'Uncategorized',
                ]
            ],
        ];
    }

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!env('IS_TOOL')) {
            $currCount = Category::count();

            foreach ($this->categories as $catData) {
                $catData['active'] = true;
                $catData['ordering'] = $currCount + 1;
                $catData['icon_mime_type'] = 'image/svg+xml';

                if (!Category::where($catData)->count()) {
                    Category::create($catData);
                }
            }
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        if (!env('IS_TOOL')) {
            foreach ($this->categories as $catData) {
                if (Category::where($catData)->count()) {
                    Category::where($catData)->get()->delete();
                }
            }
        }
    }
}
