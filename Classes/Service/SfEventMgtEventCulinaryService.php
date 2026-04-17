<?php

declare(strict_types=1);

namespace HGON\HgonTemplate\Service;

use DateTimeImmutable;
use DERHANSEN\SfEventMgt\Domain\Model\Registration;
use DERHANSEN\SfEventMgt\Domain\Repository\RegistrationRepository;
use Doctrine\DBAL\ParameterType;
use TYPO3\CMS\Core\Database\Connection;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Error\Error;
use TYPO3\CMS\Extbase\Error\Result;
use TYPO3\CMS\Extbase\Persistence\Generic\PersistenceManager;
use TYPO3\CMS\Extbase\Reflection\ObjectAccess;
use Psr\Http\Message\ServerRequestInterface;
use TYPO3\CMS\Extbase\Utility\LocalizationUtility;

final class SfEventMgtEventCulinaryService
{
    private const PLUGIN_NAMESPACE = 'tx_sfeventmgt_pieventregistration';
    private const QUANTITY_ARGUMENT = 'culinaryQuantities';

    private readonly ConnectionPool $connectionPool;
    private readonly RegistrationRepository $registrationRepository;
    private readonly PersistenceManager $persistenceManager;

    public function __construct(
        ?ConnectionPool $connectionPool = null,
        ?RegistrationRepository $registrationRepository = null,
        ?PersistenceManager $persistenceManager = null,
    ) {
        $this->connectionPool = $connectionPool ?? GeneralUtility::makeInstance(ConnectionPool::class);
        $this->registrationRepository = $registrationRepository ?? GeneralUtility::makeInstance(RegistrationRepository::class);
        $this->persistenceManager = $persistenceManager ?? GeneralUtility::makeInstance(PersistenceManager::class);
    }

    public function getSubmittedQuantities(ServerRequestInterface $request): array
    {
        $pluginArguments = $this->getPluginArguments($request);
        $submittedValues = $pluginArguments[self::QUANTITY_ARGUMENT] ?? [];
        if (!is_array($submittedValues)) {
            return [];
        }

        $quantities = [];
        foreach ($submittedValues as $optionUid => $quantity) {
            $uid = (int)$optionUid;
            if ($uid <= 0) {
                continue;
            }
            $quantities[$uid] = max(0, (int)$quantity);
        }

        return $quantities;
    }

    public function getSubmittedAmountOfRegistrations(ServerRequestInterface $request, ?Registration $registration = null): int
    {
        $pluginArguments = $this->getPluginArguments($request);
        $amount = (int)($pluginArguments['registration']['amountOfRegistrations'] ?? 0);
        if ($amount > 0) {
            return $amount;
        }
        if ($registration instanceof Registration && $registration->getAmountOfRegistrations() > 0) {
            return $registration->getAmountOfRegistrations();
        }

        return 1;
    }

    public function getSubmittedEventUid(ServerRequestInterface $request, ?Registration $registration = null): int
    {
        $pluginArguments = $this->getPluginArguments($request);
        $eventUid = (int)($pluginArguments['event'] ?? 0);
        if ($eventUid > 0) {
            return $eventUid;
        }
        if ($registration instanceof Registration && $registration->getEvent()) {
            return (int)$registration->getEvent()->getUid();
        }

        return 0;
    }

    public function getEventCulinaryOptions(int $eventUid): array
    {
        if ($eventUid <= 0) {
            return [];
        }

        $queryBuilder = $this->connectionPool->getQueryBuilderForTable('tx_hgontemplate_domain_model_eventculinary');
        $rows = $queryBuilder
            ->select('uid', 'title', 'description', 'price', 'date')
            ->from('tx_hgontemplate_domain_model_eventculinary')
            ->where(
                $queryBuilder->expr()->eq('deleted', $queryBuilder->createNamedParameter(0, ParameterType::INTEGER)),
                $queryBuilder->expr()->eq('hidden', $queryBuilder->createNamedParameter(0, ParameterType::INTEGER)),
                $queryBuilder->expr()->eq('event', $queryBuilder->createNamedParameter($eventUid, ParameterType::INTEGER))
            )
            ->orderBy('sorting', 'ASC')
            ->addOrderBy('uid', 'ASC')
            ->executeQuery()
            ->fetchAllAssociative();

        return array_map(fn(array $row): array => $this->mapEventOptionRow($row), $rows);
    }

    public function buildSelectionRows(int $eventUid, array $quantities): array
    {
        $selections = [];
        foreach ($this->getEventCulinaryOptions($eventUid) as $option) {
            $option['quantity'] = max(0, (int)($quantities[$option['uid']] ?? 0));
            $selections[] = $option;
        }

        return $selections;
    }

    public function validateSelectionRows(int $eventUid, int $amountOfRegistrations, array $quantities, Result $result): void
    {
        $allowedOptions = [];
        foreach ($this->getEventCulinaryOptions($eventUid) as $option) {
            $allowedOptions[(int)$option['uid']] = true;
        }

        foreach ($quantities as $optionUid => $quantity) {
            if (!isset($allowedOptions[$optionUid])) {
                $result->forProperty(self::QUANTITY_ARGUMENT)->addError(
                    new Error(
                        $this->translate('tx_hgontemplate.sfeventmgt.registration.culinary.invalid')
                            ?? 'Ungültige Zusatzoption ausgewählt.',
                        1744273201
                    )
                );
                continue;
            }

            if ($quantity < 0 || $quantity > $amountOfRegistrations) {
                $result->forProperty(self::QUANTITY_ARGUMENT)->addError(
                    new Error(
                        $this->translate(
                            'tx_hgontemplate.sfeventmgt.registration.culinary.max',
                            [(string)$amountOfRegistrations]
                        ) ?? sprintf('Die Menge einer Zusatzoption darf maximal %d betragen.', $amountOfRegistrations),
                        1744273202
                    )
                );
            }
        }
    }

