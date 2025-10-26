<?php

namespace App\Support;

use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Http\JsonResponse;
use Illuminate\Pagination\LengthAwarePaginator;

class ApiResponse
{
  public static function success(
    mixed $data = null,
    string $message = 'Success',
    int $code = 200,
    array $meta = []
  ): JsonResponse {
    return self::respond([
      'success' => true,
      'message' => $message,
      'data'    => self::toArray($data),
      'meta'    => empty($meta) ? null : (object) $meta,
    ], $code);
  }

  public static function error(
    string $message = 'Error',
    array $errors = [],
    int $code = 400,
    array $meta = []
  ): JsonResponse {
    return self::respond([
      'success' => false,
      'message' => $message,
      'errors'  => $errors,
      'meta'    => empty($meta) ? null : (object) $meta,
    ], $code);
  }


  public static function paginated(
    LengthAwarePaginator $paginator,
    string $message = 'Success',
    int $code = 200,
    array $extraMeta = []
  ): JsonResponse {
    $meta = array_merge([
      'current_page'  => $paginator->currentPage(),
      'per_page'      => $paginator->perPage(),
      'total'         => $paginator->total(),
      'last_page'     => $paginator->lastPage(),
      'from'          => $paginator->firstItem(),
      'to'            => $paginator->lastItem(),
      'has_more'      => $paginator->hasMorePages(),
      'prev_page_url' => $paginator->previousPageUrl(),
      'next_page_url' => $paginator->nextPageUrl(),
    ], $extraMeta);

    // Get the items for the current page
    $items = $paginator->items();

    return self::success($items, $message, $code, $meta);
  }

  /* ----------------- Helpers ----------------- */

  protected static function toArray(mixed $data): mixed
  {
    if ($data instanceof Arrayable) return $data->toArray();
    if ($data instanceof \JsonSerializable) return $data->jsonSerialize();
    return $data;
  }

  protected static function respond(array $payload, int $code): JsonResponse
  {
    // Remove null values from the payload
    $payload = array_filter($payload, fn($v) => !is_null($v));
    return response()->json($payload, $code);
  }
}
