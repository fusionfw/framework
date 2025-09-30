<?php

namespace App\Modules\Blog\Models;

use Fusion\Core\Model;

class Post extends Model
{
    protected $table = 'posts';
    protected $fillable = ['title', 'content', 'slug', 'excerpt', 'featured_image', 'status', 'author_id', 'published_at'];

    protected $dates = ['published_at'];

    public function getExcerptAttribute($value)
    {
        if ($value) {
            return $value;
        }

        return substr(strip_tags($this->content), 0, 150) . '...';
    }

    public function getSlugAttribute($value)
    {
        if ($value) {
            return $value;
        }

        return $this->generateSlug($this->title);
    }

    private function generateSlug($title)
    {
        $slug = strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $title)));
        $slug = trim($slug, '-');

        // Check if slug exists
        $originalSlug = $slug;
        $counter = 1;

        while (static::where('slug', $slug)->where('id', '!=', $this->id ?? 0)->exists()) {
            $slug = $originalSlug . '-' . $counter;
            $counter++;
        }

        return $slug;
    }

    public function scopePublished($query)
    {
        return $query->where('status', 'published')
            ->where('published_at', '<=', now());
    }

    public function scopeDraft($query)
    {
        return $query->where('status', 'draft');
    }

    public function author()
    {
        return $this->belongsTo('App\Modules\User\Models\User', 'author_id');
    }
}
