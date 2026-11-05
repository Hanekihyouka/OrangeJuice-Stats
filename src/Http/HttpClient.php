<?php

declare(strict_types=1);

namespace OJStats\Http;

/**
 * Minimal curl based HTTP GET client with support for parallel requests.
 */
final class HttpClient
{
    private const USER_AGENT = 'OrangeJuice-Stats (+https://github.com/Hanekihyouka/OrangeJuice-Stats)';

    public function __construct(
        private readonly int $timeout = 15,
        private readonly int $concurrency = 8,
    ) {
    }

    public function get(string $url): HttpResponse
    {
        return $this->getMany([$url])[0];
    }

    /**
     * Fetch several URLs in parallel (at most $concurrency at a time).
     *
     * @param list<string> $urls
     * @return list<HttpResponse> responses in the same order as $urls
     */
    public function getMany(array $urls): array
    {
        $urls = array_values($urls);
        $responses = [];
        $multi = curl_multi_init();
        $pending = array_keys($urls);
        $active = [];

        try {
            while ($pending !== [] || $active !== []) {
                while ($pending !== [] && count($active) < $this->concurrency) {
                    $index = array_shift($pending);
                    $handle = $this->createHandle($urls[$index]);
                    curl_multi_add_handle($multi, $handle);
                    $active[spl_object_id($handle)] = [$index, $handle];
                }

                curl_multi_exec($multi, $running);
                if (curl_multi_select($multi, 1.0) === -1) {
                    usleep(10_000);
                }

                while ($info = curl_multi_info_read($multi)) {
                    $handle = $info['handle'];
                    [$index] = $active[spl_object_id($handle)];
                    $responses[$index] = $this->toResponse($urls[$index], $handle, $info['result']);
                    curl_multi_remove_handle($multi, $handle);
                    unset($active[spl_object_id($handle)]);
                }
            }
        } finally {
            foreach ($active as [, $handle]) {
                curl_multi_remove_handle($multi, $handle);
            }
            curl_multi_close($multi);
        }

        ksort($responses);

        return $responses;
    }

    private function createHandle(string $url): \CurlHandle
    {
        $handle = curl_init($url);
        curl_setopt_array($handle, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_MAXREDIRS => 3,
            // Connecting (DNS + TCP + TLS) is part of the total, so one setting covers both.
            CURLOPT_CONNECTTIMEOUT => $this->timeout,
            CURLOPT_TIMEOUT => $this->timeout,
            CURLOPT_USERAGENT => self::USER_AGENT,
            CURLOPT_ENCODING => '',
        ]);

        return $handle;
    }

    private function toResponse(string $url, \CurlHandle $handle, int $curlCode): HttpResponse
    {
        $body = curl_multi_getcontent($handle);

        return new HttpResponse(
            url: $url,
            status: (int) curl_getinfo($handle, CURLINFO_RESPONSE_CODE),
            body: is_string($body) ? $body : '',
            error: $curlCode === CURLE_OK ? null : $this->describeError($handle, $curlCode),
        );
    }

    /** e.g. "Resolving timed out after 15000 milliseconds (dns 15.00s, connect 0.00s, ip -)" */
    private function describeError(\CurlHandle $handle, int $curlCode): string
    {
        $message = curl_error($handle) ?: curl_strerror($curlCode);

        return sprintf(
            '%s (dns %.2fs, connect %.2fs, ip %s)',
            $message,
            curl_getinfo($handle, CURLINFO_NAMELOOKUP_TIME),
            curl_getinfo($handle, CURLINFO_CONNECT_TIME),
            curl_getinfo($handle, CURLINFO_PRIMARY_IP) ?: '-',
        );
    }
}
