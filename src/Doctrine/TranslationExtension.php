<?php

namespace Weggs\GenericBundle\Doctrine;

use ApiPlatform\Core\Annotation\ApiFilter;
use ApiPlatform\Core\Bridge\Doctrine\Orm\Extension\QueryCollectionExtensionInterface;
use ApiPlatform\Core\Bridge\Doctrine\Orm\Extension\QueryItemExtensionInterface;
use ApiPlatform\Core\Bridge\Doctrine\Orm\Util\QueryNameGeneratorInterface;
use Weggs\GenericBundle\Service\TranslatableService;
use Doctrine\ORM\QueryBuilder;
use ReflectionClass;
use Weggs\GenericBundle\Filter\LocaleFilter;
use Symfony\Component\HttpFoundation\RequestStack;

final class TranslationExtension implements QueryItemExtensionInterface, QueryCollectionExtensionInterface
{
    public function __construct(private readonly TranslatableService $translatableService, private readonly RequestStack $request)
    {
    }

    public function applyToItem(
        QueryBuilder $queryBuilder,
        QueryNameGeneratorInterface $queryNameGenerator,
        string $resourceClass,
        array $identifiers,
        string $operationName = null,
        array $context = []
    ): void {
        if (!array_key_exists('filters', $context) || !array_key_exists('locale', $context['filters'])) {
            if (!$this->request->getMainRequest()->get('locale')) {
                return;
            }
            $context['filters']['locale'] = $this->request->getMainRequest()->get('locale');
        }
        $properties = $this->getLocalfilterProperties($resourceClass);
        \Locale::setDefault($context['filters']['locale']);
        if (!$properties) {
            return;
        }

        $this->translatableService->modifyQuery($context['filters']['locale'], $queryBuilder, $properties, $context);
    }

    public function applyToCollection(QueryBuilder $queryBuilder, QueryNameGeneratorInterface $queryNameGenerator, string $resourceClass, string $operationName = null, array $context = []): void
    {

        if (array_key_exists('filters', $context) && array_key_exists('locale', $context['filters'])) {
            return;
        }
        if (!$this->request->getMainRequest()->get('locale')) {
            return;
        }
        $properties = $this->getLocalfilterProperties($resourceClass);

        \Locale::setDefault($this->request->getMainRequest()->get('locale'));
        if (!$properties) {
            return;
        }
        $this->translatableService->modifyQuery($this->request->getMainRequest()->get('locale'), $queryBuilder, $properties, $context);
    }

    public function getLocalfilterProperties(string $resourceClass): ?array
    {
        $reflectionClass = new ReflectionClass($resourceClass);
        $attrs = $reflectionClass->getAttributes(ApiFilter::class);
        foreach ($attrs as $attr) {
            if (LocaleFilter::class == $attr->getArguments()[0]) {
                return $attr->getArguments()['properties'];
            }
        }

        return null;
    }
}
