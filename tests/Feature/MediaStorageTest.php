<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Product;
use App\Models\User;
use App\Support\Media;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class MediaStorageTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    private Category $category;

    protected function setUp(): void
    {
        parent::setUp();
        $this->admin = User::factory()->create(['role' => 'admin']);
        $this->category = Category::factory()->create();
    }

    private function useR2(): \Illuminate\Contracts\Filesystem\Filesystem
    {
        config(['shop.media_disk' => 'r2']);

        return Storage::fake('r2', ['url' => 'https://img.rukn.test']);
    }

    private function createProductWithPhoto(): Product
    {
        $this->actingAs($this->admin)->post('/en/admin/products', [
            'name' => 'Black Abaya', 'category_id' => $this->category->id, 'price' => 49, 'stock' => 3,
            'short_description' => 'Soft.', 'is_active' => 1,
            'images' => [UploadedFile::fake()->image('front.jpg', 600, 800)],
        ])->assertRedirect()->assertSessionHasNoErrors();

        return Product::latest('id')->with('images')->first();
    }

    public function test_by_default_photos_are_stored_on_the_server_disk(): void
    {
        $public = Storage::fake('public');

        $product = $this->createProductWithPhoto();
        $path = $product->images->first()->path;

        $public->assertExists($path);
        $this->assertStringStartsWith("products/{$product->id}/", $path);
        $this->assertStringEndsWith('/storage/' . $path, $product->primaryImageUrl());
    }

    public function test_with_r2_photos_are_uploaded_there_and_served_from_its_public_url(): void
    {
        $r2 = $this->useR2();
        $public = Storage::fake('public');

        $product = $this->createProductWithPhoto();
        $path = $product->images->first()->path;

        $r2->assertExists($path);
        $public->assertMissing($path);
        $this->assertSame('https://img.rukn.test/' . $path, $product->primaryImageUrl());

        $this->get('/en/products/' . $product->slug)->assertOk()->assertSee('https://img.rukn.test/' . $path, false);
        $this->get('/en/products')->assertSee('https://img.rukn.test/' . $path, false);
    }

    public function test_removing_a_photo_or_the_product_deletes_the_file_from_r2(): void
    {
        $r2 = $this->useR2();
        $product = $this->createProductWithPhoto();
        $image = $product->images->first();

        $this->actingAs($this->admin)->put("/en/admin/products/{$product->slug}", [
            'name' => 'Black Abaya', 'category_id' => $this->category->id, 'price' => 49, 'stock' => 3,
            'short_description' => 'Soft.', 'is_active' => 1, 'images_to_delete' => [$image->id],
        ])->assertRedirect();
        $r2->assertMissing($image->path);

        $product = $this->createProductWithPhoto();
        $path = $product->images->first()->path;
        $this->actingAs($this->admin)->delete("/en/admin/products/{$product->slug}")->assertRedirect();
        $r2->assertMissing($path);
    }

    public function test_avatars_use_the_media_disk_too(): void
    {
        $r2 = $this->useR2();
        $user = User::factory()->create();

        $this->actingAs($user)->patch('/en/profile', [
            'name' => $user->name, 'email' => $user->email,
            'avatar' => UploadedFile::fake()->image('me.png', 200, 200),
        ])->assertSessionHasNoErrors();

        $avatar = $user->fresh()->avatar;
        $r2->assertExists($avatar);
        $this->get('/en')->assertSee('https://img.rukn.test/' . $avatar, false);
    }

    public function test_legacy_image_paths_and_full_urls_keep_working(): void
    {
        $this->useR2();

        $this->assertSame(asset('images/abaya.jpg'), (new Product(['image_url' => 'images/abaya.jpg']))->legacyImageUrl());
        $this->assertSame('https://cdn.example.com/a.jpg', (new Product(['image_url' => 'https://cdn.example.com/a.jpg']))->legacyImageUrl());
        $this->assertSame('https://img.rukn.test/products/1/a.jpg', (new Product(['image_url' => 'products/1/a.jpg']))->legacyImageUrl());
    }

    public function test_the_r2_disk_is_configured_for_cloudflare(): void
    {
        config([
            'filesystems.disks.r2.key' => 'id', 'filesystems.disks.r2.secret' => 'secret', 'filesystems.disks.r2.bucket' => 'rukn',
            'filesystems.disks.r2.endpoint' => 'https://abc123.r2.cloudflarestorage.com',
            'filesystems.disks.r2.url' => 'https://pub-xyz.r2.dev',
        ]);

        $this->assertSame('https://pub-xyz.r2.dev/products/1/a.jpg', Storage::disk('r2')->url('products/1/a.jpg'));
        $this->assertSame('auto', config('filesystems.disks.r2.region'));
        $this->assertTrue(config('filesystems.disks.r2.use_path_style_endpoint'));
    }

    public function test_existing_local_photos_can_be_copied_to_r2(): void
    {
        $public = Storage::fake('public');
        $public->put('products/1/old.jpg', 'jpeg-bytes');
        $public->put('avatars/me.png', 'png-bytes');
        $r2 = $this->useR2();
        $r2->put('avatars/me.png', 'png-bytes');

        $this->artisan('media:copy')->expectsOutputToContain('Copied 1 file(s)')->assertSuccessful();

        $this->assertSame('jpeg-bytes', $r2->get('products/1/old.jpg'));
    }

    public function test_copy_refuses_when_the_media_disk_is_still_local(): void
    {
        config(['shop.media_disk' => 'public']);

        $this->artisan('media:copy')->assertFailed();
    }

    public function test_a_storage_error_on_delete_is_logged_not_thrown(): void
    {
        config(['shop.media_disk' => 'broken', 'filesystems.disks.broken' => ['driver' => 'does-not-exist']]);

        Media::delete('products/1/a.jpg'); // no exception

        $this->assertTrue(true);
    }

    private function brokenStorage(): void
    {
        // An S3 disk that cannot be reached (wrong endpoint) and must throw.
        config(['shop.media_disk' => 'r2', 'filesystems.disks.r2' => [
            'driver' => 's3', 'key' => 'k', 'secret' => 's', 'region' => 'auto', 'bucket' => 'b',
            'endpoint' => 'http://127.0.0.1:1', 'use_path_style_endpoint' => true, 'throw' => true,
            'http' => ['connect_timeout' => 1], 'retries' => 0,
        ]]);
    }

    public function test_a_failed_photo_upload_keeps_the_product_and_tells_the_admin(): void
    {
        $this->brokenStorage();

        $this->actingAs($this->admin)->post('/en/admin/products', [
            'name' => 'Black Abaya', 'category_id' => $this->category->id, 'price' => 49, 'stock' => 3,
            'short_description' => 'Soft.', 'is_active' => 1,
            'images' => [UploadedFile::fake()->image('front.jpg')],
        ])->assertRedirect()->assertSessionHas('warning', '1 photo(s) could not be uploaded. Please try again; details are in the error log.');

        $product = Product::sole();
        $this->assertCount(0, $product->images);

        $this->actingAs($this->admin)->followingRedirects()->get('/en/admin/products')->assertOk();
    }

    public function test_admin_layout_shows_flash_messages(): void
    {
        $this->actingAs($this->admin)->withSession(['warning' => 'Careful!', 'success' => 'Saved.'])
            ->get('/en/admin/products')->assertOk()->assertSee('Careful!')->assertSee('Saved.');
    }

    public function test_a_failed_avatar_upload_shows_an_error(): void
    {
        $this->brokenStorage();
        $user = User::factory()->create(['avatar' => null]);

        $this->actingAs($user)->from('/en/profile')->patch('/en/profile', [
            'name' => $user->name, 'email' => $user->email,
            'avatar' => UploadedFile::fake()->image('me.png'),
        ])->assertRedirect('/en/profile')->assertSessionHasErrors(['avatar' => 'Your photo could not be uploaded. Please try again later.']);

        $this->assertNull($user->fresh()->avatar);
    }
}
