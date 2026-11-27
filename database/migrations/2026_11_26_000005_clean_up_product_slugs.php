<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

return new class extends Migration
{
    /**
     * Product URLs now use the slug. Until now no public link used it, so this
     * is the moment to tidy slugs without breaking anything:
     *  - slugs that are not URL-safe ("Black Abaya") are normalised, and
     *  - the random suffix the old admin code appended ("black-abaya-x7Kq",
     *    recognisable by an upper-case letter) is replaced by a clean slug.
     * Hand-written, already clean slugs ("abaya-2024") are left alone.
     */
    public function up(): void
    {
        $taken = DB::table('products')->pluck('slug')->map(fn ($s) => (string) $s)->flip()->all();

        foreach (DB::table('products')->orderBy('id')->get(['id', 'name', 'slug']) as $product) {
            $slug = (string) $product->slug;
            $randomSuffix = preg_match('/-[A-Za-z0-9]{4}$/', $slug) && preg_match('/[A-Z]/', substr($slug, -4));

            if ($slug !== '' && $slug === Str::slug($slug) && ! $randomSuffix) {
                continue;
            }

            unset($taken[$slug]);
            $base = Str::slug($randomSuffix ? $product->name : ($slug ?: $product->name)) ?: 'product';
            $new = $base;
            for ($i = 2; isset($taken[$new]); $i++) {
                $new = $base . '-' . $i;
            }
            $taken[$new] = true;

            DB::table('products')->where('id', $product->id)->update(['slug' => $new]);
        }
    }

    public function down(): void
    {
        // Old slugs were partly random; they cannot be restored.
    }
};
