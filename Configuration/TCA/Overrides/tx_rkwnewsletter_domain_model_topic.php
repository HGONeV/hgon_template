<?php
if (!defined ('TYPO3_MODE')) {
    die ('Access denied.');
}
// remove color-items (not used)
$GLOBALS['TCA']['tx_rkwnewsletter_domain_model_topic']['types']['1']['showitem'] = str_replace(', container_page, primary_color, primary_color_editorial, secondary_color, secondary_color_editorial,', ', container_page,', $GLOBALS['TCA']['tx_rkwnewsletter_domain_model_topic']['types']['1']['showitem']);
