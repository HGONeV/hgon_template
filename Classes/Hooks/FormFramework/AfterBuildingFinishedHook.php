<?php
namespace HGON\HgonTemplate\Hooks\FormFramework;
use TYPO3\CMS\Extbase\Utility\DebuggerUtility;

/**
 * Created by PhpStorm.
 * User: maximilian
 * Date: 02.12.19
 * Time: 16:04
 */
class AfterBuildingFinishedHook
{
    /**
     * @param \TYPO3\CMS\Form\Domain\Model\Renderable\RenderableInterface $renderable
     * @return void
     */
    public function afterBuildingFinished(\TYPO3\CMS\Form\Domain\Model\Renderable\RenderableInterface $renderable)
    {

        $data = \TYPO3\CMS\Core\Utility\GeneralUtility::_GP('tx_hgondonation_detail');

        if (
            is_array($data)
            && array_key_exists('donation', $data)
            && $data['donation']
        ) {
            // if we're on donation detail page, edit subject
            if ($renderable->getIdentifier() === 'subject') {

                $objectManager = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance('TYPO3\\CMS\\Extbase\\Object\\ObjectManager');
                /** @var \HGON\HgonDonation\Domain\Repository\DonationRepository $donationRepository */
                $donationRepository = $objectManager->get('HGON\\HgonDonation\\Domain\\Repository\\DonationRepository');
                $donation = $donationRepository->findByUid(intval($data['donation']));
                $renderable->setDefaultValue($donation->getTitle());
            }

        }
    }
}