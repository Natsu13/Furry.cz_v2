<?php

/**
 * Homepage presenter.
 */
class HomepagePresenter extends BasePresenter
{

	public function renderDefault()
	{
		$cmsUtils = new Fcz\CmsUtilities($this);
		$this->template->homepageCmsHtml = $cmsUtils->getCmsHtml('homepage');
		$this->template->admin = $this->user->isInRole("admin");
		
		$this->template->userList = $this->context->database->table('Users')->order('Id DESC')->where('IsApproved = 1')->limit(9);
	}

}
