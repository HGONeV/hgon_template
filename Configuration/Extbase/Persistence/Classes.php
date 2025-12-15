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
        'tableName' => 'tx_rkwauthors_domain_model_authors',
    ],

    \HGON\HgonTemplate\Domain\Model\Projects::class => [
        'tableName' => 'tx_rkwprojects_domain_model_projects',
    ],

//    \HGON\HgonTemplate\Domain\Model\Event::class => [
//        'tableName' => 'tx_rkwevents_domain_model_event',
//    ],
//
//    \HGON\HgonTemplate\Domain\Model\EventReservation::class => [
//        'tableName' => 'tx_rkwevents_domain_model_eventreservation',
//    ],

//    \HGON\HgonTemplate\Domain\Model\DocumentType::class => [
//        'tableName' => 'tx_rkwbasics_domain_model_documenttype',
//    ],
//
//    \HGON\HgonTemplate\Domain\Model\Newsletter::class => [
//        'tableName' => 'tx_rkwnewsletter_domain_model_newsletter',
//    ],

    \HGON\HgonTemplate\Domain\Model\WorkGroup::class => [
        'tableName' => 'tx_hgonworkgroup_domain_model_workgroup',
    ],

    \HGON\HgonTemplate\Domain\Model\Article::class => [
        'tableName' => 'tx_hgonpayment_domain_model_article',
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


