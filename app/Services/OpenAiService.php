<?php

namespace App\Services;

use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class OpenAiService
{
    public function complete(array $messages): array
    {
        $model = config('services.openai.model', 'gpt-4.1-mini');
        $response = $this->request('/responses', [
            'model' => $model,
            'input' => $messages,
        ], 75);

        $body = $response->json();
        $content = data_get($body, 'output_text');

        if (! is_string($content) || trim($content) === '') {
            $content = collect(data_get($body, 'output', []))
                ->where('type', 'message')
                ->flatMap(fn (array $item) => $item['content'] ?? [])
                ->where('type', 'output_text')
                ->pluck('text')
                ->filter(fn ($text) => is_string($text) && trim($text) !== '')
                ->implode("\n");
        }

        if (! is_string($content) || trim($content) === '') {
            Log::warning('OpenAI Responses API result did not contain assistant text.');
            throw new OpenAiException('The AI service returned an empty response. Please try again.');
        }

        return [
            'content' => $content,
            'model' => data_get($body, 'model', $model),
            'usage' => data_get($body, 'usage'),
        ];
    }

    public function generateImage(string $prompt, string $size): string
    {
        $response = $this->request('/images/generations', [
            'model' => config('services.openai.image_model', 'gpt-image-1'),
            'prompt' => $prompt,
            'size' => $size,
            'n' => 1,
        ], 120);

        $encodedImage = data_get($response->json(), 'data.0.b64_json');
        $image = is_string($encodedImage) ? base64_decode($encodedImage, true) : false;

        if (! is_string($image) || $image === '') {
            Log::warning('OpenAI image response did not contain a valid base64 image.');

            throw new OpenAiException('The image service returned an invalid image. Please try again.');
        }

        return $image;
    }

    private function request(string $endpoint, array $payload, int $timeout): Response
    {
        $apiKey = config('services.openai.api_key');

        if (! is_string($apiKey) || trim($apiKey) === '') {
            throw new OpenAiException(
                'OpenAI API key is not configured. Add it to the project-root .env file as OPENAI_API_KEY=your_new_key_here, then run php artisan config:clear and restart php artisan serve from the project directory. Put the real key only in .env, never in chat or source code.',
                503
            );
        }

        try {
            $response = Http::acceptJson()
                ->asJson()
                ->withToken($apiKey)
                ->timeout($timeout)
                ->post('https://api.openai.com/v1'.$endpoint, $payload);
        } catch (ConnectionException $exception) {
            Log::warning('OpenAI request could not connect.', [
                'endpoint' => $endpoint,
                'exception' => $exception::class,
            ]);

            throw new OpenAiException(
                'Unable to connect to the AI service. Please try again.',
                503,
                $exception
            );
        }

        if ($response->failed()) {
            $body = $response->json();
            $errorCode = data_get($body, 'error.code');
            $errorType = data_get($body, 'error.type');
            $status = $response->status();

            Log::warning('OpenAI request failed.', [
                'endpoint' => $endpoint,
                'status' => $status,
                'error_code' => is_string($errorCode) ? $errorCode : null,
                'error_type' => is_string($errorType) ? $errorType : null,
            ]);

            throw $this->providerException($status, $errorCode, $errorType);
        }

        return $response;
    }

    private function providerException(int $status, mixed $errorCode, mixed $errorType): OpenAiException
    {
        if ($status === 401 || $errorCode === 'invalid_api_key') {
            return new OpenAiException('OpenAI authentication failed. Please verify your API key.', 502);
        }

        if (in_array($errorCode, ['insufficient_quota', 'billing_hard_limit_reached'], true)
            || $errorType === 'insufficient_quota') {
            return new OpenAiException('Your OpenAI API account has insufficient available quota.', 503);
        }

        if ($status === 429 || $errorCode === 'rate_limit_exceeded') {
            return new OpenAiException('Too many AI requests. Please try again shortly.', 429);
        }

        if ($status === 400 || $status === 422) {
            return new OpenAiException('The AI request could not be processed. Check your message and try again.', 422);
        }

        return new OpenAiException('The AI service could not complete this request. Please try again shortly.', 502);
    }
}
