<?php

namespace Url\Shortener\Service;

interface BasketProcessorInterface
{
    public function addProducts(array $products): bool;
}