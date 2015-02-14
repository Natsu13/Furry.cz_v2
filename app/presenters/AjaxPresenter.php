<?php

use Nette\Application\UI;
use Nette\Application\Responses\JsonResponse;
use Nette\Utils\Json;

class AjaxPresenter extends BasePresenter
{

	public function renderDefault($name)
	{

	}



	public function renderAutocomplete($id="", $user_="")
    {
		$database = $this->context->database;

		$data = NULL;

		$users = $database->table('Users')->order('Nickname');
		foreach($users as $user)
		{
			$data[] = array("name" => $user["Nickname"], "id" => $user["Id"]);
			if($id!="" and $user["Id"] == $user_){ $data = array("Avatar" => $user["AvatarFilename"],"Name" => $user["Nickname"], "Id" => $user["Id"], "object" => $id); $this->sendResponse(new JsonResponse($data)); return null; }
		}

		if($id!=""){$data = null;}
		$this->sendResponse(new JsonResponse($data));
	}



	public function renderAutocompleteInput($whichData, $typedText = '')
	{
		$database = $this->context->database;

		$data = NULL;

		$users = $database->table('Users')->order('Nickname');
		foreach($users as $user)
		{
			if($this->user->identity->nickname!=$user["Nickname"])
			{
				$data[] = $user["Nickname"];
			}
		}

		$matches = preg_grep("/$typedText/i", $data);
		$this->sendResponse(new JsonResponse($matches));
	}



	public function renderFurrafinity()
	{
		$template = $this->presenter->template;
		$template->setFile(__DIR__ . '/../templates/components/furrafinitylinker.latte');
	}



	public function renderFurrafinityget($url)
	{
		$page = file_get_contents($url);
		$dd = preg_match('/\<u\>(.*)\<\/u\> has elected to make their content available to registered users only\./U',  $page, $pam);
		if(count($pam)>0)
		{
			$data = array("Error" => "<b>".$pam[1]."</b> nastavil tento obrázek jako soukromý!");
		}
		else
		{
			$dd = preg_match('/full_url  \= \"\/\/(.*)\"\;/U', $page, $mam);
			$img = explode("/",$mam[1]);
			$img = end($img);
	
			$fields = array(
				'URL' => urlencode("http://".$mam[1])
			);
			$fields_string="";
			foreach($fields as $key=>$value)
			{
				$fields_string .= $key.'='.$value.'&';
			}
			rtrim($fields_string, '&');
	
			$cSession = curl_init(); 
			curl_setopt($cSession, CURLOPT_URL,"http://www.katedrala.cz/anonym/nph-agent.cgi/011110A/k-cebkl:/=2ffgneg");
			curl_setopt($cSession, CURLOPT_POST, count($fields));
			curl_setopt($cSession, CURLOPT_POSTFIELDS, $fields_string);
			curl_setopt($cSession, CURLOPT_RETURNTRANSFER,true);
			curl_setopt($cSession, CURLOPT_HEADER, true); 
			$result = curl_exec($cSession);
			$last_url = curl_getinfo($cSession, CURLINFO_EFFECTIVE_URL);
			curl_close($cSession);
			$result = explode(":",$result);
			$res = $result[4];
			$i = 5;
			while(isset($result[$i]))
			{
				$res.=":".$result[$i];
				$i++;
			}
			$res = trim($res);
			$data = array("Error" => 0, "urlImage" => $res, "urlDefault" => $url);
		}
		$this->sendResponse(new JsonResponse($data));
	}



