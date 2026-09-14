<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('knowledge_page_settings', function (Blueprint $table): void {
            $table->id();
            $table->string('site_name')->default('E-Book KM');
            $table->string('site_logo_path', 500)->nullable();

            $table->boolean('hero_enabled')->default(true);
            $table->string('hero_title')->default('E-Book KM');
            $table->text('hero_description')->nullable();
            $table->string('hero_image_path', 500)->nullable();
            $table->boolean('hero_button_enabled')->default(false);
            $table->string('hero_button_label', 150)->nullable();
            $table->string('hero_button_url', 2048)->nullable();
            $table->unsignedInteger('hero_sort_order')->default(10);

            $table->boolean('about_enabled')->default(true);
            $table->string('about_title')->default('เกี่ยวกับเรา');
            $table->text('about_content')->nullable();
            $table->string('about_image_path', 500)->nullable();
            $table->unsignedInteger('about_sort_order')->default(20);

            $table->boolean('books_enabled')->default(true);
            $table->string('books_title')->default('E-Book KM');
            $table->text('books_description')->nullable();
            $table->unsignedInteger('books_sort_order')->default(30);

            $table->boolean('contact_enabled')->default(true);
            $table->string('contact_title')->default('ติดต่อเรา');
            $table->text('contact_description')->nullable();
            $table->string('organization_name')->nullable();
            $table->text('address')->nullable();
            $table->string('phone', 30)->nullable();
            $table->string('email', 254)->nullable();
            $table->string('map_embed_url', 2048)->nullable();
            $table->unsignedInteger('contact_sort_order')->default(40);

            $table->boolean('contact_form_enabled')->default(true);
            $contactLabels = ['name' => 'ชื่อ', 'phone' => 'โทรศัพท์', 'email' => 'อีเมล', 'message' => 'ข้อความ'];
            foreach ($contactLabels as $field => $label) {
                $table->boolean("contact_{$field}_enabled")->default(true);
                $table->boolean("contact_{$field}_required")->default($field === 'message');
                $table->string("contact_{$field}_label", 150)->default($label);
            }
            $table->string('contact_submit_label', 150)->default('ส่งข้อความ');

            $table->boolean('footer_enabled')->default(true);
            $table->string('footer_logo_path', 500)->nullable();
            $table->string('footer_organization_name')->nullable();
            $table->text('footer_description')->nullable();
            $table->text('footer_address')->nullable();
            $table->string('footer_phone', 30)->nullable();
            $table->string('footer_email', 254)->nullable();
            $table->string('footer_copyright')->nullable();
            $table->boolean('footer_show_logo')->default(true);
            $table->boolean('footer_show_description')->default(true);
            $table->boolean('footer_show_address')->default(true);
            $table->boolean('footer_show_phone')->default(true);
            $table->boolean('footer_show_email')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('knowledge_page_settings');
    }
};
