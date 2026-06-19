<?php

return [
    [
        'url' => 'https://rss.nytimes.com/services/xml/rss/nyt/HomePage.xml',
        'country' => 'US',
        'category' => 'news',
        'rank' => 1,
        'is_featured' => true,
    ],
    [
        'url' => 'https://feeds.bbci.co.uk/news/rss.xml',
        'country' => 'UK',
        'category' => 'news',
        'rank' => 2,
        'is_featured' => true,
    ],
    [
        'url' => 'https://www.heise.de/rss/heise-atom.xml',
        'country' => 'DE',
        'category' => 'tech',
        'rank' => 3,
        'is_featured' => false,
    ],
    [
        'url' => 'https://www.reddit.com/r/worldnews/.rss',
        'country' => null,
        'category' => 'social',
        'rank' => 4,
        'is_featured' => false,
    ],

    // =========================
    // USA weitere Quellen
    // =========================
    [
        'url' => 'https://feeds.npr.org/1001/rss.xml',
        'country' => 'US',
        'category' => 'news',
        'rank' => 5,
        'is_featured' => true,
    ],
    [
        'url' => 'https://rss.cnn.com/rss/edition.rss',
        'country' => 'US',
        'category' => 'news',
        'rank' => 6,
        'is_featured' => true,
    ],
    [
        'url' => 'https://feeds.a.dj.com/rss/RSSWorldNews.xml',
        'country' => 'US',
        'category' => 'business',
        'rank' => 7,
        'is_featured' => false,
    ],

    // =========================
    // UK weitere Quellen
    // =========================
    [
        'url' => 'https://www.theguardian.com/world/rss',
        'country' => 'UK',
        'category' => 'news',
        'rank' => 8,
        'is_featured' => true,
    ],
    [
        'url' => 'https://www.telegraph.co.uk/news/rss.xml',
        'country' => 'UK',
        'category' => 'news',
        'rank' => 9,
        'is_featured' => false,
    ],

    // =========================
    // Deutschland weitere Quellen
    // =========================
    [
        'url' => 'https://www.spiegel.de/international/index.rss',
        'country' => 'DE',
        'category' => 'news',
        'rank' => 10,
        'is_featured' => true,
    ],
    [
        'url' => 'https://www.faz.net/rss/aktuell/',
        'country' => 'DE',
        'category' => 'news',
        'rank' => 11,
        'is_featured' => false,
    ],

    // =========================
    // Frankreich
    // =========================
    [
        'url' => 'https://www.lemonde.fr/rss/en_continu.xml',
        'country' => 'FR',
        'category' => 'news',
        'rank' => 12,
        'is_featured' => true,
    ],
    [
        'url' => 'https://www.france24.com/en/rss',
        'country' => 'FR',
        'category' => 'news',
        'rank' => 13,
        'is_featured' => false,
    ],

    // =========================
    // Internationale / weitere Länder
    // =========================
    [
        'url' => 'https://www.aljazeera.com/xml/rss/all.xml',
        'country' => 'QA',
        'category' => 'news',
        'rank' => 14,
        'is_featured' => true,
    ],
    [
        'url' => 'https://www.reuters.com/rssFeed/worldNews',
        'country' => null,
        'category' => 'news',
        'rank' => 15,
        'is_featured' => true,
    ],
    [
        'url' => 'https://www.rt.com/rss/',
        'country' => 'RU',
        'category' => 'news',
        'rank' => 16,
        'is_featured' => false,
    ],
];