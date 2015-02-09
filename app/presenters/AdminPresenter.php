<?php

use Nette\Application\ForbiddenRequestException;
use Nette\Application\ApplicationException;
use Nette\Application\BadRequestException;
use Nette\Application\UI;
use Nette\Utils\Html;
use Nette\Diagnostics\Debugger;

class AdminPresenter extends BasePresenter
{
	public function renderDefault()
	{
		$database = $this->context->database;
		$users = $database->table("Users")->where("IsApproved = 0");
		$user_ = $database->table("Users")->where("IsApproved = 1");
		$userf = $database->table("Users")->where("IsFrozen = 1");
		$userc = $database->table("Users");

		$this->template->users = $users;
		$this->template->activedAccountCount = count($user_);
		$this->template->unapprovedAccountCount = count($users);
		$this->template->frozenAccountCount = count($userf);
		$this->template->completeAccountCount = count($userc);
	}
	
	public function renderUsers()
	{
		$database = $this->context->database;
		$users_no = $database->table("Users")->where("IsApproved = 0")->order("id DESC");
		$users_ya = $database->table("Users")->where("IsApproved = 1")->order("id DESC");
		
		$this->template->users_no = $users_no;
		$this->template->users_ya = $users_ya;
	}
	
	public function renderUsersUnactive($userId){
		$database = $this->context->database;
		$user = $database->table("Users")->where("Id = ?", $userId);		
		$this->template->user = $user->fetch();
	}
	
	public function handleUnactiveActive($userId){
		$database = $this->context->database;
		$database->table('Users')->where('Id', $userId)->update(array(
			"IsApproved" => 1
		));
		$this->flashMessage('Uživatelský účet byl aktivován!', 'ok');
		$this->redirect('Admin:users');
	}
}
