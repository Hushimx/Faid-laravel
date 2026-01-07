<?php

namespace App\Exceptions;

use App\Support\ApiResponse;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\TooManyRequestsHttpException;
use Throwable;

class ApiExceptionHandler
{
  /**
   * Handle exceptions for API routes
   */
  public function handle(Throwable $e, Request $request): JsonResponse
  {
    // Authentication Exceptions (401)
    if ($e instanceof AuthenticationException) {
      return ApiResponse::error(
        'Unauthorized access',
        [],
        401
      );
    }

    // Validation Exceptions (422)
    if ($e instanceof ValidationException) {
      return ApiResponse::error(
        'Validation failed',
        $e->errors(),
        422
      );
    }

    // Rate Limiting / Throttle Exceptions (429)
    if ($e instanceof TooManyRequestsHttpException) {
      $headers = $e->getHeaders();
      $retryAfter = $headers['Retry-After'] ?? $headers['retry-after'] ?? null;
      $message = 'Too many requests. Please try again later.';
      
      if ($retryAfter) {
        $message .= " Retry after {$retryAfter} seconds.";
      }
      
      $errors = [];
      if ($retryAfter) {
        $errors['retry_after'] = [(string) $retryAfter];
      }
      
      return ApiResponse::error(
        $message,
        $errors,
        429
      );
    }

    // Check if it's a throttle exception by class name (for Laravel's internal throttle)
    $exceptionClass = get_class($e);
    if (str_contains($exceptionClass, 'Throttle') || str_contains($exceptionClass, 'TooManyRequests')) {
      $message = 'Too many requests. Please try again later.';
      $retryAfter = null;
      
      // Try to extract retry-after from headers if available
      if (method_exists($e, 'getHeaders')) {
        $headers = $e->getHeaders();
        $retryAfter = $headers['Retry-After'] ?? $headers['retry-after'] ?? null;
        if ($retryAfter) {
          $message .= " Retry after {$retryAfter} seconds.";
        }
      }
      
      $errors = [];
      if ($retryAfter) {
        $errors['retry_after'] = [(string) $retryAfter];
      }
      
      return ApiResponse::error(
        $message,
        $errors,
        429
      );
    }

    // Model Not Found (404)
    if ($e instanceof ModelNotFoundException) {
      return ApiResponse::error(
        'Resource not found',
        [],
        404
      );
    }

    // Route Not Found (404)
    if ($e instanceof NotFoundHttpException) {
      return ApiResponse::error(
        'Route Not found',
        [],
        404
      );
    }

    // All other exceptions (500)
    $message = config('app.debug')
      ? $e->getMessage()
      : 'An unexpected error occurred';

    $details = config('app.debug') ? [
      'message' => $e->getMessage(),
      'file' => $e->getFile(),
      'line' => $e->getLine(),
      'exception_class' => get_class($e),
    ] : [];

    // Log the error for debugging
    \Log::error('Unhandled API exception', [
      'exception' => get_class($e),
      'message' => $e->getMessage(),
      'file' => $e->getFile(),
      'line' => $e->getLine(),
      'trace' => $e->getTraceAsString(),
    ]);

    return ApiResponse::error(
      'Server Error',
      $details,
      500
    );
  }

  /**
   * Determine if the exception should be handled as JSON
   */
  public function shouldHandle(Request $request): bool
  {
    return $request->is('api/*') || $request->expectsJson();
  }
}
