<?php

namespace Fcz
{

use Nette\Application\UI;
use \Nette\Diagnostics\Debugger;
use \Nette\Utils\Json;
use \Nette\DateTime;

class ContentManager extends \Nette\Object
{

	private $presenter = null;
	
	
	
	public function __construct(\Nette\Application\UI\Presenter $presenter)
	{
		$this->presenter = $presenter;
	}
	
	
	
	public function deleteContent(\Nette\Database\ActiveRow $content)
	{
		$content->related("Ownership")->delete();
		$content->related("Permissions")->delete();
		$content->related("Posts")->delete();
		foreach ($content->related("Polls") as $poll)
		{
			$this->deletePoll($poll);
		}
		$content->delete();
	}
	
	
	
	public function deletePoll(\Nette\Database\ActiveRow $poll)
	{
		$poll->related("PollAnswers")->delete();
		$poll->related("PollVotes")->delete();
		$poll->delete();
	}
	
	
	
	/**
	* Deletes image from gallery.
	*/
	public function deleteImage(\Nette\Database\ActiveRow $image, $deleteFile = true)
	{	
		if ($deleteFile == true)
		{
			$this->getUploadHandler->deleteUploadedFile($image["UploadedFileId"]);
		}
		$this->deleteContent($image->ref("ContentId")->fetch());
		$image->delete();
	}

	//adding deleting and updating book is here!
	public function bookSet($topicId, $userId, $categoryId = null, $deleteIfExists = false)
	{
		$database = $this->presenter->context->database;
		$book = $database->table("Bookmarks")->where("TopicId = ? AND UserId = ?", $topicId, $userId)->fetch();
		if ($book !== false)
		{
			if($deleteIfExists)
				$book->delete();
			else
				$book->update( array("CategoryId" => $categoryId) );
		}
		else
		{
			$database->table("Bookmarks")->insert(array( "TopicId" => $topicId, "UserId" => $userId, "CategoryId" => $categoryId ));
		}
	}
	//is booked?
	public function bookIs($topicId, $userId){
		$database = $this->presenter->context->database;
		$book = $database->table("Bookmarks")->where("TopicId = ? AND UserId = ?", $topicId, $userId)->fetch();
		if ($book !== false){
			return true;
		}else{
			return false;
		}
	}
	
	
	/*
	Notification control in PHP
	*/
	public function notifiDiscusionNewPost($topicId){
		
		$database = $this->presenter->context->database;
		$bookHave = $database->table("Bookmarks")->where("TopicId = ?", $topicId);
		
		foreach($bookHave as $bookUser){
	
			$text = null;
			$bookUserId = $bookUser["UserId"];	
			if($bookUserId != $this->presenter->user->identity->id){
				$NotifExists = $database->table("Notifications")->where("Parent = ? AND UserId = ?", "topic_".$topicId, $bookUserId)->order("Time DESC")->fetch();
				
				if($NotifExists !== false and (time() - ($NotifExists["Time"]->getTimestamp())) < 3600 ){ //Pokud bude notifikace starší jak jedna hodina tak i přes to vytvořím novou.
					if($NotifExists["IsView"] == 0){
						$text = Json::decode($NotifExists["Text"]);
						if(in_array($this->presenter->user->identity->id, $text)){
							$text = array_diff($text, array($this->presenter->user->identity->id));
						}
						array_unshift($text, $this->presenter->user->identity->id);
					}else{					
						$text[] = $this->presenter->user->identity->id;
					}
					
					$database->table('Notifications')->where("Id", $NotifExists["Id"])->update(array(
							"Time"      => date("Y-m-d H:i:s",time()),
							"IsNotifed" => 0,
							"IsView"    => 0,
							"Href"		=> $this->presenter->link("Forum:topic",$topicId),
							"Image"		=> ($this->presenter->context->httpRequest->url->baseUrl)."/images/avatars/".$this->presenter->user->identity->avatarFilename,
							"Text"		=> Json::encode($text)
						));
				}else{
					$text[] = $this->presenter->user->identity->id;
					
					$database->table('Notifications')->insert(array(
							"Parent"    => "topic_".$topicId,
							"Time"      => date("Y-m-d H:i:s",time()),
							"IsNotifed" => 0,
							"IsView"    => 0,
							"UserId"    => $bookUserId,
							"Href"		=> $this->presenter->link("Forum:topic",$topicId),
							"Image"		=> ($this->presenter->context->httpRequest->url->baseUrl)."/images/avatars/".$this->presenter->user->identity->avatarFilename,
							"Text"		=> Json::encode($text)
						));
				}						
			}
		}
	}
	public function notifiEventAddUser($EventId){
		$database = $this->presenter->context->database;
		$event = $database->table('Events')->where('Id', $EventId)->fetch();
		$content = $database->table('Ownership')->where('ContentId', $event["ContentId"])->fetch();
		
		$NotifExists = $database->table("Notifications")->where("Parent = ? AND UserId = ?", "eventAdded_".$EventId, $content["UserId"])->order("Time DESC")->fetch();
				$text = null;
				
				if($NotifExists !== false and (time() - ($NotifExists["Time"]->getTimestamp())) < 3600 ){ //Pokud bude notifikace starší jak jedna hodina tak i přes to vytvořím novou.
					if($NotifExists["IsView"] == 0){
						$text = Json::decode($NotifExists["Text"]);
						if(in_array($this->presenter->user->identity->id, $text)){
							$text = array_diff($text, array($this->presenter->user->identity->id));
						}
						array_unshift($text, $this->presenter->user->identity->id);
					}else{					
						$text[] = $this->presenter->user->identity->id;
					}
					
					$database->table('Notifications')->where("Id", $NotifExists["Id"])->update(array(
							"Time"      => date("Y-m-d H:i:s",time()),
							"IsNotifed" => 0,
							"IsView"    => 0,
							"Href"		=> $this->presenter->link("Events:view",$EventId),
							"Image"		=> ($this->presenter->context->httpRequest->url->baseUrl)."/images/avatars/".$this->presenter->user->identity->avatarFilename,
							"Text"		=> Json::encode($text)
						));
				}else{
					$text[] = $this->presenter->user->identity->id;
					
					$database->table('Notifications')->insert(array(
							"Parent"    => "eventAdded_".$EventId,
							"Time"      => date("Y-m-d H:i:s",time()),
							"IsNotifed" => 0,
							"IsView"    => 0,
							"UserId"    => $content["UserId"],
							"Href"		=> $this->presenter->link("Events:view",$EventId),
							"Image"		=> ($this->presenter->context->httpRequest->url->baseUrl)."/images/avatars/".$this->presenter->user->identity->avatarFilename,
							"Text"		=> Json::encode($text)
						));
				}	
	}
	/* END */


