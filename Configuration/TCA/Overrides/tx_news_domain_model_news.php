<?php
if (!defined('TYPO3_MODE')) {
    die ('Access denied.');
}

// fix: https://github.com/georgringer/news/issues/1072
$GLOBALS['TCA']['tx_news_domain_model_news']['columns']['title']['config']['default'] = '';

// hide tx_news own type
//$condFce = 'FIELD:type:!=:0';
$condFce = ['AND' => [
    'FIELD:type:!=:0',
    'FIELD:type:!=:',
    ]];
$GLOBALS['TCA']['tx_news_domain_model_news']['columns']['type']['displayCond'] = $condFce;

// the tx_news-type is working like a doktype and has much influence on queries. Create own type!
$tempPagesColumns = [

    'tx_hgontemplate_type' => [
        'exclude' => false,
        'label' => 'LLL:EXT:hgon_template/Resources/Private/Language/locallang_db.xlf:tx_hgontemplate_domain_model_news.tx_hgontemplate_type',
        'config' => [
            'type' => 'select',
            'renderType' => 'selectSingle',
            'items' => [
                ['LLL:EXT:hgon_template/Resources/Private/Language/locallang_db.xlf:tx_hgontemplate_domain_model_news.tx_hgontemplate_type.0', 0],
                ['LLL:EXT:hgon_template/Resources/Private/Language/locallang_db.xlf:tx_hgontemplate_domain_model_news.tx_hgontemplate_type.1', 1],
                ['LLL:EXT:hgon_template/Resources/Private/Language/locallang_db.xlf:tx_hgontemplate_domain_model_news.tx_hgontemplate_type.2', 2],
                ['--- Bitte wählen ---', ''],
            ],
            'fieldWizard' => [
                'selectIcons' => [
                    'disabled' => false,
                ],
            ],
            'size' => 1,
            'maxitems' => 1,
            'default' => '',
            'required' => 1
        ],
        'onChange' => 'reload',
    ],

    'tx_hgontemplate_youtube_video_id' => [
        'exclude' => false,
        'label' => 'LLL:EXT:hgon_template/Resources/Private/Language/locallang_db.xlf:tx_hgontemplate_domain_model_news.tx_hgontemplate_youtube_video_id',
        'config' => [
            'type' => 'input',
            'size' => 30,
            'eval' => 'trim'
        ],
    ],

    'tx_rkwproject_project' => [
        'exclude' => true,
        'label' => 'LLL:EXT:rkw_events/Resources/Private/Language/locallang_db.xlf:tx_rkwevents_domain_model_event.project',
        'config' => [
            'type' => 'select',
            'renderType' => 'selectMultipleSideBySide',
            'foreign_table' => 'tx_rkwprojects_domain_model_projects',
            'foreign_table_where' => 'AND ((\'###PAGE_TSCONFIG_IDLIST###\' <> \'0\' AND FIND_IN_SET(tx_rkwprojects_domain_model_projects.pid,\'###PAGE_TSCONFIG_IDLIST###\')) OR (\'###PAGE_TSCONFIG_IDLIST###\' = \'0\')) AND tx_rkwprojects_domain_model_projects.sys_language_uid = ###REC_FIELD_sys_language_uid### ORDER BY tx_rkwprojects_domain_model_projects.short_name ASC',
            'maxitems'      => 1,
            'minitems'      => 1,
            'size'          => 5,
        ],
        'displayCond' => 'FIELD:tx_hgontemplate_type:=:2',
    ],

    'tx_hgontemplate_header_image' => [
        'exclude' => 0,
        'label' => 'LLL:EXT:hgon_template/Resources/Private/Language/locallang_db.xlf:tx_hgontemplate_domain_model_news.tx_hgontemplate_header_image',
        'config' => \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::getFileFieldTCAConfig(
            'image',
            array(
                'minitems' => 0,
                'maxitems' => 1,
                'overrideChildTca' => [
                    'types' => [
                        \TYPO3\CMS\Core\Resource\File::FILETYPE_IMAGE => [
                            'showitem' => '
                    --palette--;LLL:EXT:lang/Resources/Private/Language/locallang_tca.xlf:sys_file_reference.imageoverlayPalette;imageoverlayPalette,
                    --palette--;;filePalette'
                        ],
                    ],
                ],
            ),
            'jpg, jpeg, png, gif'
        ),

    ],

];
// Add TCA
\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addTCAcolumns(
    'tx_news_domain_model_news',
    $tempPagesColumns
);
\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addToAllTCAtypes(
    'tx_news_domain_model_news',
    'tx_hgontemplate_type,tx_rkwproject_project',
    '',
    'before:title'
);
\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addToAllTCAtypes(
    'tx_news_domain_model_news',
    'tx_hgontemplate_header_image,tx_hgontemplate_youtube_video_id',
    '',
    'before:fal_media'
);

