<?php
/**
 * Language/Translation Manager
 * Simple array-based i18n system
 */

namespace aReports\Core;

class Lang
{
    private static ?Lang $instance = null;
    private string $locale = 'en';
    private string $fallback = 'en';
    private array $translations = [];
    private string $langPath;

    private function __construct()
    {
        $this->langPath = dirname(__DIR__) . '/lang';
    }

    public static function getInstance(): self
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Set current locale
     */
    public function setLocale(string $locale): void
    {
        if (in_array($locale, ['en', 'ru'])) {
            $this->locale = $locale;
        }
    }

    /**
     * Get current locale
     */
    public function getLocale(): string
    {
        return $this->locale;
    }

    /**
     * Get available locales
     */
    public function getAvailableLocales(): array
    {
        return [
            'en' => 'English',
            'ru' => 'Русский',
        ];
    }

    /**
     * Get translation by dot-notation key
     * Example: __('menu.dashboard') loads lang/{locale}/menu.php and returns 'dashboard' key
     */
    public function get(string $key, array $replace = []): string
    {
        $parts = explode('.', $key, 2);
        if (count($parts) < 2) {
            return $key;
        }

        [$file, $item] = $parts;

        // Load file if not cached
        $this->loadFile($file, $this->locale);

        $value = $this->translations[$this->locale][$file][$item] ?? null;

        // Fallback to default locale
        if ($value === null && $this->locale !== $this->fallback) {
            $this->loadFile($file, $this->fallback);
            $value = $this->translations[$this->fallback][$file][$item] ?? $key;
        }

        if ($value === null) {
            return $key;
        }

        // Replace placeholders :name
        foreach ($replace as $k => $v) {
            $value = str_replace(':' . $k, $v, $value);
        }

        return $value;
    }

    /**
     * Load a translation file
     */
    private function loadFile(string $file, string $locale): void
    {
        if (isset($this->translations[$locale][$file])) {
            return;
        }

        $path = $this->langPath . '/' . $locale . '/' . $file . '.php';
        if (file_exists($path)) {
            $this->translations[$locale][$file] = require $path;
        } else {
            $this->translations[$locale][$file] = [];
        }
    }
}

/**
 * Global translation helper
 */
function __($key, array $replace = []): string
{
    return Lang::getInstance()->get($key, $replace);
}
