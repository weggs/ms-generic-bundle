<?php

namespace Weggs\GenericBundle\Entity;

use Doctrine\Common\Collections\Criteria;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

abstract class TranslatableEntity {

    protected $translations;

    public function getTranslation(?string $locale = null): mixed
    {
        if (!$locale) {
            if (!$locale = \Locale::getDefault()) {
                throw new BadRequestHttpException('Locale is missing');
            }
        }
        $criteria = Criteria::create()->andWhere(Criteria::expr()->eq('locale', $locale));

        return $this->translations->matching($criteria)->first() ?: null;
    }

}