$GLOBALS['TCA']['tx_news_domain_model_news']['types']['0'] = [
    'showitem' => '
    --palette--;;paletteCore,tx_hgontemplate_type,tx_rkwproject_project,title,--palette--;;paletteSlug,teaser,
    datetime,
    bodytext,
    --div--;LLL:EXT:frontend/Resources/Private/Language/locallang_ttc.xlf:tabs.media,
    tx_hgontemplate_header_image,tx_hgontemplate_youtube_video_id,fal_media,fal_related_files,
    --div--;LLL:EXT:core/Resources/Private/Language/Form/locallang_tabs.xlf:categories,
    categories,
    --div--;' . $ll . 'tx_news_domain_model_news.tabs.relations,
    related_links,
    tx_hgon_workgroup,
    --div--;LLL:EXT:core/Resources/Private/Language/Form/locallang_tabs.xlf:access,
    --palette--;;paletteHidden,
    --palette--;;paletteAccess,
    '
];


// set available fields (also overwrite standard news with type 0)
//$showFields = ['showitem' => 'type, title, teaser, datetime, bodytext, fal_media, categories, tags, tx_hgon_workgroup'];
/*
$showFields = ['showitem' => '
                --palette--;;paletteCore,title,--palette--;;paletteSlug,teaser,
                --palette--;;paletteDate,
                bodytext,
                --div--;' . $ll . 'tx_news_domain_model_news.content_elements,
                    content_elements,
                --div--;LLL:EXT:frontend/Resources/Private/Language/locallang_ttc.xlf:tabs.media,
                    fal_media,fal_related_files,
                --div--;LLL:EXT:core/Resources/Private/Language/Form/locallang_tabs.xlf:categories,
                    categories,
                --div--;' . $ll . 'tx_news_domain_model_news.tabs.relations,
                    related,related_from,
                    related_links,tags,
                    tx_hgon_workgroup,
                --div--;LLL:EXT:frontend/Resources/Private/Language/locallang_tca.xlf:pages.tabs.metadata,
                    --palette--;LLL:EXT:frontend/Resources/Private/Language/locallang_tca.xlf:pages.palettes.editorial;paletteAuthor,
                    --palette--;LLL:EXT:frontend/Resources/Private/Language/locallang_tca.xlf:pages.palettes.metatags;metatags,
                    --palette--;' . $ll . '                                                        palettes.alternativeTitles;alternativeTitles,
                --div--;LLL:EXT:core/Resources/Private/Language/Form/locallang_tabs.xlf:language,
                    --palette--;;paletteLanguage,
                --div--;LLL:EXT:core/Resources/Private/Language/Form/locallang_tabs.xlf:access,
                    --palette--;;paletteHidden,
                    --palette--;;paletteAccess,
                --div--;' . $ll . 'notes,
                    notes,
                --div--;LLL:EXT:frontend/Resources/Private/Language/locallang_tca.xlf:pages.tabs.extended,'
        ];
*/
