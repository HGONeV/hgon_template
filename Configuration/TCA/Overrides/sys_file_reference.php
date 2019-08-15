<?php
if (!defined ('TYPO3_MODE')) {
    die ('Access denied.');
}

// throw away all pre-set ratios. Look following file for image ratio definitions:
// /hgon_template/Configuration/TsConfig/TceForm/SysFileReference.typoscript
unset($GLOBALS['TCA']['sys_file_reference']['columns']['crop']['config']['ratios']);