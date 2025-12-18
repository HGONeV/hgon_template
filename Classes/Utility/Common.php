<?php

namespace HGON\HgonTemplate\Utility;

/*
 * This file is part of the TYPO3 CMS project.
 *
 * It is free software; you can redistribute it and/or modify it under
 * the terms of the GNU General Public License, either version 2
 * of the License, or any later version.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 *
 * The TYPO3 project - inspiring people to share!
 */

use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Configuration\ConfigurationManagerInterface;
use TYPO3\CMS\Extbase\Object\ObjectManager;

/**
 * Class Common
 *
 * @author Steffen Kroggel <developer@steffenkroggel.de>
 * @copyright Rkw Kompetenzzentrum
 * @package Hgon_HgonTemplate
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3 or later
 */
class Common
{

    /**
     * Get TypoScript configuration
     *
     * @param string $extension
     * @param string $type
     * @return array
     * @throws \TYPO3\CMS\Extbase\Configuration\Exception\InvalidConfigurationTypeException
     */
    public static function getTypoScriptConfiguration(
        string $extension = '',
        string $type = ConfigurationManagerInterface::CONFIGURATION_TYPE_SETTINGS
    ): array {
        /** @var ConfigurationManagerInterface $configurationManager */
        $configurationManager = GeneralUtility::makeInstance(ConfigurationManagerInterface::class);

        $settings = $configurationManager->getConfiguration($type, $extension);

        return is_array($settings) ? $settings : [];
    }


    /**
     * @return int[]
     */
    public static function normalizeToUidArray(iterable $items): array
    {
        $uids = [];

        foreach ($items as $item) {
            if (is_object($item) && method_exists($item, 'getUid')) {
                $uid = (int)$item->getUid();
                if ($uid > 0) {
                    $uids[] = $uid;
                }
            } elseif (is_numeric($item)) {
                $uids[] = (int)$item;
            }
        }

        return array_values(array_unique($uids));
    }


}
