<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CategoryTranslationTest extends TestCase
{
    use RefreshDatabase;

    public function test_display_name_uses_the_active_locale_and_falls_back_to_name(): void
    {
        $category = Category::factory()->create([
            'name' => 'Abayas',
            'name_translations' => ['nl' => "Abaya's", 'ar' => 'عبايات'],
        ]);

        app()->setLocale('ar');
        $this->assertSame('عبايات', $category->display_name);

        app()->setLocale('nl');
        $this->assertSame("Abaya's", $category->display_name);

        app()->setLocale('en'); // no English translation
        $this->assertSame('Abayas', $category->display_name);

        $plain = Category::factory()->create(['name' => 'Gifts', 'name_translations' => null]);
        $this->assertSame('Gifts', $plain->display_name);
    }

    public function test_shop_shows_the_translated_category_name(): void
    {
        $category = Category::factory()->create([
            'name' => 'Abayas',
            'is_active' => true,
            'name_translations' => ['ar' => 'عبايات'],
        ]);
        Product::factory()->for($category)->create(['is_active' => true, 'stock' => 3]);

        $this->get('/ar/products')->assertOk()->assertSee('عبايات');
        $this->get('/en/products')->assertOk()->assertSee('Abayas');
    }

    public function test_admin_can_save_translations_and_empty_fields_are_dropped(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $category = Category::factory()->create(['name' => 'Abayas', 'slug' => 'abayas']);

        $this->actingAs($admin)->patch("/en/admin/categories/{$category->id}", [
            'name' => 'Abayas',
            'slug' => 'abayas',
            'is_active' => 1,
            'name_translations' => ['nl' => "Abaya's", 'en' => '  ', 'ar' => 'عبايات', 'fr' => 'Abayas'],
        ])->assertRedirect();

        $this->assertSame(['nl' => "Abaya's", 'ar' => 'عبايات'], $category->fresh()->name_translations);

        // Clearing every field stores null, so the base name is used again.
        $this->actingAs($admin)->patch("/en/admin/categories/{$category->id}", [
            'name' => 'Abayas',
            'slug' => 'abayas',
            'name_translations' => ['nl' => '', 'en' => '', 'ar' => ''],
        ]);

        $this->assertNull($category->fresh()->name_translations);
    }

    public function test_admin_category_forms_show_the_translation_fields(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $category = Category::factory()->create(['name_translations' => ['ar' => 'عبايات']]);

        $this->actingAs($admin)->get('/en/admin/categories/create')
            ->assertOk()
            ->assertSee('name="name_translations[ar]"', false);

        $this->actingAs($admin)->get("/en/admin/categories/{$category->id}/edit")
            ->assertOk()
            ->assertSee('value="عبايات"', false);
    }

    public function test_admin_category_index_renders_with_translated_names(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $withProduct = Category::factory()->create(['name' => 'Abayas', 'name_translations' => ['nl' => "Abaya's"]]);
        Product::factory()->for($withProduct)->create();
        Category::factory()->create(['name' => 'Empty']);

        $this->actingAs($admin)->get('/nl/admin/categories')
            ->assertOk()
            // Name with an apostrophe must stay valid inside the Alpine handler.
            ->assertSee("deleteCategoryName = 'Abaya\\u0027s'", false)
            ->assertDontSee("deleteCategoryName = 'Abaya&#039;s'", false);
    }
}
