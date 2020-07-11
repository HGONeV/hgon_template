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


);
// Add TCA
\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addTCAcolumns(
    'tx_rkwprojects_domain_model_projects',
    $tempColumns
);



