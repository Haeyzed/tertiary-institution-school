<?php

namespace App\Helpers;

use Stichoza\GoogleTranslate\GoogleTranslate;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

/**
 * Helper class for translating text using Google Translate.
 */
class TranslateHelper
{
    /**
     * The Google Translate instance.
     *
     * @var GoogleTranslate
     */
    protected static GoogleTranslate $translator;

    /**
     * Cache duration for translations in minutes.
     *
     * @var int
     */
    protected static int $cacheDuration = 1440; // 24 hours

    /**
     * Get or create the Google Translate instance.
     *
     * @return GoogleTranslate
     */
    protected static function getTranslator(): GoogleTranslate
    {
        if (!static::$translator) {
            static::$translator = new GoogleTranslate();
        }

        return static::$translator;
    }

    /**
     * Translate text to the specified language.
     *
     * @param string $text The text to translate
     * @param string $targetLanguage The target language code (e.g., 'es', 'fr', 'de')
     * @param string|null $sourceLanguage The source language code (null for auto-detection)
     * @param bool $useCache Whether to use caching for translations
     * @return string The translated text
     */
    public static function translate(
        string $text,
        string $targetLanguage,
        ?string $sourceLanguage = null,
        bool $useCache = true
    ): string {
        if (empty($text)) {
            return $text;
        }

        $cacheKey = static::getCacheKey($text, $targetLanguage, $sourceLanguage);

        if ($useCache && Cache::has($cacheKey)) {
            return Cache::get($cacheKey);
        }

        try {
            $translator = static::getTranslator();

            if ($sourceLanguage) {
                $translator->setSource($sourceLanguage);
            }

            $translator->setTarget($targetLanguage);
            $translatedText = $translator->translate($text);

            if ($useCache) {
                Cache::put($cacheKey, $translatedText, static::$cacheDuration);
            }

            return $translatedText;
        } catch (\Exception $e) {
            Log::error('Translation failed: ' . $e->getMessage(), [
                'text' => $text,
                'target_language' => $targetLanguage,
                'source_language' => $sourceLanguage,
            ]);

            // Return original text if translation fails
            return $text;
        }
    }

    /**
     * Translate an array of texts.
     *
     * @param array $texts Array of texts to translate
     * @param string $targetLanguage The target language code
     * @param string|null $sourceLanguage The source language code
     * @param bool $useCache Whether to use caching
     * @return array Array of translated texts
     */
    public static function translateArray(
        array $texts,
        string $targetLanguage,
        ?string $sourceLanguage = null,
        bool $useCache = true
    ): array {
        $translatedTexts = [];

        foreach ($texts as $key => $text) {
            if (is_string($text)) {
                $translatedTexts[$key] = static::translate($text, $targetLanguage, $sourceLanguage, $useCache);
            } else {
                $translatedTexts[$key] = $text;
            }
        }

        return $translatedTexts;
    }

    /**
     * Recursively translate data structure (arrays and objects).
     *
     * @param mixed $data The data to translate
     * @param string $targetLanguage The target language code
     * @param array $fieldsToTranslate Fields that should be translated
     * @param string|null $sourceLanguage The source language code
     * @param bool $useCache Whether to use caching
     * @return mixed The translated data
     */
    public static function translateData(
        mixed   $data,
        string  $targetLanguage,
        array   $fieldsToTranslate = [],
        ?string $sourceLanguage = null,
        bool    $useCache = true
    ): mixed
    {
        if (is_array($data)) {
            return static::translateArrayData($data, $targetLanguage, $fieldsToTranslate, $sourceLanguage, $useCache);
        }

        if (is_object($data)) {
            return static::translateObjectData($data, $targetLanguage, $fieldsToTranslate, $sourceLanguage, $useCache);
        }

        return $data;
    }

    /**
     * Translate array data.
     *
     * @param array $data
     * @param string $targetLanguage
     * @param array $fieldsToTranslate
     * @param string|null $sourceLanguage
     * @param bool $useCache
     * @return array
     */
    protected static function translateArrayData(
        array $data,
        string $targetLanguage,
        array $fieldsToTranslate,
        ?string $sourceLanguage,
        bool $useCache
    ): array {
        foreach ($data as $key => $value) {
            if (empty($fieldsToTranslate) || in_array($key, $fieldsToTranslate)) {
                if (is_string($value)) {
                    $data[$key] = static::translate($value, $targetLanguage, $sourceLanguage, $useCache);
                } elseif (is_array($value) || is_object($value)) {
                    $data[$key] = static::translateData($value, $targetLanguage, $fieldsToTranslate, $sourceLanguage, $useCache);
                }
            } elseif (is_array($value) || is_object($value)) {
                $data[$key] = static::translateData($value, $targetLanguage, $fieldsToTranslate, $sourceLanguage, $useCache);
            }
        }

        return $data;
    }

    /**
     * Translate object data.
     *
     * @param object $data
     * @param string $targetLanguage
     * @param array $fieldsToTranslate
     * @param string|null $sourceLanguage
     * @param bool $useCache
     * @return object
     */
    protected static function translateObjectData(
        object  $data,
        string  $targetLanguage,
        array   $fieldsToTranslate,
        ?string $sourceLanguage,
        bool    $useCache
    ): object
    {
        $dataArray = json_decode(json_encode($data), true);
        $translatedArray = static::translateArrayData($dataArray, $targetLanguage, $fieldsToTranslate, $sourceLanguage, $useCache);

        return json_decode(json_encode($translatedArray));
    }

    /**
     * Generate cache key for translation.
     *
     * @param string $text
     * @param string $targetLanguage
     * @param string|null $sourceLanguage
     * @return string
     */
    protected static function getCacheKey(string $text, string $targetLanguage, ?string $sourceLanguage): string
    {
        $source = $sourceLanguage ?? 'auto';
        return 'translation:' . md5($text . $targetLanguage . $source);
    }

    /**
     * Clear translation cache.
     *
     * @return bool
     */
    public static function clearCache(): bool
    {
        return Cache::flush();
    }

    /**
     * Set cache duration in minutes.
     *
     * @param int $minutes
     * @return void
     */
    public static function setCacheDuration(int $minutes): void
    {
        static::$cacheDuration = $minutes;
    }
}
