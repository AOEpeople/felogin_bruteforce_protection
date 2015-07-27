<?php
namespace Aoe\FeloginBruteforceProtection\Domain\Service;

/***************************************************************
 *  Copyright notice
 *
 *  (c) 2015 AOE GmbH <dev@aoe.com>
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

use Aoe\FeloginBruteforceProtection\Service\Logger\LoggerInterface;
use TYPO3\CMS\Frontend\Authentication\FrontendUserAuthentication;

/**
 * @package Aoe\FeloginBruteforceProtection\Domain\Service
 * @author Patrick Roos <patrick.roos@aoe.com>
 */
class RestrictionIdentifierFrontendName extends RestrictionIdentifierAbstract
{
    /**
     * @var FrontendUserAuthentication
     */
    protected $frontendUserAuthentication;

    /**
     * the value of the restriction identifier
     * @return string
     */
    public function getIdentifierValue()
    {
        if (!isset($this->identifierValue)) {
            $loginFormData = $this->frontendUserAuthentication->getLoginFormData();
            if (isset($loginFormData['uname']) &&  !empty($loginFormData['uname'])) {
                $this->identifierValue = $loginFormData['uname'];
            } else {
                $this->identifierValue = '';
                $this->log(
                    'Empty user login.',
                    LoggerInterface::SEVERITY_NOTICE
                );
            }
        }
        return $this->identifierValue;
    }

    /**
     * no precondition for frontend name
     * @return boolean
     */
    public function checkPreconditions()
    {
        return true;
    }

    /**
     * @param FrontendUserAuthentication $frontendUserAuthentication
     * @return void
     **/
    public function setFrontendUserAuthentication(FrontendUserAuthentication $frontendUserAuthentication)
    {
        $this->frontendUserAuthentication = $frontendUserAuthentication;
    }
}
