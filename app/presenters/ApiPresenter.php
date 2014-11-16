<?php

use Nette\Application\UI;
use Nette\Application\Responses\JsonResponse;
use Nette\Utils\Json;

class ApiPresenter extends BasePresenter
{

	public function renderDefault()
	{
		$data = array(
				"Name" => "Furry.cz API",
				"Version" => "0.1",
				"State" => "Alpha",
				"Codename" => "Cookies",
				"Error" => 0,
				"Error_text" => ""
			);
		
		$this->sendResponse(new JsonResponse($data));
	}
	
	public function renderForum(){
		$data = null;		
		$database = $this->context->database;
		
		$users = $database->table('Users');
		foreach($users as $user)
		{
			$allUserWithInfo[$user["Id"]] = array($user["Nickname"], $user["AvatarFilename"]);
		}
		
		$categories = $database->table('TopicCategories')->select('Id, Name');
		$cate = null;
		foreach($categories as $cat)
		{
			$cate[$cat["Id"]] = $cate[$cat["Name"]];
		}
		$categories = $cate;
		
		$topics = $database->query('SELECT Topics.* FROM Topics LEFT JOIN Posts on Topics.ContentId = Posts.ContentId JOIN Content on Topics.ContentId = Content.Id GROUP BY Topics.ContentId ORDER BY Topics.Pin DESC, CASE WHEN COUNT( Posts.TimeCreated ) = 0 THEN Content.TimeCreated ELSE Posts.TimeCreated END DESC')->fetchAll();		
		$i=0;
		foreach($topics as $topic)
		{
			$content = $database->table('Content')->where('Id = ?', $topic["ContentId"])->fetch();
			$lastPost = $this->context->database->table('Posts')->where("ContentId",$topic['ContentId'])->where("Deleted",0)->order("TimeCreated DESC");
			$lastVisit = $content->related("LastVisits")->where("UserId", $this->user->id)->fetch();
			$clp = count($lastPost);
			
			if($this->user->isInRole('approved')){ if($lastVisit == false){$new=-1;}else{
				$new = count( $this->context->database->table('Posts')->where("TimeCreated > ? AND ContentId = ? AND Deleted = 0",$lastVisit["Time"], $topic['ContentId']) );		
			} }else{$new=0;}
			
			$lastPost = $lastPost->fetch();
			
			if(!(!$this->user->isInRole('approved') and $content['IsForAdultsOnly']) and !(!$this->user->isInRole('approved') and $content['IsForRegisteredOnly'])){
				$data["topics"][] = array(
								"Id" => $topic["Id"],
								"Name" => $topic["Name"],
								"Category" => ($topic['CategoryId'] != null?$categories[$topic['CategoryId']]:"Nezařazeno"),
								"Posts" => $clp,
								"User" => ($clp>0?$allUserWithInfo[$lastPost["Author"]][0]:""),
								"Time" => ($clp>0?Fcz\CmsUtilities::getTimeElapsedString(strtotime($lastPost["TimeCreated"])):Fcz\CmsUtilities::getTimeElapsedString(strtotime($content["TimeCreated"]))),
								"New" => $new
							);
			}
			$i++;				
		}				
		
		$data["categories"] = $categories;
		$data["users"] = $allUserWithInfo;
		
		$this->sendResponse(new JsonResponse($data));
	}
}	