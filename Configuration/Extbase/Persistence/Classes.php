<?php
declare(strict_types = 1);

return [
    \HGON\HgonTemplate\Domain\Model\Pages::class => [
        'tableName' => 'pages',
        'properties' => [
            'uid' => ['fieldName' => 'uid'],
            'pid' => ['fieldName' => 'pid'],
            'sorting' => ['fieldName' => 'sorting'],
            'title' => ['fieldName' => 'title'],
            'subtitle' => ['fieldName' => 'subtitle'],
            'noSearch' => ['fieldName' => 'no_search'],
            'crdate' => ['fieldName' => 'crdate'],
            'tstamp' => ['fieldName' => 'tstamp'],
            'hidden' => ['fieldName' => 'hidden'],
            'deleted' => ['fieldName' => 'deleted'],
            'txHgontemplateArticleImage' => ['fieldName' => 'tx_hgontemplate_article_image'],
            'txHgontemplateTeaserText' => ['fieldName' => 'tx_hgontemplate_teaser_text'],
            // 'lastUpdated' => ['fieldName' => 'lastUpdated'], // nur falls das Feld wirklich existiert
        ],
    ],
    \HGON\HgonTemplate\Domain\Model\SysCategory::class => [
        'tableName' => 'sys_category',
        'properties' => [
            'title' => ['fieldName' => 'title'],
        ],
    ],

    \HGON\HgonTemplate\Domain\Model\Authors::class => [
        'tableName' => 'tx_mdnewsauthor_domain_model_newsauthor',
    ],

    \HGON\HgonTemplate\Domain\Model\WorkGroup::class => [
        'tableName' => 'tx_hgonworkgroup_domain_model_workgroup',
    ],
//    \HGON\HgonTemplate\Domain\Model\TtContent::class => [
//        'tableName' => 'tt_content',
//    ],

    // News: Subclass-Mapping (0 = Default-Typ)
    \GeorgRinger\News\Domain\Model\News::class => [
        'subclasses' => [
            0 => \HGON\HgonTemplate\Domain\Model\News::class,
        ],
    ],

    \HGON\HgonTemplate\Domain\Model\News::class => [
        'tableName'  => 'tx_news_domain_model_news',
        'recordType' => 0,
    ],
];
