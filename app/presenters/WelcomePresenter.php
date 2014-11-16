<?php

use Nette\Application;
use Nette\Application\UI;
use Nette\Database;
use Nette\Application\ForbiddenRequestException;
use Nette\Application\ApplicationException;
use Nette\Application\BadRequestException;

/**
 * CMS pages presenter
 */
class WelcomePresenter extends BasePresenter
{

	public function renderDefault($step)
	{
				
		if (! $this->user->isInRole('approved'))
		{
			$this->redirect("Homepage:default");
		}

		if($step == "" or $step == NULL){ $step = 1; }
		
		$database = $this->context->database;
		$myPages = $database->table("Users")->where(array(
			"Username" => $this->user->identity->username
		));

		$this->template->setParameters(array(
			"userData" => $myPages->fetch()
		));

		$this->template->setFile(realpath(__DIR__ . "/..") . '/templates/Welcome/welcome_' . $step . '.latte');
	}

}