<?php
defined('TYPO3') or die("Access denied.");

$GLOBALS['TCA']['tx_hgontemplate_domain_model_didyouknow'] = [
	'ctrl' => [
		'title'	=> 'LLL:EXT:hgon_template/Resources/Private/Language/locallang_db.xlf:tx_hgontemplate_domain_model_didyouknow',
		'label' => 'description',
		'label_alt' => 'title',
        'rootLevel' => 0,
		'tstamp' => 'tstamp',
		'crdate' => 'crdate',
		'dividers2tabs' => TRUE,
		'languageField' => 'sys_language_uid',
		'transOrigPointerField' => 'l10n_parent',
		'transOrigDiffSourceField' => 'l10n_diffsource',
		'delete' => 'deleted',
		'enablecolumns' => [
			'disabled' => 'hidden',
		],
		'searchFields' => 'title,description',
		'iconfile' => 'EXT:hgon_template/Resources/Public/Icons/tx_hgontemplate_domain_model_didyouknow.gif'
    ],
	'types' => [
		'1' => ['showitem' => 'sys_language_uid, l10n_diffsource, hidden, --palette--;;1,visibility, title, description,'],
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
				'foreign_table' => 'tx_hgontemplate_domain_model_didyouknow',
				'foreign_table_where' => 'AND tx_hgontemplate_domain_model_didyouknow.pid=###CURRENT_PID### AND tx_hgontemplate_domain_model_didyouknow.sys_language_uid IN (-1,0)',
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
			'label' => 'LLL:EXT:hgon_template/Resources/Private/Language/locallang_db.xlf:tx_hgontemplate_domain_model_didyouknow.title',
			'config' => [
				'type' => 'input',
				'size' => 30,
				'eval' => 'trim'
            ],
        ],
        'description' => [
            'exclude' => 0,
            'label' => 'LLL:EXT:hgon_template/Resources/Private/Language/locallang_db.xlf:tx_hgontemplate_domain_model_didyouknow.description',
            'config' => [
                'type' => 'text',
                'enableRichtext' => true,
                'richtextConfiguration' => 'default',
                'fieldControl' => [
                    'fullScreenRichtext' => [
                        'disabled' => false,
                    ],
                ],
                'cols' => 40,
                'rows' => 15,
                'eval' => 'trim',
                'required' => true
            ],
        ],
		'sys_category' => [
			'exclude' => 0,
			'label' => 'LLL:EXT:hgon_template/Resources/Private/Language/locallang_db.xlf:tx_hgontemplate_domain_model_didyouknow.sys_category',
			'config' => [
				'type' => 'select',
				'renderType' => 'selectMultipleSideBySide',
				'foreign_table' => 'sys_category',
				'foreign_table_where' => 'AND sys_language_uid = ###REC_FIELD_sys_language_uid### ORDER BY title ASC',
				'size' => 10,
				'autoSizeMax' => 30,
				'maxitems' => 9999,
				'multiple' => 0,
            ],
        ],
    ],
];
