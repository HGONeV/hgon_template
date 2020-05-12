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
$GLOBALS['TCA']['tx_rkwevents_domain_model_event']['types']['1']['showitem'] = str_replace(', currency,', ', tx_hgontemplate_eventculinary, currency,', $GLOBALS['TCA']['tx_rkwevents_domain_model_event']['types']['1']['showitem']);

// re-add existing gallery back to events
$GLOBALS['TCA']['tx_rkwevents_domain_model_event']['types']['1']['showitem'] = str_replace(', presentations,', ', gallery1, presentations,', $GLOBALS['TCA']['tx_rkwevents_domain_model_event']['types']['1']['showitem']);


// ***************
// RkwEvents (we need a extra plugin for reservations)
// -> we can't create a simple marker with controller and actions for eventReservation. Throws an error in relation of the multiple use of controller / actions / plugins on the same page
// ***************
\TYPO3\CMS\Extbase\Utility\ExtensionUtility::configurePlugin(
    'RKW.RkwEvents',
    'RkwEventsReservation',
    [
        'EventReservation' => 'new, create, createAlternative, update, delete, remove, optIn, edit, end',
    ],
    // non-cacheable actions
    [
        'EventReservation' => 'new, create, createAlternative, update, delete, remove, optIn, edit, end',
    ]
);

\TYPO3\CMS\Extbase\Utility\ExtensionUtility::configurePlugin(
    'RKW.RkwEvents',
    'Upcoming',
    array(
        'Event' => 'upcoming'
    ),
    // non-cacheable actions
    array(
        'Event' => 'upcoming'
    )
);