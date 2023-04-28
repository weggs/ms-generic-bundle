<?php

namespace Weggs\GenericBundle\Service;

use Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Contracts\HttpClient\ResponseInterface;

abstract class AbstractRequestService
{
    private TokenProviderService $tokenProviderService;
    private ?ResponseInterface $lastAuthenticatedRequest = null;
    private bool $disableHttp = false;

    public function __construct(private readonly HttpClientInterface $httpClient)
    {
    }

    public function setTokenProviderService(TokenProviderService $tokenProviderService)
    {
        $this->tokenProviderService = $tokenProviderService;
    }

    public function setParameter($disableHttp)
    {
        $this->disableHttp = $disableHttp === 'true' || $disableHttp === true;
    }

    public function authenticatedRequest(string $method, string $url, array $options = []): ?array
    {
        if ($this->disableHttp) {
            return [];
        }

        if (!array_key_exists('headers', $options)) {
            $options['headers'] = [];
        }

        $options['headers'] = array_merge([
            'Authorization' => 'Bearer '.$this->tokenProviderService->getToken(),
        ], $options['headers']);

        try {
            $this->lastAuthenticatedRequest = $this->httpClient->request($method, $url, $options);
            if (204 === $this->lastAuthenticatedRequest->getStatusCode()) {
                return [];
            }

            return json_decode($this->lastAuthenticatedRequest->getContent(), true, 512, JSON_THROW_ON_ERROR);
        } catch (ClientExceptionInterface) {
            $options['headers']['Authorization'] = 'Bearer '.$this->tokenProviderService->refreshToken();
            $this->lastAuthenticatedRequest = $this->httpClient->request($method, $url, $options);

            return json_decode($this->lastAuthenticatedRequest->getContent(false), true, 512, JSON_THROW_ON_ERROR);
        }
    }

    public function getLastAuthenticatedRequest() {
        return $this->lastAuthenticatedRequest;
    }
}
