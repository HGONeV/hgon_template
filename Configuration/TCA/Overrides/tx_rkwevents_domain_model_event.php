<?php
if (!defined ('TYPO3_MODE')) {
    die ('Access denied.');
}


$GLOBALS['TCA']['tx_rkwevents_domain_model_event']['columns']['internal_contact'] = array(
    'exclude' => 0,
    'label' => 'LLL:EXT:rkw_events/Resources/Private/Language/locallang_db.xlf:tx_rkwevents_domain_model_event.internal_contact',
    'config' => array(
        'type' => 'select',
        'renderType' => 'selectMultipleSideBySide',
        'foreign_table' => 'tx_rkwauthors_domain_model_authors',
        'foreign_table_where' => 'AND tx_rkwauthors_domain_model_authors.internal = 1 AND tx_rkwauthors_domain_model_authors.sys_language_uid = ###REC_FIELD_sys_language_uid### ORDER BY tx_rkwauthors_domain_model_authors.last_name ASC',
        'maxitems'      => 9999,
        'minitems'      => 0,
        'size'          => 5,
    ),
);
$GLOBALS['TCA']['tx_rkwevents_domain_model_event']['types']['1']['showitem'] = str_replace(', external_contact,', ', internal_contact, external_contact,', $GLOBALS['TCA']['tx_rkwevents_domain_model_event']['types']['1']['showitem']);


// ***************
// RkwEvents (we need a extra plugin for reservations)
// -> we can't create a simple marker with controller and actions for eventReservation. Throws an error in relation of the multiple use of controller / actions / plugins on the same page
// ***************
\TYPO3\CMS\Extbase\Utility\ExtensionUtility::configurePlugin(
    'RKW.RkwEvents',
    'RkwEventsReservation',
    [
        'EventReservation' => 'new, create, update, delete, remove, optIn, edit, end',
    ],
    // non-cacheable actions
    [
        'EventReservation' => 'new, create, update, delete, remove, optIn, edit, end',
    ]
);