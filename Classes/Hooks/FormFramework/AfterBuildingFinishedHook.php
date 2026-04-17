<?php
namespace HGON\HgonTemplate\Hooks\FormFramework;
use Psr\Http\Message\ServerRequestInterface;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Form\Domain\Model\Renderable\RenderableInterface;
use HGON\HgonDonation\Domain\Repository\DonationRepository;

/**
 * Created by PhpStorm.
 * User: maximilian
 * Date: 02.12.19
 * Time: 16:04
 */
final class AfterBuildingFinishedHook
{
    public function afterBuildingFinished(RenderableInterface $renderable): void
    {
        /** @var ServerRequestInterface|null $request */
        $request = $GLOBALS['TYPO3_REQUEST'] ?? null;

        $data = ($request?->getQueryParams()['tx_hgondonation_detail'] ?? null);

        if (!is_array($data) || empty($data['donation']) || $renderable->getIdentifier() !== 'subject') {
            return;
        }

        $donationUid = (int)preg_replace('/\D+/', '', (string)$data['donation']);
        if ($donationUid <= 0) {
            return;
        }

        /** @var DonationRepository $donationRepository */
        $donationRepository = GeneralUtility::makeInstance(DonationRepository::class);

        $donation = $donationRepository->findByIdentifier($donationUid);
        if ($donation !== null) {
            $renderable->setDefaultValue($donation->getTitle());
        }
    }
}
