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
		
		$this->template->userList = $this->context->database->table('Users')->order('Nickname ASC')->where('IsApproved = 1')->limit(10);
	}

}