	public function renderNotificationcount()
	{
		$database = $this->context->database;
		$data = array("Notif" => 0, "Count" => 0);
		
		$users = $database->table('Users')->order('Nickname');
		foreach($users as $user)
		{
			if($this->user->identity->nickname!=$user["Nickname"])
			{
				$allUsers[] = array($user["Id"],$user["Username"]);
			}
			$allUserId[$user["Id"]] = $user["Username"];
			$allUserName[$user["Username"]] = $user["Id"];
			$allUserWithInfo[$user["Id"]] = array($user["Nickname"], $user["AvatarFilename"]);
		}
		
		$messages = $database->table('PrivateMessages')->where("AddresseeId = ? AND Read = 0 AND Deleted=0",$this->user->identity->id);
		foreach($messages as $message)
		{
			if($message["SenderId"] == $this->user->identity->id)
			{
				$name_ = $allUserId[$message["AddresseeId"]];
				$id    = $message["AddresseeId"];
				$SID   = $message["AddresseeId"].$message["SenderId"];
			}
			else
			{
				$name_ = $allUserId[$message["SenderId"]];
				$id    = $message["SenderId"];
				$SID   = $message["SenderId"].$message["AddresseeId"];
			}

			if(!isset($msgFrom[$SID]))
			{
				$notif = $database->table('Notifications')->where("Parent = ? AND IsView = 0","chat_".$message["Id"]);
				if(count($notif)!=0)
				{
					$msgFrom[$SID]=1;
					$data["Count"]++;
				}
			}
		}
		if($this->presenter->user->isInRole('admin')){
			$nottifications = $database->table('Notifications')->where("(UserId = ? OR UserId = -4) AND IsNotifed = 0 AND Parent NOT LIKE 'chat_%'",$this->user->identity->id);
		}else{
			$nottifications = $database->table('Notifications')->where("UserId = ? AND IsNotifed = 0 AND Parent NOT LIKE 'chat_%'",$this->user->identity->id);
		}
		//$nottifications = $database->table('Notifications')->where("UserId = ? AND IsNotifed = 0", $this->user->identity->id);
		foreach($nottifications as $nottification)
		{
			$data["Notif"]++;
		}
		
		$this->sendResponse(new JsonResponse($data));
	}



	public function renderNotificationnotif($time)
	{
		$database = $this->context->database;
		$data = array("0" => array("time" => time()));
		
		$users = $database->table('Users')->order('Nickname');
		foreach($users as $user)
		{
			if($this->user->identity->nickname!=$user["Nickname"])
			{
				$allUsers[] = array($user["Id"],$user["Username"]);
			}
			$allUserId[$user["Id"]] = $user["Username"];
			$allUserName[$user["Username"]] = $user["Id"];
			$allUserWithInfo[$user["Id"]] = array($user["Nickname"], $user["AvatarFilename"]);
		}
		
		$messages = $database->table('PrivateMessages')->where("AddresseeId = ? AND Read = 0 AND Deleted=0",$this->user->identity->id);
		foreach($messages as $message)
		{
			$notif = $database->table('Notifications')->where("Parent = ?","chat_".$message["Id"]);		
			if(count($notif)==0)
			{
				$text = strip_tags($message["Text"]);
				$pext = substr($text,0,67);
				if($pext != $text)
				{
					$text = $pext."...";
				}
				$data[] = array(
					"Text"  => "<b>".$allUserId[$message["SenderId"]]."</b> ti posílá zprávu:<br>".$text,
					"Info"  => Fcz\CmsUtilities::getTimeElapsedString(strtotime($message["TimeSent"])),
					"Href"  => $this->link("Intercom:default",$allUserId[$message["SenderId"]]),
					"Image" => $allUserWithInfo[$message["SenderId"]][1],
				);
				$database->table('Notifications')->insert(array(
					"Parent"    => "chat_".$message["Id"],
					"Time"      => date("Y-m-d H:i:s",time()),
					"IsNotifed" => 1,
					"IsView"    => 0,
					"UserId"    => $this->user->identity->id
				));
			}
		}

		$this->sendResponse(new JsonResponse($data));
	}



