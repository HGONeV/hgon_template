<?php
defined('TYPO3') or die();

$GLOBALS['TCA']['tx_hgontemplate_domain_model_eventculinary'] = [
	'ctrl' => [
	    'hideTable' => true,
		'title'	=> 'LLL:EXT:hgon_template/Resources/Private/Language/locallang_db.xlf:tx_hgontemplate_domain_model_eventculinary',
		'label' => 'title',
		'label_alt' => 'description',
        'rootLevel' => 0,
		'tstamp' => 'tstamp',
		'crdate' => 'crdate',
		'dividers2tabs' => true,

		'languageField' => 'sys_language_uid',
		'transOrigPointerField' => 'l10n_parent',
		'transOrigDiffSourceField' => 'l10n_diffsource',
		'delete' => 'deleted',
		'enablecolumns' => [
			'disabled' => 'hidden',
		],
		'searchFields' => 'title,description,price,date',
		'iconfile' => 'EXT:hgon_template/Resources/Public/Icons/tx_hgontemplate_domain_model_eventculinary.gif'
    ],
	'types' => [
		'1' => ['showitem' => 'sys_language_uid, l10n_diffsource, hidden, --palette--;;1,visibility, title, description, price, date,'],
	],
	'palettes' => [
		'1' => ['showitem' => ''],
	],
	'columns' => [

        'sys_language_uid' => [
            'exclude' => true,
            'label' => 'LLL:EXT:core/Resources/Private/Language/locallang_general.xlf:LGL.language',
            'config' => [
                'type' => 'language',
            ],
        ],
		'l10n_parent' => [
			'displayCond' => 'FIELD:sys_language_uid:>:0',
			'label' => 'LLL:EXT:lang/locallang_general.xlf:LGL.l18n_parent',
			'config' => [
				'type' => 'select',
				'renderType' => 'selectSingle',
                'items' => [
                    [
                        'label' => '',
                        'value' => 0,
                    ],
                ],
				'foreign_table' => 'tx_hgontemplate_domain_model_eventculinary',
				'foreign_table_where' => 'AND tx_hgontemplate_domain_model_eventculinary.pid=###CURRENT_PID### AND tx_hgontemplate_domain_model_eventculinary.sys_language_uid IN (-1,0)',
            ],
        ],
		'l10n_diffsource' => [
			'config' => [
				'type' => 'passthrough',
            ],
        ],

		'hidden' => [
			'exclude' => 1,
			'label' => 'LLL:EXT:lang/locallang_general.xlf:LGL.hidden',
			'config' => [
				'type' => 'check',
            ],
        ],

		'title' => [
			'exclude' => 0,
			'label' => 'LLL:EXT:hgon_template/Resources/Private/Language/locallang_db.xlf:tx_hgontemplate_domain_model_eventculinary.title',
			'config' => [
				'type' => 'input',
				'size' => 30,
				'eval' => 'trim'
            ],
        ],
        'description' => [
            'exclude' => 0,
            'label' => 'LLL:EXT:hgon_template/Resources/Private/Language/locallang_db.xlf:tx_hgontemplate_domain_model_eventculinary.description',
            'config' => [
                'type' => 'text',
                'cols' => 40,
                'rows' => 15,
                'eval' => 'trim',
                'required' => true
            ],
        ],
        'price' => [
            'exclude' => 0,
            'label' => 'LLL:EXT:hgon_template/Resources/Private/Language/locallang_db.xlf:tx_hgontemplate_domain_model_eventculinary.price',
            'config' => [
                'type' => 'input',
                'size' => 30,
                'eval' => 'trim, required'
            ],
        ],
        'date' => [
            'label' => 'LLL:EXT:hgon_template/Resources/Private/Language/locallang_db.xlf:tx_hgontemplate_domain_model_eventculinary.date',
            'config' => [
                'type' => 'datetime',
            ],
        ],
        'event' => array(
            'config' => array(
                'type' => 'passthrough',
            ),
        ),

    ],
];
