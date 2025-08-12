<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;

abstract class Controller
{
    protected function successResponse(
        $data,
        string $message = 'OK',
        int $statusCode = 200,
        bool $success = true
    ): JsonResponse {
        return response()->json(compact('success', 'data', 'message'), $statusCode);
    }

    protected function errorResponse(
        string $message = 'Error',
        int $statusCode = 400,
        bool $success = false
    ): JsonResponse {
        return response()->json(compact('success', 'message', 'statusCode'), $statusCode);
    }

    // 200 OK
    protected function okResponse($data = null, string $message = 'Success'): JsonResponse
    {
        return $this->successResponse($data, $message, 200);
    }

    // 201 Created
    protected function createdResponse($data = null, string $message = 'Created'): JsonResponse
    {
        return $this->successResponse($data, $message, 201);
    }

    // 202 Accepted
    protected function acceptedResponse($data = null, string $message = 'Accepted'): JsonResponse
    {
        return $this->successResponse($data, $message, 202);
    }

    // 204 No Content
    protected function noContentResponse(string $message = 'No Content'): JsonResponse
    {
        return $this->successResponse(null, $message, 204);
    }

    // 400 Bad Request
    protected function badRequestResponse(string $message = 'Bad Request'): JsonResponse
    {
        return $this->errorResponse($message, 400);
    }

    // 401 Unauthorized
    protected function unauthorizedResponse(string $message = 'Unauthorized'): JsonResponse
    {
        return $this->errorResponse($message, 401);
    }

    // 403 Forbidden
    protected function forbiddenResponse(string $message = 'Forbidden'): JsonResponse
    {
        return $this->errorResponse($message, 403);
    }

    // 404 Not Found
    protected function notFoundResponse(string $message = 'Not Found'): JsonResponse
    {
        return $this->errorResponse($message, 404);
    }

    // 409 Conflict
    protected function conflictResponse(string $message = 'Conflict'): JsonResponse
    {
        return $this->errorResponse($message, 409);
    }

    // 422 Unprocessable Entity
    protected function unprocessableEntityResponse(string $message = 'Unprocessable Entity'): JsonResponse
    {
        return $this->errorResponse($message, 422);
    }

    // 429 Too Many Requests
    protected function tooManyRequestsResponse(string $message = 'Too Many Requests'): JsonResponse
    {
        return $this->errorResponse($message, 429);
    }

    // 500 Internal Server Error
    protected function serverErrorResponse(string $message = 'Internal Server Error'): JsonResponse
    {
        return $this->errorResponse($message, 500);
    }

    // 503 Service Unavailable
    protected function serviceUnavailableResponse(string $message = 'Service Unavailable'): JsonResponse
    {
        return $this->errorResponse($message, 503);
    }
}
