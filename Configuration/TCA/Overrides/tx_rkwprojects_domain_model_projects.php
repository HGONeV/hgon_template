<?php
if (!defined ('TYPO3_MODE')) {
    die ('Access denied.');
}

$tempColumns = array(

    'partner_logos' => [
        'exclude' => 0,
        'label' => 'LLL:EXT:rkw_projects/Resources/Private/Language/locallang_db.xlf:tx_rkwprojects_domain_model_projects.partner_logos',
        'config' =>
        \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::getFileFieldTCAConfig(
            'partnerLogos',
            ['maxitems' => 10,
                  'foreign_types' => [
                      '0' => [
                          'showitem' => '
                            --palette--;LLL:EXT:lang/locallang_tca.xlf:sys_file_reference.imageoverlayPalette;imageoverlayPalette,
                            --palette--;;filePalette'
                      ],
                      \TYPO3\CMS\Core\Resource\File::FILETYPE_IMAGE => [
                          'showitem' => '
                            --palette--;LLL:EXT:lang/locallang_tca.xlf:sys_file_reference.imageoverlayPalette;imageoverlayPalette,
                            --palette--;;filePalette'
                      ],
                  ]
            ], $GLOBALS['TYPO3_CONF_VARS']['GFX']['imagefile_ext']

        ),
    ],

    'tx_hgontemplate_bank_header' => [
        'exclude' => true,
        'label' => 'LLL:EXT:hgon_template/Resources/Private/Language/locallang_db.xlf:tx_hgontemplate_domain_model_project.tx_hgontemplate_bank_header',
        'config' => [
            'type' => 'input',
            'size' => 30,
            'eval' => 'trim'
        ],
    ],
    'tx_hgontemplate_bank_institute' => [
        'exclude' => true,
        'label' => 'LLL:EXT:hgon_template/Resources/Private/Language/locallang_db.xlf:tx_hgontemplate_domain_model_project.tx_hgontemplate_bank_institute',
        'config' => [
            'type' => 'input',
            'size' => 30,
            'eval' => 'trim'
        ],
    ],
    'tx_hgontemplate_bank_iban' => [
        'exclude' => true,
        'label' => 'LLL:EXT:hgon_template/Resources/Private/Language/locallang_db.xlf:tx_hgontemplate_domain_model_project.tx_hgontemplate_bank_iban',
        'config' => [
            'type' => 'input',
            'size' => 30,
            'eval' => 'trim'
        ],
    ],
    'tx_hgontemplate_bank_bic' => [
        'exclude' => true,
        'label' => 'LLL:EXT:hgon_template/Resources/Private/Language/locallang_db.xlf:tx_hgontemplate_domain_model_project.tx_hgontemplate_bank_bic',
        'config' => [
            'type' => 'input',
            'size' => 30,
            'eval' => 'trim'
        ],
    ],


);
// Add TCA
\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addTCAcolumns(
    'tx_rkwprojects_domain_model_projects',
    $tempColumns
);

$GLOBALS['TCA']['tx_rkwprojects_domain_model_projects']['types']['1']['showitem'] = str_replace('project_staff,', 'project_staff, tx_hgontemplate_bank_header, tx_hgontemplate_bank_institute, tx_hgontemplate_bank_iban, tx_hgontemplate_bank_bic,', $GLOBALS['TCA']['tx_rkwprojects_domain_model_projects']['types']['1']['showitem']);



