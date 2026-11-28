<?php

namespace App\Support;

use App\Models\Product;
use Illuminate\Support\Collection;

/**
 * Shop search, done in PHP over the active products (a shop catalogue is
 * small), so it can do what LIKE cannot:
 *
 * - every word must match (in any order), in the name, descriptions,
 *   category name or colour names, in any of the shop's languages;
 * - letters are compared loosely: case, accents and Arabic variants
 *   (أ/إ/آ → ا, ة → ه, ى → ي, harakat, tatweel) do not matter;
 * - with no exact match, small typos are forgiven ("abya" finds "abaya")
 *   and the corrected words are reported ("Showing results for …").
 */
class ProductSearch
{
    /** @var array<int, array{name: string, text: string, tokens: list<string>}>|null */
    private static ?array $index = null;

    /**
     * @return array{ids: list<int>, corrected: ?string}
     *         ids best match first; corrected is the query as understood
     *         when typos were fixed (null for an exact match / no results).
     */
    public static function search(string $query): array
    {
        $terms = self::terms($query);
        if (! $terms) {
            return ['ids' => [], 'corrected' => null];
        }

        $index = self::index();

        // 1. Exact (normalised) matches.
        $scores = [];
        foreach ($index as $id => $entry) {
            $score = 0;
            foreach ($terms as $term) {
                if (str_starts_with($entry['name'], $term) || str_contains($entry['name'], ' ' . $term)) {
                    $score += 3;
                } elseif (str_contains($entry['name'], $term)) {
                    $score += 2;
                } elseif (str_contains($entry['text'], $term)) {
                    $score += 1;
                } else {
                    continue 2;   // every word must match
                }
            }
            $scores[$id] = $score;
        }

        if ($scores) {
            return ['ids' => self::ranked($scores), 'corrected' => null];
        }

        // 2. Forgive small typos: each word may be replaced by a close word
        //    from the catalogue.
        $vocabulary = array_unique(array_merge(...array_values(array_column($index, 'tokens'))));
        $corrections = [];
        foreach ($terms as $term) {
            $corrections[$term] = self::closest($term, $vocabulary);
            if ($corrections[$term] === null) {
                return ['ids' => [], 'corrected' => null];
            }
        }

        foreach ($index as $id => $entry) {
            $score = 0;
            foreach ($corrections as $word) {
                if (str_contains($entry['name'], $word)) {
                    $score += 2;
                } elseif (str_contains($entry['text'], $word)) {
                    $score += 1;
                } else {
                    continue 2;
                }
            }
            $scores[$id] = $score;
        }

        return [
            'ids' => self::ranked($scores),
            'corrected' => $scores ? implode(' ', $corrections) : null,
        ];
    }

    /** Lower-case, no accents, Arabic letter variants unified. */
    public static function normalize(string $text): string
    {
        $text = mb_strtolower($text);
        if (class_exists(\Normalizer::class)) {
            $text = \Normalizer::normalize($text, \Normalizer::FORM_D) ?: $text;
        }
        $text = preg_replace('/\p{Mn}+/u', '', $text) ?? $text;          // accents and harakat
        $text = str_replace(
            ['ـ', 'أ', 'إ', 'آ', 'ٱ', 'ة', 'ى', 'ؤ', 'ئ'],
            ['', 'ا', 'ا', 'ا', 'ا', 'ه', 'ي', 'و', 'ي'],
            $text
        );

        return trim(preg_replace('/[^\p{L}\p{N}]+/u', ' ', $text) ?? '');
    }

    /** @return list<string> */
    public static function terms(string $query): array
    {
        $normalized = self::normalize(mb_substr($query, 0, 100));

        return $normalized === '' ? [] : array_values(array_unique(array_filter(explode(' ', $normalized), fn ($t) => $t !== '')));
    }

    /** Forget the cached catalogue (tests, and after saving a product). */
    public static function flush(): void
    {
        self::$index = null;
    }

    private static function index(): array
    {
        if (self::$index !== null && ! app()->runningUnitTests()) {
            return self::$index;
        }

        $index = [];
        Product::query()
            ->where('is_active', true)
            ->with('category')
            ->get(['id', 'name', 'short_description', 'long_description', 'category_id', 'colors'])
            ->each(function (Product $product) use (&$index) {
                $category = $product->category;
                $colors = collect($product->colors ?? [])->flatMap(fn ($c) => array_values($c['names'] ?? []));
                $name = self::normalize((string) $product->name);
                $text = self::normalize(implode(' ', array_filter([
                    $product->name,
                    strip_tags((string) $product->short_description),
                    strip_tags((string) $product->long_description),
                    $category?->name,
                    implode(' ', array_values($category?->name_translations ?? [])),
                    $colors->implode(' '),
                ])));
                $index[$product->id] = [
                    'name' => $name,
                    'text' => $text,
                    'tokens' => array_values(array_unique(array_filter(explode(' ', $text), fn ($t) => mb_strlen($t) >= 3))),
                ];
            });

        return self::$index = $index;
    }

    /** @param array<int, int> $scores  @return list<int> */
    private static function ranked(array $scores): array
    {
        // Higher score first, then newer products (higher id) first.
        uksort($scores, fn ($a, $b) => [$scores[$b], $b] <=> [$scores[$a], $a]);

        return array_map('intval', array_keys($scores));
    }

    /** The catalogue word closest to $term, if close enough to be a typo. */
    private static function closest(string $term, array $vocabulary): ?string
    {
        $length = mb_strlen($term);
        if ($length < 3) {
            return null;
        }
        $allowed = $length <= 4 ? 1 : 2;

        $best = null;
        $bestDistance = PHP_INT_MAX;
        foreach ($vocabulary as $word) {
            if (abs(mb_strlen($word) - $length) > $allowed) {
                continue;
            }
            $distance = self::distance($term, $word);
            if ($distance <= $allowed && ($distance < $bestDistance || ($distance === $bestDistance && strcmp($word, $best) < 0))) {
                $best = $word;
                $bestDistance = $distance;
            }
        }

        return $best;
    }

    /** Levenshtein distance on characters (not bytes), so Arabic works. */
    private static function distance(string $a, string $b): int
    {
        $a = mb_str_split($a);
        $b = mb_str_split($b);
        $previous = range(0, count($b));
        foreach ($a as $i => $charA) {
            $current = [$i + 1];
            foreach ($b as $j => $charB) {
                $current[] = min($previous[$j + 1] + 1, $current[$j] + 1, $previous[$j] + ($charA === $charB ? 0 : 1));
            }
            $previous = $current;
        }

        return $previous[count($b)];
    }
}