	public function renderNotifications($jak)
	{
		$database = $this->context->database;
		$data = array("length" => 0);
		
		$users = $database->table('Users')->order('Nickname');
		foreach($users as $user)
		{
			if($this->user->identity->nickname!=$user["Nickname"])
			{
				$allUsers[] = array($user["Id"],$user["Username"]);
			}
			$allUserId[$user["Id"]] = $user["Username"];
			$allUserName[$user["Username"]] = $user["Id"];
			if($user["AvatarFilename"]=="")
			{
				$user["AvatarFilename"]="0.jpg";
			}
			$allUserWithInfo[$user["Id"]] = array($user["Nickname"], $user["AvatarFilename"]);
		}

		if($jak==0)
		{
			$count = 0;
			$messages = $database->table('PrivateMessages')->where("(SenderId = ? OR AddresseeId = ?) AND Deleted=0",$this->user->identity->id, $this->user->identity->id)->order('TimeSent DESC');
			foreach($messages as $message)
			{
				if($message["SenderId"] == $this->user->identity->id)
				{
					$name_ = $allUserId[$message["AddresseeId"]];
					$id    = $message["AddresseeId"];
					$SID   = $message["AddresseeId"].$message["SenderId"];
				}
				else
				{
					$name_=$allUserId[$message["SenderId"]];
					$id=$message["SenderId"];
					$SID = $message["SenderId"].$message["AddresseeId"];
				}

				if(!isset($msgFrom[$SID]))
				{
					$msgFrom[$SID]=1;
					$reply = false;

					if($message["SenderId"] == $this->user->identity->id)
					{
						$read = 1;
						$reply = true;
					}
					else
					{
						$read = $message["Read"];
					}

					$text = strip_tags($message["Text"]);
					$pext = substr($text,0,60);
					if($pext != $text)
					{
						$text = $pext."...";
					}

					$data[] = array(
						"Url"   => $this->link("Intercom:default",$allUserId[$id]),
						"Class" => "Read_".$read, 
						"Id"    => $id,
						"Image" => ($this->getHttpRequest()->url->baseUrl)."/images/avatars/".$allUserWithInfo[$id][1], 
						"Info"  => Fcz\CmsUtilities::getTimeElapsedString(strtotime($message["TimeSent"]))."".($read==0?" <b style='color:red;'>NOVÉ!</b>":""),
						"Text"  => "<div style='font-weight:bold;'>".$allUserWithInfo[$id][0]."</div><div style='height:19px;overflow: hidden;text-overflow: ellipsis;white-space: nowrap;'>".($reply?"<img src='".($this->getHttpRequest()->url->baseUrl)."/images/reply.png' style='height: 11px;width: 11px;padding-top: 2px;'>":"")."".$text."</div>"
						);
					$count++;
					
					$notif = $database->table('Notifications')->where("Parent = ?","chat_".$message["Id"])->update(array("IsView" => 1));		
				}
			}
			$data["length"] = $count;
		}else{
			if($this->presenter->user->isInRole('admin')){
				$notifications = $database->table('Notifications')->where("(UserId = ? OR UserId = -4) AND Parent NOT LIKE 'chat_%'",$this->user->identity->id)->limit(20)->order('Time DESC');
			}else{
				$notifications = $database->table('Notifications')->where("UserId = ? AND Parent NOT LIKE 'chat_%'",$this->user->identity->id)->limit(20)->order('Time DESC');
			}
			$count = 0;
			foreach($notifications as $notif)
			{
				$dat = explode("_", $notif["Parent"]);
				$typ = $dat[0];
				$arg = $dat[1];
				if($typ=="topic"){
					if($notif["IsView"] == 0){$read = 0;}else{$read = 1;}
					
					$topic = $database->table('Topics')->where("Id = ?",$arg)->fetch();
										
					$prispevatele = Json::decode($notif["Text"]);
					if(count($prispevatele) == 1){
						$text = "Tvor <b>".$allUserWithInfo[$prispevatele[0]][0]."</b> přidal";
					}else{
						$text = "<b>".$allUserWithInfo[$prispevatele[0]][0]."</b>";
						if(count($prispevatele)<3){
							for($u=1;$u<count($prispevatele);$u++){
								if(count($prispevatele)-1 == $u){ $text.=" a "; }else{ $text.=", "; }
								$text.="<b>".$allUserWithInfo[$prispevatele[$u]][0]."</b>";
							}
						}else{
							for($u=1;$u<2;$u++){
								$text.=", <b>".$allUserWithInfo[$prispevatele[$u]][0]."</b>";
							}
							$text.=" a další(".(count($prispevatele)-2).")";
						}
						$text.=" přidali";
					}
					$text.=" nový příspěvek do tématu „<b>".$topic["Name"]."</b>“.";
					
					$data[] = array(
						"Url"   => $notif["Href"],
						"Class" => "Read_".$read, 
						"Id"    => 0,
						"Image" => $notif["Image"], 
						"Info"  => "<img src='".($this->presenter->context->httpRequest->url->baseUrl)."/images/star.png' style='height: 12px;width: 12px;'> ".Fcz\CmsUtilities::getTimeElapsedString(strtotime($notif["Time"])),
						"Text"  => $text
						);
					$count++;
					$notif = $database->table('Notifications')->where("Id = ?",$notif["Id"])->update(array("IsView" => 1, "IsNotifed" => 1));		
				}
				else if($typ=="eventAdded"){
					if($notif["IsView"] == 0){$read = 0;}else{$read = 1;}
					
					$event = $database->table('Events')->where("Id = ?",$arg)->fetch();
										
					$prispevatele = Json::decode($notif["Text"]);
					if(count($prispevatele) == 1){
						$text = "Tvor <b>".$allUserWithInfo[$prispevatele[0]][0]."</b> se přidal";
					}else{
						$text = "<b>".$allUserWithInfo[$prispevatele[0]][0]."</b>";
						if(count($prispevatele)<3){
							for($u=1;$u<count($prispevatele);$u++){
								if(count($prispevatele)-1 == $u){ $text.=" a "; }else{ $text.=", "; }
								$text.="<b>".$allUserWithInfo[$prispevatele[$u]][0]."</b>";
							}
						}else{
							for($u=1;$u<2;$u++){
								$text.=", <b>".$allUserWithInfo[$prispevatele[$u]][0]."</b>";
							}
							$text.=" a další(".(count($prispevatele)-2).")";
						}
						$text.=" se přidali";
					}
					$text.=" na tvou událost „<b>".$event["Name"]."</b>“.";
					
					$data[] = array(
						"Url"   => $notif["Href"],
						"Class" => "Read_".$read, 
						"Id"    => 0,
						"Image" => $notif["Image"], 
						"Info"  => "<img src='".($this->presenter->context->httpRequest->url->baseUrl)."/images/event.png' style='height: 12px;width: 12px;'> ".Fcz\CmsUtilities::getTimeElapsedString(strtotime($notif["Time"])),
						"Text"  => $text
						);
					$count++;
					$notif = $database->table('Notifications')->where("Id = ?",$notif["Id"])->update(array("IsView" => 1, "IsNotifed" => 1));		
				}
				else if($typ=="welcome"){
					if($notif["IsView"] == 0){$read = 0;}else{$read = 1;}
					$data[] = array(
						"Url"   => $this->presenter->link("CmsPage:showPage","Welcome"),
						"Class" => "Read_".$read, 
						"Id"    => 0,
						"Image" => ($this->presenter->context->httpRequest->url->baseUrl)."/images/avatars/system.jpg", 
						"Info"  => "<img src='".($this->presenter->context->httpRequest->url->baseUrl)."/images/furrycz.ico' style='height: 12px;width: 12px;'> ".Fcz\CmsUtilities::getTimeElapsedString(strtotime($notif["Time"])),
						"Text"  => "Vítej na furry.cz prosím přečti si pravidla správného chování na tomto webu."
						);
					$count++;	
					$notif = $database->table('Notifications')->where("Id = ?",$notif["Id"])->update(array("IsView" => 1, "IsNotifed" => 1));	
				}
				else if($typ=="activationRequest"){
					if($notif["IsView"] == 0){$read = 0;}else{$read = 1;}
					
					$prispevatele = Json::decode($notif["Text"]);
					if(count($prispevatele) == 1){
						$text = "Nový tvor <b>".$allUserWithInfo[$prispevatele[0]][0]."</b> se zaregistroval a čeká";
					}else{
						$text = "<b>".$allUserWithInfo[$prispevatele[0]][0]."</b>";
						if(count($prispevatele)<3){
							for($u=1;$u<count($prispevatele);$u++){
								if(count($prispevatele)-1 == $u){ $text.=" a "; }else{ $text.=", "; }
								$text.="<b>".$allUserWithInfo[$prispevatele[$u]][0]."</b>";
							}
						}else{
							for($u=1;$u<2;$u++){
								$text.=", <b>".$allUserWithInfo[$prispevatele[$u]][0]."</b>";
							}
							$text.=" a další(".(count($prispevatele)-2).")";
						}
						$text.=" se zaregistrovaly a čekají";
					}
					$text.=" na schválení účtu.";
					
					$data[] = array(
						"Url"   => $this->presenter->link("Admin:users"),
						"Class" => "Read_".$read, 
						"Id"    => 0,
						"Image" => ($this->presenter->context->httpRequest->url->baseUrl)."/images/avatars/system.jpg", 
						"Info"  => "<img src='".($this->presenter->context->httpRequest->url->baseUrl)."/images/furrycz.ico' style='height: 12px;width: 12px;'> ".Fcz\CmsUtilities::getTimeElapsedString(strtotime($notif["Time"]))." (ADMIN)",
						"Text"  => $text
						);
					$count++;	
					$notif = $database->table('Notifications')->where("Id = ?",$notif["Id"])->update(array("IsView" => 1, "IsNotifed" => 1));	
				}
			}
			$data["length"] = $count;
		}
		
		$this->sendResponse(new JsonResponse($data));
	}



