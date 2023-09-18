<?php

namespace App;

use GuzzleHttp\Client;
use Symfony\Component\DomCrawler\Crawler;

class ScrapeHelper
{
    public static function fetchDocument(string $url): Crawler
    {
        $client = new Client();

        $response = $client->get($url);

        return new Crawler($response->getBody()->getContents(), $url);
    }

    public static function findDateInString(string $string): string
    {

        $ordinalNumberEndings = ['st', 'nd', 'rd', 'th'];
        $shortMonthNames = [
            "Jan",
            "Feb",
            "Mar",
            "Apr",
            "May",
            "Jun",
            "Jul",
            "Aug",
            "Sep",
            "Oct",
            "Nov",
            "Dec"
        ];

        if (preg_match("/\d{4}\-\d{2}-\d{2}/", $string, $matches)) {
            return $matches[0];
        }

        if (preg_match('/tomorrow|today|yesterday|next|last|prev|((0?[1-9]|([12][0-9])|(3[01]))(' . implode('|', $ordinalNumberEndings) . ')?\s(' . implode('|', $shortMonthNames) . ')\s\d{4})/', $string, $matches)) {
            $dateTimestamp = strtotime($matches[0]);
            $date = date('Y-m-d', $dateTimestamp);
            return $date;
        }

        return "";
    }
}
