<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class KnowledgePageNavItem extends Model
{
    public const PLACEMENTS = ['navbar', 'footer', 'social'];

    public const TARGETS = ['_self', '_blank'];

    public const ICON_KEYS = ['home', 'info', 'book', 'contact', 'link', 'facebook', 'line', 'website'];

    protected $fillable = ['placement', 'label', 'url', 'target', 'icon_key', 'sort_order', 'is_visible'];

    protected $casts = ['sort_order' => 'integer', 'is_visible' => 'boolean'];
}
