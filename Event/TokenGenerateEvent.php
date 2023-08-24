<?php

namespace MauticPlugin\MauticCitrixBundle\Event;

use Mautic\CoreBundle\Event\CommonEvent;

class TokenGenerateEvent extends CommonEvent
{
    public function __construct(private array $params)
    {
    }

    /**
     * @return array<string>
     */
    public function getParams(): array
    {
        return $this->params;
    }

    /**
     * @param array<string> $params
     */
    protected function setParams(array $params): void
    {
        $this->params = $params;
    }

    public function getProduct(): string
    {
        return array_key_exists('product', $this->params) ? $this->params['product'] : '';
    }

    public function setProduct(string $product): void
    {
        $this->params['product'] = $product;
    }

    public function getProductLink(): string
    {
        return array_key_exists('productLink', $this->params) ? $this->params['productLink'] : '';
    }

    public function setProductLink($productLink): void
    {
        $this->params['productLink'] = $productLink;
    }

    public function getProductText(): string
    {
        return array_key_exists('productText', $this->params) ? $this->params['productText'] : '';
    }

    public function setProductText(string $productText): void
    {
        $this->params['productText'] = $productText;
    }
}
