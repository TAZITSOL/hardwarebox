<?php

namespace App\Models;

use App\Helpers\Helpers;
use Illuminate\Database\Eloquent\Model;
use Spatie\Translatable\HasTranslations;
use Cviebrock\EloquentSluggable\Sluggable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Tag extends Model
{
    use Sluggable, HasFactory, HasTranslations;

    public $translatable = [
        'name','description'
    ];

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name',
        'slug',
        'type',
        'status',
        'description',
        'created_by_id'
    ];

    protected $casts = [
        'status' => 'integer',
        'blogs_count' => 'integer',
        'products_count' => 'integer',
    ];

    protected $withCount = [
        'blogs',
        'products'
    ];

    public static function boot()
    {
        parent::boot();
        static::saving(function ($model) {
            $model->created_by_id = Helpers::getCurrentUserId();
            if (request()['slug']) {
                $model->slug = request()['slug'];
            }
        });
    }

    public function sluggable(): array
    {
        return [
            'slug' => [
                'source' => 'name',
                'onUpdate' => true,
            ]
        ];
    }

    public function toArray()
    {
        $attributes = parent::toArray();
        $translated = Helpers::handleModelTranslations($this, $attributes, $this->translatable);
        return $translated;
    }


    /**
     * @return Int
     */
    public function getId($request)
    {
        return ($request->id) ? $request->id : $request->route('tag')->id;
    }

    /**
     * @return BelongsToMany
     */
    public function products(): BelongsToMany
    {
        return $this->belongsToMany(Product::class, 'product_tags');
    }

    /**
     * @return belongsToMany
     */
    public function blogs()
    {
        return $this->belongsToMany(Blog::class, 'blog_tags');
    }
}
