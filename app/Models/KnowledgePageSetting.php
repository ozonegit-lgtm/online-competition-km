<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class KnowledgePageSetting extends Model
{
    public const ASSET_COLUMNS = [
        'logo' => 'site_logo_path',
        'hero' => 'hero_image_path',
        'about' => 'about_image_path',
        'footer-logo' => 'footer_logo_path',
    ];

    protected $guarded = ['id', 'created_at', 'updated_at'];

    protected $attributes = [
        'site_name' => 'E-Book KM',
        'hero_enabled' => true,
        'hero_title' => 'E-Book KM',
        'hero_button_enabled' => false,
        'hero_sort_order' => 1,
        'about_enabled' => true,
        'about_title' => 'เกี่ยวกับเรา',
        'about_sort_order' => 2,
        'books_enabled' => true,
        'books_title' => 'E-Book KM',
        'books_sort_order' => 3,
        'contact_enabled' => true,
        'contact_title' => 'ติดต่อเรา',
        'contact_sort_order' => 4,
        'contact_form_enabled' => true,
        'contact_name_enabled' => true,
        'contact_name_required' => false,
        'contact_name_label' => 'ชื่อ',
        'contact_phone_enabled' => true,
        'contact_phone_required' => false,
        'contact_phone_label' => 'โทรศัพท์',
        'contact_email_enabled' => true,
        'contact_email_required' => false,
        'contact_email_label' => 'อีเมล',
        'contact_message_enabled' => true,
        'contact_message_required' => true,
        'contact_message_label' => 'ข้อความ',
        'contact_submit_label' => 'ส่งข้อความ',
        'footer_enabled' => true,
        'footer_show_logo' => true,
        'footer_show_description' => true,
        'footer_show_address' => true,
        'footer_show_phone' => true,
        'footer_show_email' => true,
    ];

    protected $casts = [
        'hero_enabled' => 'boolean', 'hero_button_enabled' => 'boolean',
        'about_enabled' => 'boolean', 'books_enabled' => 'boolean',
        'contact_enabled' => 'boolean', 'contact_form_enabled' => 'boolean',
        'contact_name_enabled' => 'boolean', 'contact_name_required' => 'boolean',
        'contact_phone_enabled' => 'boolean', 'contact_phone_required' => 'boolean',
        'contact_email_enabled' => 'boolean', 'contact_email_required' => 'boolean',
        'contact_message_enabled' => 'boolean', 'contact_message_required' => 'boolean',
        'footer_enabled' => 'boolean', 'footer_show_logo' => 'boolean',
        'footer_show_description' => 'boolean', 'footer_show_address' => 'boolean',
        'footer_show_phone' => 'boolean', 'footer_show_email' => 'boolean',
        'hero_sort_order' => 'integer', 'about_sort_order' => 'integer',
        'books_sort_order' => 'integer', 'contact_sort_order' => 'integer',
    ];

    public static function current(): self
    {
        return static::query()->first() ?? new static;
    }
}