    public function calculateSelectionsTotal(array $selectionRows): float
    {
        $total = 0.0;
        foreach ($selectionRows as $selectionRow) {
            $total += ((float)$selectionRow['unitPrice']) * ((int)$selectionRow['quantity']);
        }

        return round($total, 2);
    }

    public function persistRegistrationAddons(Registration $registration, array $selectionRows): void
    {
        $connection = $this->connectionPool->getConnectionForTable('tx_hgontemplate_domain_model_registrationaddon');
        $connection->delete('tx_hgontemplate_domain_model_registrationaddon', [
            'registration' => (int)$registration->getUid(),
        ]);

        foreach ($selectionRows as $selectionRow) {
            if ((int)$selectionRow['quantity'] <= 0) {
                continue;
            }

            $connection->insert('tx_hgontemplate_domain_model_registrationaddon', [
                'pid' => (int)$registration->getPid(),
                'tstamp' => time(),
                'crdate' => time(),
                'registration' => (int)$registration->getUid(),
                'culinary' => (int)$selectionRow['uid'],
                'quantity' => (int)$selectionRow['quantity'],
                'title' => (string)$selectionRow['title'],
                'description' => (string)$selectionRow['description'],
                'unit_price' => number_format((float)$selectionRow['unitPrice'], 2, '.', ''),
                'selected_date' => $selectionRow['dateTimestamp'],
            ]);
        }
    }

    public function getRegistrationAddons(int $registrationUid): array
    {
        if ($registrationUid <= 0) {
            return [];
        }

        $queryBuilder = $this->connectionPool->getQueryBuilderForTable('tx_hgontemplate_domain_model_registrationaddon');
        $rows = $queryBuilder
            ->select('title', 'description', 'quantity', 'unit_price', 'selected_date')
            ->from('tx_hgontemplate_domain_model_registrationaddon')
            ->where(
                $queryBuilder->expr()->eq('deleted', $queryBuilder->createNamedParameter(0, ParameterType::INTEGER)),
                $queryBuilder->expr()->eq('hidden', $queryBuilder->createNamedParameter(0, ParameterType::INTEGER)),
                $queryBuilder->expr()->eq('registration', $queryBuilder->createNamedParameter($registrationUid, ParameterType::INTEGER))
            )
            ->orderBy('uid', 'ASC')
            ->executeQuery()
            ->fetchAllAssociative();

        return array_map(static function (array $row): array {
            $dateTimestamp = (int)$row['selected_date'];
            return [
                'title' => (string)$row['title'],
                'description' => (string)$row['description'],
                'quantity' => (int)$row['quantity'],
                'unitPrice' => (float)$row['unit_price'],
                'totalPrice' => round(((float)$row['unit_price']) * ((int)$row['quantity']), 2),
                'date' => $dateTimestamp > 0 ? (new DateTimeImmutable())->setTimestamp($dateTimestamp) : null,
            ];
        }, $rows);
    }

    public function createDependingRegistrationsWithMainPriceOnly(Registration $registration): void
    {
        $registrations = $registration->getAmountOfRegistrations();
        for ($i = 1; $i <= $registrations - 1; $i++) {
            $newRegistration = GeneralUtility::makeInstance(Registration::class);
            $properties = ObjectAccess::getGettableProperties($registration);
            foreach ($properties as $propertyName => $propertyValue) {
                ObjectAccess::setProperty($newRegistration, $propertyName, $propertyValue);
            }

            $newRegistration->setMainRegistration($registration);
            $newRegistration->setAmountOfRegistrations(1);
            $newRegistration->setIgnoreNotifications(true);
            $newRegistration->setPrice(0.0);
            $newRegistration->setTaxRate(0.0);
            $this->registrationRepository->add($newRegistration);
        }

        $this->persistenceManager->persistAll();
    }

    private function getPluginArguments(ServerRequestInterface $request): array
    {
        $parsedBody = $request->getParsedBody();
        if (!is_array($parsedBody)) {
            return [];
        }

        $pluginArguments = $parsedBody[self::PLUGIN_NAMESPACE] ?? [];
        return is_array($pluginArguments) ? $pluginArguments : [];
    }

    private function mapEventOptionRow(array $row): array
    {
        $dateTimestamp = (int)($row['date'] ?? 0);
        return [
            'uid' => (int)$row['uid'],
            'title' => (string)$row['title'],
            'description' => (string)$row['description'],
            'unitPrice' => $this->normalizePrice((string)($row['price'] ?? '0')),
            'date' => $dateTimestamp > 0 ? (new DateTimeImmutable())->setTimestamp($dateTimestamp) : null,
            'dateTimestamp' => $dateTimestamp,
        ];
    }

    private function normalizePrice(string $rawPrice): float
    {
        $normalizedPrice = trim(preg_replace('/[^0-9,.-]/', '', $rawPrice) ?: '');
        if ($normalizedPrice === '') {
            return 0.0;
        }
        if (str_contains($normalizedPrice, ',') && str_contains($normalizedPrice, '.')) {
            $normalizedPrice = str_replace('.', '', $normalizedPrice);
            $normalizedPrice = str_replace(',', '.', $normalizedPrice);
        } else {
            $normalizedPrice = str_replace(',', '.', $normalizedPrice);
        }

        return round((float)$normalizedPrice, 2);
    }

    private function translate(string $key, array $arguments = []): ?string
    {
        return LocalizationUtility::translate($key, 'hgon_template', $arguments);
    }
}
