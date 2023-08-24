<?php

declare(strict_types=1);

namespace MauticPlugin\MauticCitrixBundle\Event;

use Mautic\CoreBundle\Event\CommonEvent;

/**
 * Class TokenGenerateEvent.
 */
class TokenGenerateEvent extends CommonEvent
{
    /**
     * TokenGenerateEvent constructor.
     */
    public function __construct(private array $params)
    {
    }

    /**
     * Returns the params array.
     *
     * @return array
     */
    public function getParams()
    {
        return $this->params;
    }

    /**
     * @param array $params
     */
    protected function setParams($params)
    {
        $this->params = $params;
    }

    /**
     * @return string
     */
    public function getProduct()
    {
        return array_key_exists('product', $this->params) ? $this->params['product'] : '';
    }

    /**
     * @param string $product
     */
    public function setProduct($product): void
    {
        $this->params['product'] = $product;
    }

    /**
     * @return string
     */
    public function getProductLink()
    {
        return array_key_exists('productLink', $this->params) ? $this->params['productLink'] : '';
    }

    /**
     * @param string $productLink
     */
    public function setProductLink($productLink): void
    {
        $this->params['productLink'] = $productLink;
    }

    /**
     * @return string
     */
    public function getProductText()
    {
        return array_key_exists('productText', $this->params) ? $this->params['productText'] : '';
    }

    /**
     * @param string $productText
     */
    public function setProductText($productText): void
    {
        $this->params['productText'] = $productText;
    }
}