	public function renderRatepost($PostId, $ContentId, $Rating)
	{
		$database = $this->context->database;
		$data = array();
		
		//Kontrola kdyby chtěl někdo podvádět ^^
		if($Rating>1 or $Rating<-1)
		{
			$Rating=0;
		}
		
		$ratTop = 0;
		$mam=0;
		$rating = $database->table('RatingsPost')->Where("PostId = ?", $PostId);
		foreach($rating as $rat)
		{
			if($rat["UserId"] == $this->user->identity->id)
			{
				if($rat["Rating"] == $Rating)
				{
					$rat["Rating"]=0;
				}
				else
				{
					$rat["Rating"] = $Rating;
				}
				$database->table('RatingsPost')->where("PostId = ? AND UserId = ?",$rat["PostId"],$this->user->identity->id)->update(array("Rating" => $rat["Rating"]));
				$mam=1;
			}
			$ratTop += $rat["Rating"];
		}

		if($mam==0)
		{
			$database->table('RatingsPost')->insert(array(
				"ContentId" => $ContentId,
				"PostId"    => $PostId,
				"UserId"    => $this->user->identity->id,
				"Rating"    => $Rating
			));
			$ratTop += $Rating;
		}
		
		if($ratTop<0)
		{
			$c="Red";
		}
		elseif($ratTop>0)
		{
			$c="Green";
		}
		else
		{
			$c="Orange";
		}
		$data = array("Class" => $c, "Rating" => $ratTop, "PostId" => $PostId);
		
		$this->sendResponse(new JsonResponse($data));
	}
	
