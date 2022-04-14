<?php
namespace Weggs\GenericBundle;

use Symfony\Bundle\FrameworkBundle\FrameworkBundle;
use Symfony\Component\HttpKernel\Bundle\Bundle;
use Symfony\Component\HttpKernel\KernelInterface;

/**
 * GenericBundle
 */
class WeggsGenericBundle extends Bundle
{
    public static function getBundleDependencies(KernelInterface $kernel)
    {
        return [
            FrameworkBundle::class,
        ];
    }
}
