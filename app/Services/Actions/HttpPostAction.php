<?php

namespace App\Services\Actions;

use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Http;

class HttpPostAction
{
    /**
     * @param  array{url:string,method?:string,headers?:array<string,string>,body?:array|string,timeout?:int}  $config
     * @param  array  $payload  // event payload from the trigger
     */
    public function __invoke(array $config, array $payload): array
    {
        $url = $config['url'] ?? null;
        if (!$url) {
            throw new \InvalidArgumentException('HttpPostAction requires a url');
        }

        $method   = strtoupper($config['method'] ?? 'POST');
        $headers  = $config['headers'] ?? [];
        $timeout  = (int) ($config['timeout'] ?? 10);

        // Allow templating: replace {{name}} etc. for array bodies
        $body = $config['body'] ?? [];
        $body = $this->interpolate($body, $payload);

        $request = Http::timeout($timeout)->withHeaders($headers);

        $response = match ($method) {
            'GET'    => $request->get($url, is_array($body) ? $body : []),
            'POST'   => is_array($body) ? $request->post($url, $body) : $request->withBody($body, $headers['Content-Type'] ?? 'application/json')->post($url),
            'PUT'    => is_array($body) ? $request->put($url, $body)  : $request->withBody($body, $headers['Content-Type'] ?? 'application/json')->put($url),
            'PATCH'  => is_array($body) ? $request->patch($url, $body): $request->withBody($body, $headers['Content-Type'] ?? 'application/json')->patch($url),
            'DELETE' => $request->delete($url, is_array($body) ? $body : []),
            default  => throw new \InvalidArgumentException("Unsupported HTTP method: {$method}"),
        };

        return [
            'status'  => $response->status(),
            'ok'      => $response->successful(),
            'body'    => $response->json() ?? $response->body(),
            'headers' => $response->headers(),
        ];
    }

    /** @param mixed $data */
    private function interpolate(mixed $data, array $vars): mixed
    {
        if (is_array($data)) {
            return array_map(fn ($v) => $this->interpolate($v, $vars), $data);
        }
        if (!is_string($data)) return $data;

        return preg_replace_callback('/{{\s*([\w\.\-]+)\s*}}/', function ($m) use ($vars) {
            return (string) Arr::get($vars, $m[1], $m[0]);
        }, $data);
    }
}