	public function renderBlock(){
		$database = $this->context->database;
		
		$userUt = new Fcz\UserUtilities($this);
		$allUserWithInfo = $userUt->getAllUsers();
		
		$data = null;
		$rating = $database->table('Ignorelist')->where("IgnoredUserId = ?", $this->user->id);
		foreach($rating as $rat)
		{
			if($rat["IgnoreType"] == 1){$type = "Blokování intercomu";}else{$type = "Neznámé zablokování";}
			$data["users"][] = array(
				"Name"     => $allUserWithInfo[$rat["IgnoringUserId"]][0],
				"Avatar"   => $allUserWithInfo[$rat["IgnoringUserId"]][1],
				"Id"       => $rat["IgnoringUserId"],
				"Username" => $allUserWithInfo[$rat["IgnoringUserId"]][3],
				"Type"     => $type
			);
		}
		
		$this->sendResponse(new JsonResponse($data));
	}

	public function renderAttendanceschange($EventId, $Attendances = "Maybe")
	{
		$database = $this->context->database;
		$data = array("Id"=>$Attendances);

		$uca = $database->table('EventAttendances')->where('EventId', $EventId)->where('UserId', $this->user->id)->fetch();
		if($uca == false)
		{
			if($Attendances!="No")
				$this->presenter->getContentManager()->notifiEventAddUser($EventId);
			$database->table('EventAttendances')->insert(array(
				'EventId' => $EventId,
				'UserId' => $this->user->id,
				"Attending" => $Attendances
			));
		}
		else
		{
			$database->table('EventAttendances')->where('EventId', $EventId)->where('UserId', $this->user->id)->update(array(
				"Attending" => $Attendances
			));
		}

		$this->sendResponse(new JsonResponse($data));
	}



