# Weggs symfon generic bundle

## Documentation
- [Installation](#installation)
- [Abstract request service](#Abstract-request-service)
    - [Configuration](#configuration)
    - [Usage](#usage)

## Installation
add the vcs source in the composer.json
```json
    "repositories": [
        {"type": "vcs", "url": "https://github.com/weggs/ms-generic-bundle"}
    ]
```

install the package

```
composer require weggs/ms-generic-bundle:dev-master
```
## Abstract request service

### Configuration

Create config file `config/packages/weggs_generic.yaml` if not exist and add the following params:

```yaml
weggs_generic:
  abstract_request:
    keycloak_base_url: '%env(KEYCLOAK_BASE_URL)%'
    keycloak_client_id: '%env(KEYCLOAK_CLIENT_ID)%'
    keycloak_client_secret: '%env(KEYCLOAK_CLIENT_SECRET)%'
    redis_url: '%env(REDIS_URL)%'
    redis_token_key: 'microservice.keycloak.token'
```

add the environement variable you need exemple:
```
KEYCLOAK_BASE_URL="http://dockerize_keycloak:8080/auth/realms/master/"
KEYCLOAK_CLIENT_ID="my_project-connect"
KEYCLOAK_CLIENT_SECRET="e3842784-eafa-4276-a452-ace301a05f38"

REDIS_URL="redis://dockerize_redis:6379/mymicroservice"
```

### Usage

- create your symfony http client (in `config/packages/framework.yaml`)
```yaml
framework:
    http_client:
        scoped_clients:
            my.client:
                base_uri: '%env(MY_MS_URI)%' # put it in your env
                headers:
                    Accept: 'application/json'
```

- create your service exemple: `src/service/MyRequestService.php`

```php
<?php

namespace App\Service;

use Weggs\GenericBundle\Service\AbstractRequestService;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class MyRequestService extends AbstractRequestService
{

    public function __construct(HttpClientInterface $myClient) {
        parent::__construct($myClient);
    }

    public function myRequest(): array
    {
        $results = $this->authenticatedRequest('GET', 'route/', [
            'query' => [
                'mydata' => 'toto'
            ]
        ]);
        
        return $results;
    }
}

```

- declare the service

in the `services.yaml`

```yaml
services:
    App\Service\MyRequestService:
        parent: Weggs\GenericBundle\Service\AbstractRequestService
```
