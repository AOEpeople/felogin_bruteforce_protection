<?php

namespace Aoe\FeloginBruteforceProtection\Domain\Service;

/***************************************************************
 *  Copyright notice
 *
 *  (c) 2019 AOE GmbH <dev@aoe.com>
 *
 *  All rights reserved
 *
 *  This script is part of the TYPO3 project. The TYPO3 project is
 *  free software; you can redistribute it and/or modify
 *  it under the terms of the GNU General Public License as published by
 *  the Free Software Foundation; either version 3 of the License, or
 *  (at your option) any later version.
 *
 *  The GNU General Public License can be found at
 *  http://www.gnu.org/copyleft/gpl.html.
 *
 *  This script is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU General Public License for more details.
 *
 *  This copyright notice MUST APPEAR in all copies of the script!
 ***************************************************************/

use Psr\Http\Message\ServerRequestInterface;
use TYPO3\CMS\Frontend\Authentication\FrontendUserAuthentication;

class RestrictionIdentifierFrontendName extends RestrictionIdentifierAbstract
{
    /**
     * @var FrontendUserAuthentication
     */
    protected $frontendUserAuthentication;

    /**
     * the value of the restriction identifier
     *
     * @return string
     */
    public function getIdentifierValue()
    {
        if (!isset($this->identifierValue)) {
            $loginFormData = $this->frontendUserAuthentication->getLoginFormData($this->getRequest());
            if (isset($loginFormData['uname']) && !empty($loginFormData['uname'])) {
                $this->identifierValue = $loginFormData['uname'];
            } elseif(array_key_exists('feuser_login_username', $_POST)) {
                /**
                 * If we build our own FE-login-form, then it can happen, that TYPO3 doesn't recognize the username,
                 * so we must support the "workaround", that we can (during the FE-login-process) manually put the
                 * username in the $_POST-variable 'feuser_login_username'.
                 */
                $this->identifierValue = $_POST['feuser_login_username'];
            } else {
                $this->identifierValue = '';
            }
        }

        return $this->identifierValue;
    }

    /**
     * no precondition for frontend name
     */
    public function checkPreconditions(): bool
    {
        return true;
    }

    public function setFrontendUserAuthentication(FrontendUserAuthentication $frontendUserAuthentication): void
    {
        $this->frontendUserAuthentication = $frontendUserAuthentication;
    }

    private function getRequest(): ServerRequestInterface
    {
        return $GLOBALS['TYPO3_REQUEST'];
    }
}