	public function renderGethostevent($Data)
	{
		$database = $this->context->database;
		$data = array("EventId"=>$Data);
		
		$userUt = new Fcz\UserUtilities($this);
		$allUserWithInfo = $userUt->getAllUsers();
		
		$data["width"]   = 170;
		$data["title"]   = "Seznam pozvaných";
		$data["sekcion"] = array("Učastní se","Možná se účastní","Pozvaní","-","Odmítnuto");
		
		$ucastnici = $database->table('EventAttendances')->where('EventId', $Data);
		foreach($ucastnici as $ucastnik)
		{
			if($ucastnik["Attending"]=="Yes")
			{
				$u=0;
			}
			elseif($ucastnik["Attending"]=="No")
			{
				$u=4;
			}
			else
			{
				$u=1;
			}
			$data["users"][$u][] = array(
				"Name"     => $allUserWithInfo[$ucastnik["UserId"]][0],
				"Avatar"   => $allUserWithInfo[$ucastnik["UserId"]][1],
				"Id"       => $ucastnik["UserId"],
				"Username" => $allUserWithInfo[$ucastnik["UserId"]][3]
			);
		}
		$this->sendResponse(new JsonResponse($data));
	}



	public function renderGetpostlike($Data)
	{
		$database = $this->context->database;
		$data = array("PostId"=>$Data);
		
		$data["width"] = 170;
		$data["title"] = "Hodnocení příspěvku v tématu";
		$data["sekcion"] = array("Líbí se","Nelíbí se");
		
		$userUt = new Fcz\UserUtilities($this);
		$allUserWithInfo = $userUt->getAllUsers();
		
		$likes = $database->table('RatingsPost')->where('PostId', $Data);
		foreach($likes as $like)
		{
			$u=($like["Rating"]==1?$u=0:$u=1);
			$data["users"][$u][] = array(
				"Name"     => $allUserWithInfo[$like["UserId"]][0],
				"Avatar"   => $allUserWithInfo[$like["UserId"]][1],
				"Id"       => $like["UserId"],
				"Username" => $allUserWithInfo[$like["UserId"]][3]
			);
		}
		
		$this->sendResponse(new JsonResponse($data));
	}
}
