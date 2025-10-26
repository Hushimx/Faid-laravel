<?php

namespace App\Exceptions;

use App\Support\ApiResponse;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
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
    ] : [];

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
