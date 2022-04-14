<?php

namespace Weggs\GenericBundle\Service;

use Symfony\Component\Cache\Adapter\RedisAdapter;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class TokenProviderService
{

    private string $token;
    private ?RedisAdapter $redis = null;

    public function __construct(
        private readonly RequestStack $requestStack,
        private HttpClientInterface $keycloakClient,
        string $keycloakBaseUrl,
        private readonly string $keycloakClientId,
        private readonly string $keycloakClientSecret,
        public string $redisUrl,
        private string $redisKey
    ) {
        $this->keycloakClient = $this->keycloakClient->withOptions([
            'base_uri' => $keycloakBaseUrl,
            'headers' => ['Accept' => 'application/json']
        ]);
    }

    public function getToken(): string
    {
        $this->connectRedis();
        $token = '';
        if ($this->requestStack->getCurrentRequest()) {
            $request = $this->requestStack->getCurrentRequest();
            $token = $request->headers->get('Authorization');

            if (!empty($token)) {
                return str_replace('Bearer ', '', $token);
            }
        }

        if (!empty($this->$token)) {
            return $this->$token;
        }

        $this->token = $this->redis->get($this->redisKey, [$this, 'requestToken']);

        return $this->token;
    }

    public function refreshToken(): string
    {
        $this->connectRedis();

        $this->redis->delete($this->redisKey);
        $this->token = $this->redis->get($this->redisKey, [$this, 'requestToken']);

        return $this->token;
    }

    public function requestToken(): string
    {
        $request = $this->keycloakClient->request('POST', 'protocol/openid-connect/token/', [
            'body' => [
                'grant_type' => 'client_credentials',
                'client_id' => $this->keycloakClientId,
                'client_secret' => $this->keycloakClientSecret,
            ],
        ]);

        return json_decode($request->getContent(), true, 512, JSON_THROW_ON_ERROR)['access_token'];
    }

    private function connectRedis()
    {
        if (!$this->redis) {
            $this->redis = new RedisAdapter(RedisAdapter::createConnection($this->redisUrl));
        }
    }
}