	/**
	* @param int|\Nette\Security\User $user
	*/
	public function updateLastVisit(\Nette\Database\Table\ActiveRow $content, $user, DateTime $time = null)
	{
		if ($user === null)
		{
			return; /* Nothing to do */
		}
		else if ($user instanceof \Nette\Security\User)
		{
			list($approved, $message) = $this->presenter->getAuthorizator()->isAccountApproved($user);
			if (! $approved)
			{
				return; /* Nothing to do */
			}
			else
			{
				$userId = $user->id;
			}
		}
		else if (is_int($user))
		{
			$userId = $user;
		}
		else
		{
			throw new InvalidArgumentException("Invalid parameter #2 `\$user`");
		}
		if ($time === null)
		{
			$time = new \DateTime();
		}
		// Fill last visit
		$database = $this->presenter->context->database;
		$lastVisit = $database
			->table("LastVisits")
			->where("ContentId = ? AND UserId = ?", $content["Id"], $userId)
			->fetch();
		if ($lastVisit !== false)
		{
			$lastVisit->update(array("Time" => $time));
		}
		else
		{
			$database
				->table("LastVisits")
				->insert(array(
					"ContentId" => $content["Id"],
					"UserId" => $userId,
					"Time" => $time
				));
		}
	}



	/**
	* @param        string $contentType
	* @param           int $userId
	* @param DateTime|null $time
	*/
	public function bulkUpdateLastVisit($contentType, $userId, \DateTime $time = null)
	{
		$database = $this->presenter->context->database;
		$contentEntries = $database->table("Content")->where("Type", $contentType);
		if (! $userId)
		{
			throw new InvalidArgumentException("Invalid parameter #2 `\$userId`");
		}
		if ($time === null)
		{
			$time = new \DateTime();
		}

		// Fill last visit entries where missing;
		$lastVisits = $database->table("LastVisits")->where("UserId", $userId);
		$notVisited = $contentEntries->where("Id NOT", $lastVisits->select("ContentId"));
		foreach ($notVisited as $content)
		{
			$database->table("LastVisits")->insert(array(
				"UserId"    => $userId,
				"ContentId" => $content["Id"],
				"Time"      => $time,
			));
		}

		// Update all last visits
		$lastVisits
			->where("ContentId", $database->table("Content")->select("Id")->where("Type", $contentType))
			->update(array("Time" => $time));
	}

}

} // namespace Fcz
