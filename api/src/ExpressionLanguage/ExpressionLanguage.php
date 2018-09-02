<?php

namespace App\ExpressionLanguage;

use Psr\Cache\CacheItemPoolInterface;
use Symfony\Component\ExpressionLanguage\ExpressionLanguage as BaseExpressionLanguage;

class ExpressionLanguage extends BaseExpressionLanguage
{

    /**
     * {@inheritdoc}
     */
    public function __construct(CacheItemPoolInterface $cache = null, $providers = [])
    {
        $providers[] = new MathExpressionLanguageProvider();
        
        parent::__construct($cache, $providers);
    }

}
