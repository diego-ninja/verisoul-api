<?php

namespace Ninja\Verisoul\Contracts;

use Ninja\Verisoul\Exceptions\VerisoulApiException;
use Ninja\Verisoul\Exceptions\VerisoulConnectionException;

interface HttpClientInterface
{
    /**
     * Make HTTP GET request
     *
     * @throws VerisoulConnectionException
     * @throws VerisoulApiException
     */
    public function get(string $url, array $query = [], array $headers = []): array;

    /**
     * Make HTTP POST request
     *
     * @throws VerisoulConnectionException
     * @throws VerisoulApiException
     */
    public function post(string $url, array $data = [], array $headers = []): array;

    /**
     * Make HTTP PUT request
     *
     * @throws VerisoulConnectionException
     * @throws VerisoulApiException
     */
    public function put(string $url, array $data = [], array $headers = []): array;

    /**
     * Make HTTP DELETE request
     *
     * @throws VerisoulConnectionException
     * @throws VerisoulApiException
     */
    public function delete(string $url, array $data = [], array $headers = []): array;

    /**
     * Set timeout for requests
     */
    public function setTimeout(int $timeout): self;

    /**
     * Set connection timeout for requests
     */
    public function setConnectTimeout(int $connectTimeout): self;

    /**
     * Set default headers for all requests
     */
    public function setHeaders(array $headers): self;
}