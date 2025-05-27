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
         * @return JsonResponse
         */
        Response::macro('success', function ($data = null, $message = 'Operation successful', $statusCode = 200, $language = null) {
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

            return Response::json([
                'success' => true,
                'message' => $translatedMessage,
                'data' => $translatedData,
            ], $statusCode);
        });

        /**
         * Error response macro.
         *
         * @param string $message Error message
         * @param mixed $errors Validation errors or additional error data
         * @param int $statusCode HTTP status code
         * @param string|null $language Target language for translation
         * @return JsonResponse
         */
        Response::macro('error', function ($message = 'An error occurred', $errors = null, $statusCode = 400, $language = null) {
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

            return Response::json([
                'success' => false,
                'message' => $translatedMessage,
                'errors' => $translatedErrors,
            ], $statusCode);
        });
    }
}
