<?php

namespace App\Providers;

use App\Helpers\TranslateHelper;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\ServiceProvider;

/**
 * Service provider for registering response macros.
 */
class MacroServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     *
     * @return void
     */
    public function register()
    {
        //
    }

    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function boot(): void
    {
        /**
         * Success response macro.
         *
         * @param mixed $data The response data
         * @param string $message Success message
         * @param int $statusCode HTTP status code
         * @param string|null $language Target language for translation
         * @param array $meta Additional metadata
         * @return JsonResponse
         */
        Response::macro('success', function ($data = null, $message = 'Operation successful', $statusCode = 200, $language = null, $meta = []) {
            $translatedMessage = $message;
            $translatedData = $data;

            if ($language && $language !== 'en') {
                $translatedMessage = TranslateHelper::translate($message, $language);

                if ($data && is_array($data)) {
                    // Define fields that should be translated in response data
                    $fieldsToTranslate = ['message', 'title', 'description', 'name', 'remark', 'remarks'];
                    $translatedData = TranslateHelper::translateData($data, $language, $fieldsToTranslate);
                }
            }

            $response = [
                'success' => true,
                'message' => $translatedMessage,
                'data' => $translatedData,
            ];

            // Add pagination metadata if present
            if (isset($meta['pagination'])) {
                $response['meta'] = $meta['pagination'];
            }

            // Add per_page and perpage metadata
            if (isset($meta['per_page'])) {
                $response['meta']['per_page'] = $meta['per_page'];
            }

            if (isset($meta['perpage'])) {
                $response['meta']['perpage'] = $meta['perpage'];
            }

            // Add any additional metadata
            if (!empty($meta) && !isset($meta['pagination'])) {
                $response['meta'] = array_merge($response['meta'] ?? [], $meta);
            }

            return Response::json($response, $statusCode);
        });

        /**
         * Error response macro.
         *
         * @param string $message Error message
         * @param mixed $errors Validation errors or additional error data
         * @param int $statusCode HTTP status code
         * @param string|null $language Target language for translation
         * @param array $meta Additional metadata
         * @return JsonResponse
         */
        Response::macro('error', function ($message = 'An error occurred', $errors = null, $statusCode = 400, $language = null, $meta = []) {
            $translatedMessage = $message;
            $translatedErrors = $errors;

            if ($language && $language !== 'en') {
                $translatedMessage = TranslateHelper::translate($message, $language);

                if ($errors) {
                    if (is_array($errors)) {
                        $translatedErrors = TranslateHelper::translateArray($errors, $language);
                    } elseif (is_string($errors)) {
                        $translatedErrors = TranslateHelper::translate($errors, $language);
                    }
                }
            }

            $response = [
                'success' => false,
                'message' => $translatedMessage,
                'errors' => $translatedErrors,
            ];

            // Add any additional metadata
            if (!empty($meta)) {
                $response['meta'] = $meta;
            }

            return Response::json($response, $statusCode);
        });

        /**
         * Paginated response macro.
         *
         * @param mixed $data The paginated data
         * @param string $message Success message
         * @param string|null $language Target language for translation
         * @return JsonResponse
         */
        Response::macro('paginated', function ($data, $message = 'Data retrieved successfully', $language = null) {
            $meta = [];

            if (method_exists($data, 'toArray')) {
                $paginationData = $data->toArray();
                $meta['pagination'] = [
                    'current_page' => $paginationData['current_page'],
                    'last_page' => $paginationData['last_page'],
                    'per_page' => $paginationData['per_page'],
                    'total' => $paginationData['total'],
                    'from' => $paginationData['from'],
                    'to' => $paginationData['to'],
                    'path' => $paginationData['path'],
                    'first_page_url' => $paginationData['first_page_url'],
                    'last_page_url' => $paginationData['last_page_url'],
                    'next_page_url' => $paginationData['next_page_url'],
                    'prev_page_url' => $paginationData['prev_page_url'],
                ];

                // Add per_page metadata
                $meta['per_page'] = $paginationData['per_page'];
                $meta['perpage'] = $paginationData['per_page'];
            }

            return Response::macro('success')($data, $message, 200, $language, $meta);
        });
    }
}
