<?php
if (!defined ('TYPO3_MODE')) {
    die ('Access denied.');
}

$tempPagesColumns = [
    'tx_hgontemplate_subtitle' => [
        'exclude' => 0,
        'label' => 'LLL:EXT:hgon_template/Resources/Private/Language/locallang_db.xlf:tx_hgontemplate_domain_model_article.subtitle',
        'config' => [
            'type' => 'input',
            'size' => 30,
            'eval' => 'trim'
        ],
    ],
    'tx_hgontemplate_image' => [
        'exclude' => 0,
        'label' => 'LLL:EXT:hgon_template/Resources/Private/Language/locallang_db.xlf:tx_hgontemplate_domain_model_article.image',
        'config' => \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::getFileFieldTCAConfig(
            'image',
            array(
                'minitems' => 1,
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
            'jpg, png, gif'
        ),

    ],
    'tx_hgontemplate_link' => [
        'exclude' => 0,
        'label' => 'LLL:EXT:hgon_template/Resources/Private/Language/locallang_db.xlf:tx_hgontemplate_domain_model_article.link',
        'config' => [
            'type' => 'input',
            'size' => '50',
            'max' => '1024',
            'eval' => 'trim',
            'wizards' => [
                'link' => [
                    'type' => 'popup',
                    'title' => 'LLL:EXT:cms/locallang_ttc.xlf:header_link_formlabel',
                    'icon' => 'link_popup.gif',
                    'module' => [
                        'name' => 'wizard_element_browser',
                        'urlParameters' => [
                            'mode' => 'wizard'
                        ]
                    ],
                    'JSopenParams' => 'height=300,width=500,status=0,menubar=0,scrollbars=1'
                ]
            ],
            'softref' => 'typolink'
        ],
    ],
];

// Add TCA
\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addTCAcolumns(
    'tx_hgonpayment_domain_model_article',
    $tempPagesColumns
);
\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addToAllTCAtypes(
    'tx_hgonpayment_domain_model_article',
    'tx_hgontemplate_subtitle,tx_hgontemplate_image,tx_hgontemplate_link',
    '',
    'after:name'
);

