<?php
namespace Visol\EasyvoteImporter\Controller;

/***************************************************************
 *  Copyright notice
 *
 *  (c) 2013 Lorenz Ulrich <lorenz.ulrich@visol.ch>, visol digitale Dienstleistungen GmbH
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
use Visol\EasyvoteImporter\Domain\Model\BusinessUser;

/**
 *
 *
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3 or later
 *
 */
class BusinessUserController extends \TYPO3\CMS\Extbase\Mvc\Controller\ActionController {

	/**
	 * businessUserRepository
	 *
	 * @var \Visol\EasyvoteImporter\Domain\Repository\BusinessUserRepository
	 * @inject
	 */
	protected $businessUserRepository;

	/**
	 * action loginPanel
	 *
	 * @return void
	 */
	public function loginPanelAction() {
		$businessUser = $this->getLoggedInUser();
		if ($businessUser instanceof BusinessUser) {
			$this->view->assign('user', $businessUser);
		}
	}


	/**
	 * @return \Visol\EasyvoteImporter\Domain\Model\BusinessUser|bool
	 */
	protected function getLoggedInUser() {
		if ((int)$GLOBALS['TSFE']->fe_user->user['uid'] > 0) {
			$businessUser = $this->businessUserRepository->findByUid($GLOBALS['TSFE']->fe_user->user['uid']);
			if ($businessUser instanceof BusinessUser) {
				return $businessUser;
			} else {
				return FALSE;
			}
		} else {
			return FALSE;
		}
	}

}
?>