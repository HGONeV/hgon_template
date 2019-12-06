<?php
if (!defined ('TYPO3_MODE')) {
    die ('Access denied.');
}

$GLOBALS['TCA']['tx_rkwevents_domain_model_event']['columns']['tx_hgontemplate_eventculinary'] = [
    'exclude' => 0,
    'label' => 'LLL:EXT:hgon_template/Resources/Private/Language/locallang_db.xlf:tx_rkwevents_domain_model_event.tx_hgontemplate_eventculinary',
    'config' => [
        'type' => 'inline',
        'foreign_table' => 'tx_hgontemplate_domain_model_eventculinary',
        'foreign_field' => 'event',
        'maxitems'      => 9999,
        'minitems'      => 0,
        'appearance' => [
            'collapseAll' => 1,
            'expandSingle' => 1,
        ],
    ],
];
$GLOBALS['TCA']['tx_rkwevents_domain_model_event']['types']['1']['showitem'] = str_replace(', email,', ', tx_hgontemplate_eventculinary, currency,', $GLOBALS['TCA']['tx_rkwevents_domain_model_event']['types']['1']['showitem']);
