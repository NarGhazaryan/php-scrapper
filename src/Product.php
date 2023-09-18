<?php

namespace App;

use Symfony\Component\DomCrawler\Crawler;

class Product
{
    public string $title;
    public float $price;
    public string $imageUrl;
    public int $capacityMB;
    public array  $colours = [];
    public string $availabilityText = '';
    public bool $isAvailable;
    public string $shippingText = '';
    public string $shippingDate = '';

    public function __construct(Crawler $product)
    {
        $this->parseProduct($product);
    }

    private function parseProduct(Crawler $product): void
    {
        $productNode = $product->children()->first();

        $title = $productNode->filter('h3')->text();
        $this->title = $title;
        $this->imageUrl = $productNode->filter('img')->image()->getUri();

        if (preg_match('/(\d+)\s*(GB|MB)/i', $title, $matches)) {
            $capacity = (int)$matches[1];
            $unit = strtoupper($matches[2]);
            $this->capacityMB = ($unit === 'GB') ? $capacity * 1024 : $capacity;
        }

        $productInfoFields = $productNode->filter('div.text-center');

        $productInfoFields->each(function (Crawler $text) {
            $text = $text->text();
            if (strpos($text, '£') === 0) {
                $this->price = (float)trim($text, '£ ');
            } elseif (strpos($text, 'Availability:') === 0) {
                $availabilityText = trim(substr($text, strlen('Availability:')));
                $this->availabilityText = $availabilityText;
                $this->isAvailable = (strpos($availabilityText, 'In Stock') === 0);
            } else {
                $this->shippingText = $text;
                $this->shippingDate = ScrapeHelper::findDateInString($text);
            }
        });

        $colours = $productNode->children()->filter('div')->filter('span');

        $colours->each(function (Crawler $colour) {
            $this->colours[] = $colour->attr('data-colour');
        });
    }

    public function getProductVariationsArray(): array
    {
        $productVariations = [];

        foreach ($this->colours as $colour){
            $productVariation = [];

            $productVariation['title'] = $this->title;
            $productVariation['price'] = $this->price;
            $productVariation['imageUrl'] = $this->imageUrl;
            $productVariation['capacityMB'] = $this->capacityMB;
            $productVariation['colour'] = $colour;
            $productVariation['availabilityText'] = $this->availabilityText;
            $productVariation['isAvailable'] = $this->isAvailable;
            $productVariation['shippingText'] = $this->shippingText;
            $productVariation['shippingDate'] = $this->shippingDate;

            $productVariations[] = $productVariation;
        }

        return $productVariations;
    }
}
