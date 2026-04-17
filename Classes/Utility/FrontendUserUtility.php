<?php

declare(strict_types=1);

namespace HGON\HgonTemplate\Utility;

use Psr\Http\Message\ServerRequestInterface;
use TYPO3\CMS\Frontend\Authentication\FrontendUserAuthentication;

final class FrontendUserUtility
{
    private function __construct() {}

    public static function getFrontendUserAuthentication(): ?FrontendUserAuthentication
    {
        $request = $GLOBALS['TYPO3_REQUEST'] ?? null;

        if ($request instanceof ServerRequestInterface) {
            $feUser = $request->getAttribute('frontend.user');
            if ($feUser instanceof FrontendUserAuthentication) {
                return $feUser;
            }
        }

        $feUser = $GLOBALS['FE_USER'] ?? null;
        return $feUser instanceof FrontendUserAuthentication ? $feUser : null;
    }

    public static function getFrontendUserUid(): int
    {
        $feUser = self::getFrontendUserAuthentication();
        return $feUser ? (int)($feUser->user['uid'] ?? 0) : 0;
    }

    public static function isFrontendUserLoggedIn(): bool
    {
        return self::getFrontendUserUid() > 0;
    }
}
