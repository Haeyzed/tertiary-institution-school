<?php

namespace App\Http\Requests;

use App\Helpers\TranslateHelper;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

/**
 * Base request class for all form requests.
 *
 * Provides common functionality and structure for all request classes.
 */
abstract class BaseRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    abstract public function rules(): array;

    /**
     * Get custom messages for validator errors.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [];
    }

    /**
     * Get custom attributes for validator errors.
     *
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [];
    }

    /**
     * Get the validated data from the request with optional translation.
     *
     * @param array|null $key
     * @param mixed $default
     * @param string|null $language
     * @return array|mixed
     */
    public function validated($key = null, $default = null, $language = null): mixed
    {
        $validated = parent::validated($key, $default);

        if ($language && $language !== 'en' && is_array($validated)) {
            // Define fields that should be translated
            $fieldsToTranslate = ['message', 'title', 'description', 'name', 'remark', 'remarks'];
            $validated = TranslateHelper::translateData($validated, $language, $fieldsToTranslate);
        }

        return $validated;
    }

    /**
     * Prepare the data for validation.
     *
     * @return void
     */
    protected function prepareForValidation(): void
    {
        //
    }

    /**
     * Handle a failed validation attempt.
     *
     * @param Validator $validator
     * @return void
     *
     * @throws HttpResponseException
     */
    protected function failedValidation(Validator $validator): void
    {
        $language = $this->header('Accept-Language', 'en');
        $language = $this->extractLanguageCode($language);

        $errors = $validator->errors()->toArray();
        $message = 'Validation failed';

        // Translate error messages if language is not English
        if ($language && $language !== 'en') {
            $message = TranslateHelper::translate($message, $language);
            $errors = $this->translateValidationErrors($errors, $language);
        }

        throw new HttpResponseException(
            response()->error($message, $errors, 422, $language)
        );
    }

    /**
     * Extract language code from Accept-Language header.
     *
     * @param string $acceptLanguage
     * @return string
     */
    private function extractLanguageCode(string $acceptLanguage): string
    {
        // Extract the primary language code (e.g., 'en' from 'en-US,en;q=0.9')
        $languages = explode(',', $acceptLanguage);
        $primaryLanguage = explode('-', explode(';', $languages[0])[0])[0];

        return strtolower(trim($primaryLanguage));
    }

    /**
     * Translate validation error messages.
     *
     * @param array $errors
     * @param string $language
     * @return array
     */
    private function translateValidationErrors(array $errors, string $language): array
    {
        $translatedErrors = [];

        foreach ($errors as $field => $messages) {
            $translatedErrors[$field] = [];
            foreach ($messages as $message) {
                $translatedErrors[$field][] = TranslateHelper::translate($message, $language);
            }
        }

        return $translatedErrors;
    }
}
