<?php
if (!defined ('TYPO3_MODE')) {
    die ('Access denied.');
}

$GLOBALS['TCA']['tx_rkwevents_domain_model_eventreservation']['columns']['tx_hgontemplate_eventcosts'] = [
    'exclude' => 0,
    'label' => 'LLL:EXT:hgon_template/Resources/Private/Language/locallang_db.xlf:tx_rkwevents_domain_model_event.tx_hgontemplate_eventcosts',
    'config' => [
        'readOnly' => 1,
        'type' => 'input',
        'size' => 30,
        'eval' => 'trim'
    ],
];

$GLOBALS['TCA']['tx_rkwevents_domain_model_eventreservation']['columns']['tx_hgontemplate_paymenttype'] = [
    'exclude' => 0,
    'label' => 'LLL:EXT:hgon_template/Resources/Private/Language/locallang_db.xlf:tx_rkwevents_domain_model_event.tx_hgontemplate_paymenttype',
    'config' => [
        'readOnly' => 1,
        'type' => 'select',
        'renderType' => 'selectSingle',
        'default' => 0,
        'items' => [
            ['LLL:EXT:hgon_template/Resources/Private/Language/locallang_db.xlf:tx_rkwevents_domain_model_event.tx_hgontemplate_paymenttype.0', 0],
            ['LLL:EXT:hgon_template/Resources/Private/Language/locallang_db.xlf:tx_rkwevents_domain_model_event.tx_hgontemplate_paymenttype.1', 1],
            ['LLL:EXT:hgon_template/Resources/Private/Language/locallang_db.xlf:tx_rkwevents_domain_model_event.tx_hgontemplate_paymenttype.2', 2],
        ],
    ],
];

$GLOBALS['TCA']['tx_rkwevents_domain_model_eventreservation']['columns']['tx_hgontemplate_eventculinary'] = [
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
//$GLOBALS['TCA']['tx_rkwevents_domain_model_eventreservation']['types']['1']['showitem'] = str_replace(', tx_hgontemplate_eventcosts,', ', tx_hgontemplate_eventcosts, tx_hgontemplate_eventculinary,', $GLOBALS['TCA']['tx_rkwevents_domain_model_eventreservation']['types']['1']['showitem']);
$GLOBALS['TCA']['tx_rkwevents_domain_model_eventreservation']['types']['1']['showitem'] = str_replace(', email,', ', email, tx_hgontemplate_eventcosts, tx_hgontemplate_paymenttype, tx_hgontemplate_eventculinary,', $GLOBALS['TCA']['tx_rkwevents_domain_model_eventreservation']['types']['1']['showitem']);