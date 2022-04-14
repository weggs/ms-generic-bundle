<?php

namespace Weggs\GenericBundle\Filter;

use ApiPlatform\Core\Bridge\Doctrine\Orm\Filter\AbstractContextAwareFilter;
use ApiPlatform\Core\Bridge\Doctrine\Orm\Util\QueryNameGeneratorInterface;
use Weggs\GenericBundle\Service\TranslatableService;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;
use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Serializer\NameConverter\NameConverterInterface;
use Symfony\Component\Serializer\SerializerInterface;

final class LocaleFilter extends AbstractContextAwareFilter
{
    public final const FILTER_NAME = 'locale';

    private readonly array $availableLocales;

    public function __construct(
        ManagerRegistry $managerRegistry,
        ParameterBagInterface $parameterBag,
        private readonly TranslatableService $translatableService,
        private readonly SerializerInterface $serializer,
        ?RequestStack $requestStack = null,
        LoggerInterface $logger = null,
        array $properties = null,
        NameConverterInterface $nameConverter = null,
    ) {
        parent::__construct($managerRegistry, $requestStack, $logger, $properties, $nameConverter);
        $this->availableLocales = $parameterBag->get('availableLocales');
    }

    protected function filterProperty(string $property, $value, QueryBuilder $queryBuilder, QueryNameGeneratorInterface $queryNameGenerator, string $resourceClass, string $operationName = null, array $context = [])
    {
        if (self::FILTER_NAME != $property) {
            return;
        }

        \Locale::setDefault($value);

        $this->translatableService->modifyQuery($value, $queryBuilder, array_keys($this->properties), $context);
    }

    public function getDescription(string $resourceClass): array
    {
        $description = [];
        if (!$this->properties) {
            return [];
        }
        $description['locale'] = [
            'type' => 'string',
            'required' => false,
            'description' => 'to get only the requested language',
            'schema' => [
                'type' => 'string',
                'enum' => $this->availableLocales,
            ],
            'property' => 'locale',
        ];

        return $description;
    }
}
