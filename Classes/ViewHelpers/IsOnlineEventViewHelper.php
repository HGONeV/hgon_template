<?php

declare(strict_types=1);

namespace HGON\HgonTemplate\ViewHelpers;

use Doctrine\DBAL\ParameterType;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3Fluid\Fluid\Core\ViewHelper\AbstractViewHelper;

final class IsOnlineEventViewHelper extends AbstractViewHelper
{
    private static array $onlineEventCache = [];

    public function initializeArguments(): void
    {
        parent::initializeArguments();
        $this->registerArgument('event', 'object', 'Event object', true);
    }

    public function render(): bool
    {
        $event = $this->arguments['event'];
        $eventUid = is_object($event) && method_exists($event, 'getUid') ? (int)$event->getUid() : 0;
        if ($eventUid <= 0) {
            return false;
        }

        if (array_key_exists($eventUid, self::$onlineEventCache)) {
            return self::$onlineEventCache[$eventUid];
        }

        $queryBuilder = GeneralUtility::makeInstance(ConnectionPool::class)
            ->getQueryBuilderForTable('tx_sfeventmgt_domain_model_event');
        $onlineEvent = (bool)$queryBuilder
            ->select('tx_hgontemplate_online_event')
            ->from('tx_sfeventmgt_domain_model_event')
            ->where(
                $queryBuilder->expr()->eq(
                    'uid',
                    $queryBuilder->createNamedParameter($eventUid, ParameterType::INTEGER)
                )
            )
            ->executeQuery()
            ->fetchOne();

        self::$onlineEventCache[$eventUid] = $onlineEvent;

        return $onlineEvent;
    }
}
