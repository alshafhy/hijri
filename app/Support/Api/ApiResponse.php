<?php

declare(strict_types=1);

namespace App\Support\Api;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Http\Resources\Json\ResourceCollection;
use Illuminate\Pagination\LengthAwarePaginator;

final class ApiResponse
{
    /**
     * @param  array<string, mixed>  $meta
     * @param  array<int|string, mixed>  $errors
     */
    public static function success(
        mixed $data = null,
        array $meta = [],
        int $status = 200,
        array $errors = [],
    ): JsonResponse {
        return response()->json([
            'data' => $data,
            'meta' => (object) $meta,
            'errors' => array_values($errors) === $errors ? $errors : (object) $errors,
        ], $status);
    }

    /**
     * @param  array<string, mixed>  $meta
     */
    public static function resource(
        JsonResource $resource,
        array $meta = [],
        int $status = 200,
    ): JsonResponse {
        $payload = $resource->resolve();

        return self::success($payload, $meta, $status);
    }

    /**
     * @param  array<string, mixed>  $meta
     */
    public static function collection(
        ResourceCollection $collection,
        array $meta = [],
        int $status = 200,
    ): JsonResponse {
        $response = $collection->response()->getData(true);
        $data = $response['data'] ?? $response;
        $paginationMeta = [];

        if (isset($response['meta'])) {
            $paginationMeta = $response['meta'];
        } elseif (isset($response['links'])) {
            $paginationMeta['links'] = $response['links'];
        }

        return self::success($data, array_merge($paginationMeta, $meta), $status);
    }

    /**
     * @param  array<int|string, mixed>  $errors
     * @param  array<string, mixed>  $meta
     */
    public static function error(
        string $message,
        array $errors = [],
        int $status = 400,
        array $meta = [],
    ): JsonResponse {
        $errorPayload = $errors === []
            ? [['message' => $message]]
            : $errors;

        return response()->json([
            'data' => null,
            'meta' => (object) array_merge(['message' => $message], $meta),
            'errors' => $errorPayload,
        ], $status);
    }

    /**
     * @param  LengthAwarePaginator<int, mixed>  $paginator
     * @return array<string, mixed>
     */
    public static function paginationMeta(LengthAwarePaginator $paginator): array
    {
        return [
            'current_page' => $paginator->currentPage(),
            'per_page' => $paginator->perPage(),
            'total' => $paginator->total(),
            'last_page' => $paginator->lastPage(),
        ];
    }
}
