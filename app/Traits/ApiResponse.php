<?php

namespace App\Traits;

use Illuminate\Http\JsonResponse;

/**
 * Responds Trait
 * 
 * Provides standardized JSON response formatting across the API.
 * Think of this like a standardized envelope format - all mail from our API
 * arrives in the same packaging, making it predictable for clients.
 * 
 * Usage:
 * - success(): For successful operations
 * - error(): For failures
 * - paginated(): For list endpoints with pagination
 */
trait ApiResponse
{
    /**
     * Return a successful response
     * 
     * @param mixed $data Response payload
     * @param string $message Human-readable message
     * @param int $status HTTP status code
     */
    protected function success($data = null, string $message = 'Success', int $status = 200): JsonResponse
    {
        return response()->json([
            'success' => true,
            'status' => $status,
            'message' => $message,
            'data' => $data,
        ], $status);
    }

    /**
     * Return a created response (201)
     * 
     * @param mixed $data Response payload
     * @param string $message Human-readable message
     */
    protected function created($data = null, string $message = 'Resource created successfully'): JsonResponse
    {
        return $this->success($data, $message, 201);
    }

    /**
     * Return an error response
     * 
     * @param string $message Error message
     * @param int $status HTTP status code
     * @param mixed $errors Additional error details
     * @param string $code Error code for client handling
     */
    protected function error(
        string $message = 'An error occurred',
        int $status = 400,
        $errors = null,
        string $code = null
    ): JsonResponse {
        return response()->json([
            'success' => false,
            'status' => $status,
            'message' => $message,
            'code' => $code,
            'errors' => $errors,
        ], $status);
    }

    /**
     * Return a not found error
     * 
     * @param string $message Error message
     */
    protected function notFound(string $message = 'Resource not found'): JsonResponse
    {
        return $this->error($message, 404, null, 'NOT_FOUND');
    }

    /**
     * Return an unauthorized error
     * 
     * @param string $message Error message
     */
    protected function unauthorized(string $message = 'Unauthorized'): JsonResponse
    {
        return $this->error($message, 401, null, 'UNAUTHORIZED');
    }

    /**
     * Return a forbidden error
     * 
     * @param string $message Error message
     */
    protected function forbidden(string $message = 'Forbidden'): JsonResponse
    {
        return $this->error($message, 403, null, 'FORBIDDEN');
    }

    /**
     * Return a validation error
     * 
     * @param array $errors Validation errors
     * @param string $message Error message
     */
    protected function validationError(array $errors, string $message = 'Validation failed'): JsonResponse
    {
        return $this->error($message, 422, $errors, 'VALIDATION_ERROR');
    }

    /**
     * Return a paginated response
     * 
     * @param \Illuminate\Pagination\LengthAwarePaginator $paginator
     * @param string $message Success message
     */
    protected function paginated($paginator, string $message = 'Success'): JsonResponse
    {
        return response()->json([
            'success' => true,
            'status' => 200,
            'message' => $message,
            'data' => $paginator->items(),
            'pagination' => [
                'total' => $paginator->total(),
                'per_page' => $paginator->perPage(),
                'current_page' => $paginator->currentPage(),
                'last_page' => $paginator->lastPage(),
                'from' => $paginator->firstItem(),
                'to' => $paginator->lastItem(),
            ],
        ], 200);
    }

    /**
     * Return a server error
     * 
     * @param string $message Error message
     */
    protected function serverError(string $message = 'Internal server error'): JsonResponse
    {
        return $this->error($message, 500, null, 'SERVER_ERROR');
    }

    /**
     * Return a conflict error (duplicate resource, etc)
     * 
     * @param string $message Error message
     */
    protected function conflict(string $message = 'Conflict'): JsonResponse
    {
        return $this->error($message, 409, null, 'CONFLICT');
    }
}