<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Symfony\Component\HttpFoundation\Response;

class SetApiLocale
{
  /**
   * Cache key for supported locales.
   */
  protected const CACHE_KEY = 'api_supported_locales';

  /**
   * Cache duration in seconds (1 hour).
   */
  protected const CACHE_DURATION = 3600;

  /**
   * Handle an incoming request.
   *
   * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
   */
  public function handle(Request $request, Closure $next): Response
  {
    $locale = $this->getLocaleFromRequest($request);

    // Set application locale
    app()->setLocale($locale);

    // Get response and add locale header for debugging
    $response = $next($request);
    $response->headers->set('X-Locale', $locale);

    return $response;
  }

  /**
   * Get locale from request headers or query parameter.
   *
   * Priority:
   * 1. X-Locale header (custom header)
   * 2. Accept-Language header (standard header)
   * 3. locale query parameter
   * 4. Default app locale
   */
  protected function getLocaleFromRequest(Request $request): string
  {
    // 1. Check custom X-Locale header (highest priority)
    if ($request->hasHeader('X-Locale')) {
      $locale = strtolower(trim($request->header('X-Locale')));
      if ($this->isValidLocale($locale)) {
        return $locale;
      }
    }

    // 2. Check Accept-Language header
    if ($request->hasHeader('Accept-Language')) {
      $locale = $this->parseAcceptLanguage($request->header('Accept-Language'));
      if ($locale && $this->isValidLocale($locale)) {
        return $locale;
      }
    }

    // 3. Check locale query parameter
    $localeParam = $request->query('locale');
    if ($localeParam) {
      $locale = strtolower(trim($localeParam));
      if ($this->isValidLocale($locale)) {
        return $locale;
      }
    }

    // 4. Default to app locale
    return config('app.locale', 'en');
  }

  /**
   * Parse Accept-Language header to extract preferred locale.
   *
   * Example: "en-US,en;q=0.9,ar;q=0.8" -> "en"
   * Handles quality values and prioritizes languages by q-value.
   */
  protected function parseAcceptLanguage(string $acceptLanguage): ?string
  {
    if (empty($acceptLanguage)) {
      return null;
    }

    // Parse languages with quality values
    $languages = [];
    foreach (explode(',', $acceptLanguage) as $lang) {
      $parts = explode(';q=', trim($lang));
      $locale = strtolower(trim($parts[0]));
      $quality = isset($parts[1]) ? (float) $parts[1] : 1.0;

      // Extract base locale (en-US -> en)
      $baseLocale = explode('-', $locale)[0];

      $languages[] = [
        'locale' => $locale,
        'base' => $baseLocale,
        'quality' => $quality,
      ];
    }

    // Sort by quality (highest first)
    usort($languages, fn($a, $b) => $b['quality'] <=> $a['quality']);

    // Check full locale first, then base locale
    foreach ($languages as $lang) {
      if ($this->isValidLocale($lang['locale'])) {
        return $lang['locale'];
      }
      if ($this->isValidLocale($lang['base'])) {
        return $lang['base'];
      }
    }

    return null;
  }

  /**
   * Check if locale is valid/supported.
   */
  protected function isValidLocale(string $locale): bool
  {
    if (empty($locale)) {
      return false;
    }

    $locale = strtolower(trim($locale));
    $supportedLocales = $this->getSupportedLocales();

    return in_array($locale, $supportedLocales, true);
  }

  /**
   * Get supported locales from database with caching.
   *
   * @return array<string>
   */
  protected function getSupportedLocales(): array
  {
    return Cache::remember(self::CACHE_KEY, self::CACHE_DURATION, function () {
      try {
        if (!function_exists('locales')) {
          return $this->getDefaultLocales();
        }

        $locales = locales();

        if (empty($locales) || !is_array($locales)) {
          return $this->getDefaultLocales();
        }

        $codes = [];
        foreach ($locales as $locale) {
          // Handle both object and array formats
          $code = is_object($locale) ? ($locale->code ?? null) : ($locale['code'] ?? null);

          if ($code && is_string($code)) {
            $codes[] = strtolower(trim($code));
          }
        }

        // Remove duplicates and return
        return array_values(array_unique(array_filter($codes))) ?: $this->getDefaultLocales();
      } catch (\Throwable $e) {
        return $this->getDefaultLocales();
      }
    });
  }

  /**
   * Get default supported locales.
   *
   * @return array<string>
   */
  protected function getDefaultLocales(): array
  {
    return ['en', 'ar'];
  }
}
