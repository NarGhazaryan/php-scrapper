<?php

namespace App;

require 'vendor/autoload.php';

class Scrape
{
    private array $products = [];
    public function run(): void
    {
        $BASE_URL = 'https://www.magpiehq.com/developer-challenge/smartphones';

        $document = ScrapeHelper::fetchDocument($BASE_URL);

        $pageLinks = $document->filter('#pages')->filter('a');

        $pageLinks->each(function ($link) use (&$BASE_URL){
            $pageURL = $BASE_URL . trim(substr($link->attr('href'), strlen('../smartphones')));

            echo "Parsing: ", $pageURL, "\n";
            $document = ScrapeHelper::fetchDocument($pageURL);

            $productNodes = $document->filter('.product');

            $productNodes->each(function ($productNode) {
                $productParser = new Product($productNode);
                $this->products = array_merge($this->products, $productParser->getProductVariationsArray());
            });
        });

        $uniqueProducts = [];

        foreach ($this->products as $product) {
        $key = $product['title'] . '_' . $product['colour'];

        if (!isset($uniqueProducts[$key])) {
            $uniqueProducts[$key] = $product;
        }
    }

        $uniqueProducts = array_values($uniqueProducts);

        file_put_contents('output.json', json_encode($uniqueProducts));
    }
}

$scrape = new Scrape();
$scrape->run();
