<?php

namespace Weggs\GenericBundle\Service;

use Doctrine\ORM\QueryBuilder;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

class TranslatableService
{
    public final const ALIAS_ROOT = 'locale';

    public final const DEFAULT_LOCALE_RELATION = 'translations.locale';

    private readonly array $availableLocales;
    private int $aliasIncrement = 1;
    private array $joined = [];

    public function __construct(ParameterBagInterface $parameterBag)
    {
        $this->availableLocales = $parameterBag->get('availableLocales');
    }

    public function modifyQuery(string $value, QueryBuilder $queryBuilder, array $properties, array $context)
    {
        if (!in_array($value, $this->availableLocales)) {
            throw new BadRequestHttpException(sprintf('invalid locale "%s", allowed: %s', $value, join(', ', $this->availableLocales)));
        }
        $this->joined = [];
        $this->aliasIncrement = 1;

        if (array_key_exists('attributes', $context)) {
            $fieldsRequested = $this->getFieldsForAttributes($context['attributes']);
            $properties = array_intersect($fieldsRequested, $properties);
            $properties = [];
        }
        $this->addLocalFilter($value, $queryBuilder, $properties);
    }

    /**
     * @return string[]
     */
    private function getFieldsForAttributes(array $attributes, string $prefix = ''): array
    {
        $res = [];

        foreach ($attributes as $key => $attribute) {
            if ('edges' == $key || 'node' == $key) {
                $res = array_merge($res, $this->getFieldsForAttributes($attribute, $prefix));
                continue;
            }
            $key = (empty($prefix) ? '' : $prefix . '.') . $key;
            $res[] = $key . '.' . self::DEFAULT_LOCALE_RELATION;
            if (is_array($attribute)) {
                $res = array_merge($res, $this->getFieldsForAttributes($attribute, $key));
            }
        }

        $res[] = self::DEFAULT_LOCALE_RELATION;

        return array_unique($res);
    }

    private function addLocalFilter(string $value, QueryBuilder $queryBuilder, array $properties)
    {
        foreach ($properties as $property) {
            $path = $this->joinColumns($property, $queryBuilder);
            $queryBuilder
                ->andWhere(sprintf('%s = :%s', $path, 'locale'))
                ->setParameter('locale', $value)
            ;
        }
    }

    public function joinColumns(string $path, QueryBuilder $queryBuilder)
    {
        if (array_key_exists($path, $this->joined)) {
            return $this->joined[$path];
        }
        $properties = explode('.', $path);
        $mainAlias = 'o';
        $lastAlias = $mainAlias;

        $joinedQuery = $this->getJoinedFromQueryBuilder($queryBuilder);

        while (count($properties) > 1) {
            $newAlias = self::ALIAS_ROOT . $this->aliasIncrement;
            ++$this->aliasIncrement;
            $joinName = sprintf('%s.%s', $lastAlias, array_shift($properties));
            if (array_key_exists($joinName, $joinedQuery)) {
                $lastAlias = $joinedQuery[$joinName];
            } else {
                $queryBuilder->leftJoin($joinName, $newAlias);
                $lastAlias = $newAlias;
            }
            $queryBuilder->addSelect($lastAlias);
        }
        $this->joined[$path] = $lastAlias . '.' . $properties[0];

        return $lastAlias . '.' . $properties[0];
    }

    private function getJoinedFromQueryBuilder(QueryBuilder $queryBuilder): array
    {
        $joins = $queryBuilder->getDQLParts()['join'];
        $result = [];
        foreach ($joins as $ents) {
            /* @var Join */
            foreach ($ents as $join) {
                $result[$join->getJoin()] = $join->getAlias();
            }
        }

        return $result;
    }
}
