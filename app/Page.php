<?php

namespace App;

use App\Contracts\TranslatableInterface;
use App\Http\Controllers\Traits\RecordSignature;
use App\Translator\Translatable;
use Illuminate\Database\Eloquent\Model;
use Laravel\Scout\Searchable;

class Page extends Model implements TranslatableInterface
{
    use Translatable;
    use RecordSignature;
    use Searchable;

    protected $guarded = ['id'];

    const DATE_TYPE_UPDATED = 'updated';
    const DATE_TYPE_CREATED = 'created';
    const DATE_TYPE_VALID = 'valid';
    const ACTIVE_FALSE = 0;

    const TYPE_NEWS = 1;
    const TYPE_PAGE = 2;

    const NEWS_TYPE_REGULAR = 0;
    const NEWS_TYPE_LATEST_NEWS = 1;
    const NEWS_TYPE_ALERT = 2;

    const RESOURCE_RESPONSE_JSON = 1;
    const RESOURCE_RESPONSE_CSV = 2;

    protected static $translatable = [
        'title'             => 'label',
        'abstract'          => 'text',
        'body'              => 'text',
        'head_title'        => 'label',
        'meta_descript'     => 'text',
        'meta_key_words'    => 'label',
    ];

    public static function getTransFields()
    {
        return self::$translatable;
    }

    public static function getResourceResponseFormats()
    {
        return [
            self::RESOURCE_RESPONSE_JSON => 'JSON',
            self::RESOURCE_RESPONSE_CSV  => 'CSV',
        ];
    }

    public function toSearchableArray()
    {
        return [
            'id'                => $this->id,
            'title'             => $this->concatTranslations('title'),
            'abstract'          => $this->concatTranslations('abstract'),
            'body'              => $this->concatTranslations('body'),
            'head_title'        => $this->concatTranslations('head_title'),
            'meta_descript'     => $this->concatTranslations('meta_descript'),
            'meta_key_words'    => $this->concatTranslations('meta_key_words'),
        ];
    }

    public function section()
    {
        return $this->belongsTo('App\Section');
    }

    public function searchableAs()
    {
        return 'pages';
    }
}
