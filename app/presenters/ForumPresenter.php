<?php

use Nette\Utils\Html;
use Nette\Diagnostics\Debugger;
use Nette\Database;
use Nette\Application;
use Nette\Application\UI;
use Nette\Application\ForbiddenRequestException;
use Nette\Application\ApplicationException;
use Nette\Application\BadRequestException;

/**
 * Discussion forum presenter
 */
class ForumPresenter extends BasePresenter
{

	/**
	 * Action: Shows a list of forum topics
	 */
	public function renderDefault()
	{
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
		$topicsAll = null;
		$i=0;
		foreach($topics as $topic)
		{
			$postCount[$topic["Id"]]["Count"] = $i; // FIXME: this is definitely wrong, fix as soon as possible.
			$i++;
		}				
		
		$this->template->setParameters(array(
			'categories' => $categories,
			'topics' => $topics,
			'allUserWithInfo' => $allUserWithInfo,
			'database' => $database
		));
	}
	
	public function renderPoll($pollId){
		$database = $this->context->database;
		
		$poll = $database->table('Polls')->where('Id = ?', $pollId)->fetch();
		$ContentId = $poll["ContentId"];
		
		$topic = $database->table('Topics')->where('ContentId', $ContentId)->fetch();
		if ($topic == false)
		{
			throw new Nette\Application\BadRequestException('Zadané téma neexistuje');
		}
		$authorizator = new Authorizator($database);
		$access = $authorizator->authorize($topic["ContentId"], $this->user);
		if ($access['CanReadPosts'] == true )
		{
			$this->template->Name = $poll["Name"];
			$this->template->topicId = $topic["Id"];
			$this->template->Owner = $access['IsOwner'];
			$this->template->Description = $poll["Description"];
			$this->template->MaxVotesPerUser = $poll["MaxVotesPerUser"];
			$this->template->AllowCustomAnswer = $poll["AllowCustomAnswer"];
			$this->template->EditAnswer = $poll["SaveIndividualVotes"];
			
			$this->template->Topic = $topic;
			$this->template->Answer = $database->table('PollAnswers')->where('PollId = ?', $poll["Id"]);
			
			$vote = $database->table('PollVotes')->where('PollId = ?', $poll["Id"]);
			$votr = NULL;
			$hasoval = false;
			
			$answer = $database->table('PollAnswers')->where('PollId = ?', $poll["Id"]);
			foreach($answer as $ans){
				$votr[$ans["Id"]] = array(false, 0);
			}
			
			foreach($vote as $vot){
				if($vot["UserId"] == $this->presenter->user->identity->id){ $votr[$vot["AnswerId"]][0]=true;$hasoval=true; }
				$votr[$vot["AnswerId"]][1]++;
			}
			
			$this->template->Voted = $hasoval;
			$this->template->Vote  = $votr;
			
			$this['votePoll']->setDefaults(
					array(	"TopicId" => $topic["Id"],
							"ContentId" => $topic["ContentId"],
							"PollId" => $pollId
					)
			);		
		}
		else
		{
			throw new Nette\Application\BadRequestException('Nemáš oprávnění ke čtené v tématu "'.$topic["Name"].'" a proto nemůžeš hlasovat v anketě!');
		}
	}
	
	public function createComponentVotePoll()
	{
		$form = new UI\Form;
		$form->addText('vote', 'Hlas');
		$form->addText('UserAnswer', 'Uživatelská udpověď');
		$form->addHidden('TopicId');
		$form->addHidden('ContentId');
		$form->addHidden('PollId');
		$form->onSuccess[] = $this->processValidatedVotePoll;
		$form->addSubmit('SubmitVotePoll', 'Hlasovat');
		return $form;
	}

	public function processValidatedVotePoll($form){
		$values = $form->getValues();
		$database = $this->context->database;
		
		$poll = $database->table('Polls')->where('Id = ?', $values["PollId"])->fetch();
		$ContentId = $poll["ContentId"];
		
		$topic = $database->table('Topics')->where('ContentId', $ContentId)->fetch();
		
		$authorizator = new Authorizator($database);
		$access = $authorizator->authorize($values["ContentId"], $this->user);
		if ($access['CanReadPosts'] == true )
		{
			
			$answerID = $_POST["vote"];
			if(!is_array($answerID)){$answerID = array($answerID);}
			//if($answerID == -1){dump($values["UserAnswer"]);}
			//dump($answerID);
			
			if( count($answerID) > $poll["MaxVotesPerUser"] ){
				$this->flashMessage('Můžeš zvolit pouze '.$poll["MaxVotesPerUser"].' odpovědí!', 'error');
			}else{			
				$database->table('PollVotes')->where('UserId = ? AND PollId = ?', $this->presenter->user->identity->id, $values["PollId"])->delete();
				$values["UserAnswer"] = trim($values["UserAnswer"]);
				
				foreach($answerID as $ans){
					if($ans == -1){
						if(count($database->table('PollAnswers')->where('Text = ?', $values["UserAnswer"])) == 0){
							$content = $database->table('PollAnswers')->insert(array(
								'PollId' => $values["PollId"],
								"Text" => $values["UserAnswer"]
							));
							
							$content = $database->table('PollVotes')->insert(array(
								'PollId' => $values["PollId"],
								"AnswerId" => $database->lastInsertId(),
								"UserId" => $this->presenter->user->identity->id
							));
						}else{ 
							$this->flashMessage('Tato odpověď v anketě již existuje!', 'error');
						}
					}else{
						$content = $database->table('PollVotes')->insert(array(
							'PollId' => $values["PollId"],
							"AnswerId" => $ans,
							"UserId" => $this->presenter->user->identity->id
						));
					}
				}
			}
			
			$this->redirect('Forum:Poll', $poll["Id"]);
		
		}else{ throw new Nette\Application\BadRequestException('Nemáš oprávnění ke čtené v tématu "'.$topic["Name"].'" a proto nemůžeš hlasovat v anketě!'); }
	}
	
	public function renderPolls($topicId){
		$database = $this->context->database;
		$topic = $database->table('Topics')->where('Id', $topicId)->fetch();
		if ($topic == false)
		{
			throw new Nette\Application\BadRequestException('Zadané téma neexistuje');
		}
		$authorizator = new Authorizator($database);
		$access = $authorizator->authorize($topic["ContentId"], $this->user);
		if ($access['IsOwner'] == true or $access['CanEditPolls'] == true )
		{
			$this->template->Name = $topic["Name"];
			$this->template->topicId = $topicId;
			$this->template->Owner = $access['IsOwner'];
			
			$this->template->Polls = $database->table('Polls')->where('ContentId = ? AND ShowInTopic != -1', $topic["ContentId"]);
		}
		else
		{
			throw new Nette\Application\BadRequestException('Nejsi vlastník nebo pověřený správce anket!');
		}
	}
	
	public function renderEditPoll($topicId, $pollId){
		$database = $this->context->database;
		$topic = $database->table('Topics')->where('Id', $topicId)->fetch();
		if ($topic == false)
		{
			throw new Nette\Application\BadRequestException('Zadané téma neexistuje');
		}
		$authorizator = new Authorizator($database);
		$access = $authorizator->authorize($topic["ContentId"], $this->user);
		if ($access['IsOwner'] == true or $access['CanEditPolls'] == true )
		{
			$this->template->Name = $topic["Name"];
			$this->template->topicId = $topicId;
			$this->template->Owner = $access['IsOwner'];
			
			$this->template->Poll = $database->table('Polls')->where('Id = ? AND ShowInTopic != -1', $pollId)->fetch();
			$PollAnswer = $database->table('PollAnswers')->where('PollId = ?', $pollId);
			$this->template->PollAnswer = NULL;
			$i=0;
			foreach($PollAnswer as $Pa){
				$this->template->PollAnswer[$i] = array($Pa["Text"], $Pa["Id"]);
				$i++;
			}
			
			$this['editPoll']->setDefaults(
					array(	"TopicId" => $topicId,
							"ContentId" => $topic["ContentId"],
							"PollId" => $pollId
				));		
		}
		else
		{
			throw new Nette\Application\BadRequestException('Nejsi vlastník nebo pověřený správce anket!');
		}
	}
	
	public function handleShowPoll($topicId, $pollId){
		$database = $this->context->database;
		$topic = $database->table('Topics')->where('Id', $topicId)->fetch();
		if ($topic == false)
		{
			throw new Nette\Application\BadRequestException('Zadané téma neexistuje');
		}
		$authorizator = new Authorizator($database);
		$access = $authorizator->authorize($topic["ContentId"], $this->user);
		
		if ($access['IsOwner'] == true or $access['CanEditPolls'] == true )
		{
			$poll = $database->table('Polls')->where('ContentId = ? AND Id = ?', $topic["ContentId"], $pollId)->fetch();
			if($poll["ShowInTopic"]==1){ $s = 0; }
			else{ $s = 1; }
			$database->table('Polls')->where('ContentId = ? AND Id = ?', $topic["ContentId"], $pollId)->update(array(
				"ShowInTopic" => $s
			));
			$this->redirect('Forum:Polls', $topicId);
		}
		else
		{
			throw new Nette\Application\BadRequestException('Nejsi vlastník nebo pověřený správce anket!');
		}
	}
	
	public function handleDeletePoll($topicId){
		$database = $this->context->database;
		$topic = $database->table('Topics')->where('Id', $topicId)->fetch();
		if ($topic == false)
		{
			throw new Nette\Application\BadRequestException('Zadané téma neexistuje');
		}
		$authorizator = new Authorizator($database);
		$access = $authorizator->authorize($topic["ContentId"], $this->user);
		
		if ($access['IsOwner'] == true or $access['CanEditPolls'] == true )
		{
			$database->table('Polls')->where('ContentId', $topic["ContentId"])->update(array(
				"ShowInTopic" => -1
			));
			$this->redirect('Forum:Polls', $topicId);
		}
		else
		{
			throw new Nette\Application\BadRequestException('Nejsi vlastník nebo pověřený správce anket!');
		}
	}
	
	public function renderNewPoll($topicId){
		$database = $this->context->database;
		$topic = $database->table('Topics')->where('Id', $topicId)->fetch();
		if ($topic == false)
		{
			throw new Nette\Application\BadRequestException('Zadané téma neexistuje');
		}
		$authorizator = new Authorizator($database);
		$access = $authorizator->authorize($topic["ContentId"], $this->user);
		if ($access['IsOwner'] == true or $access['CanEditPolls'] == true )
		{
			$this->template->Name = $topic["Name"];
			$this->template->topicId = $topicId;
			$this->template->Owner = $access['IsOwner'];
			
			$this['newPoll']->setDefaults(
					array(	"TopicId" => $topicId,
							"ContentId" => $topic["ContentId"]
					));		
		}
		else
		{
			throw new Nette\Application\BadRequestException('Nejsi vlastník nebo pověřený správce anket!');
		}
	}
	
	public function createComponentEditPoll()
	{
		$form = new UI\Form;
		$form->addTextArea('Data', 'Data', 2, 5);
		$form->addHidden('TopicId');
		$form->addHidden('ContentId');
		$form->addHidden('PollId');
		//$form->addHidden('Data');
		$form->onSuccess[] = $this->processValidatedEditPoll;
		$form->addSubmit('SubmitEditPoll', 'Editovat anketu');
		return $form;
	}
	
	public function createComponentNewPoll()
	{
		$form = new UI\Form;
		$form->addTextArea('Data', 'Data', 2, 5);
		$form->addHidden('TopicId');
		$form->addHidden('ContentId');
		//$form->addHidden('Data');
		$form->onSuccess[] = $this->processValidatedNewPoll;
		$form->addSubmit('SubmitCreatePoll', 'Vytvořit anketu');
		return $form;
	}
	
	public function processValidatedEditPoll($form){
		$values = $form->getValues();
		$database = $this->context->database;
		
		$authorizator = new Authorizator($database);
		$access = $authorizator->authorize($values["ContentId"], $this->user);
		if ($access['IsOwner'] == true or $access['CanEditPolls'] == true )
		{
		
			$data = json_decode($values["Data"],true);
			
			if($data["votes_show"] == 3){ $sv = "Noboty"; }
			else if($data["votes_show"] == 2){ $sv = "OtherVoters"; }
			else{ $sv = "Everybody"; }
			
			$content = $database->table('Polls')->where("Id", $values["PollId"])->update(array(
				'ContentId' => $values["ContentId"],
				"Name" => $data["nazev"],
				"Description" => $data["description"],
				"MaxVotesPerUser" => $data["max_answer"],
				"SaveIndividualVotes" => $data["save_vote"],
				"DisplayVotersTo" => $sv,
				"AllowCustomAnswer" => $data["custom"]
			));	
			
			$idPoll = $values["PollId"];
			
			$i=0;
			foreach($data["poll_answer"] as $name){
				if($data["poll_id"][$i] != ""){
					if($name == ""){
						$database->table('PollVotes')->where('AnswerId = ? AND PollId = ?', $data["poll_id"][$i], $values["PollId"])->delete();
						$content = $database->table('PollAnswers')->where("Id", $data["poll_id"][$i])->delete();						
					}else{
						$content = $database->table('PollAnswers')->where("Id", $data["poll_id"][$i])->update(array(
							"Text" => $name
						));
					}
				}else if($name!=""){
					$content = $database->table('PollAnswers')->insert(array(
						'PollId' => $idPoll,
						"Text" => $name
					));
				}
				$i++;
			}
			
			$this->redirect('Forum:EditPoll', $values["TopicId"], $idPoll);
		
		}else{ throw new Nette\Application\BadRequestException('Nejsi vlastník nebo pověřený správce anket!'); }
	}
	
	public function processValidatedNewPoll($form){
		$values = $form->getValues();
		$database = $this->context->database;
		
		$authorizator = new Authorizator($database);
		$access = $authorizator->authorize($values["ContentId"], $this->user);
		if ($access['IsOwner'] == true or $access['CanEditPolls'] == true )
		{
		
			$data = json_decode($values["Data"],true);
			
			if($data["votes_show"] == 3){ $sv = "Noboty"; }
			else if($data["votes_show"] == 2){ $sv = "OtherVoters"; }
			else{ $sv = "Everybody"; }
			
			$content = $database->table('Polls')->insert(array(
				'ContentId' => $values["ContentId"],
				"Name" => $data["nazev"],
				"Description" => $data["description"],
				"MaxVotesPerUser" => $data["max_answer"],
				"SaveIndividualVotes" => $data["save_vote"],
				"DisplayVotersTo" => $sv,
				"AllowCustomAnswer" => $data["custom"],
				"ShowInTopic" => 1
			));	
			
			$idPoll = $database->lastInsertId();
			
			foreach($data["poll_answer"] as $name){
				if($name!=""){
					$content = $database->table('PollAnswers')->insert(array(
						'PollId' => $idPoll,
						"Text" => $name
					));
				}
			}
			
			$this->redirect('Forum:topic', $values["TopicId"]);
		
		}else{ throw new Nette\Application\BadRequestException('Nejsi vlastník nebo pověřený správce anket!'); }
	}


	/**
	* Action: Cretates new topic
	*/
	public function renderNewTopic()
	{
		if (! $this->user->isInRole("approved"))
		{
			throw new BadRequestException("Nemáte oprávnění");
		}
	}
	
	public function handleBook($topicId){
		if (!$this->user->isInRole("approved"))
		{
			throw new BadRequestException("Nemáte oprávnění");
		}
		
		$this->getContentManager()->bookSet($topicId, $this->user->id, null, true);
		$this->redirect('Forum:topic', $topicId);
	}

	public function handleLock($topicId){
		$topicId = $this->getParameter("topicId");
		
		$database = $this->context->database;
		list($topic, $content, $access) = $this->checkTopicAccess($topicId, $this->user);				
		
		if ($this->user->isInRole("admin"))
		{
			if($topic["Lock"]==1){ $lock = 0; }
			else{ $lock = 1; }
			$database->table('Topics')->where('Id', $topicId)->update(array(
				"Lock" => $lock
			));
			$this->redirect('Forum:topic', $topicId);
		}
		else
		{
			throw new Nette\Application\BadRequestException('Nejsi administrátor!');
		}
	}
	
	public function handlePin($topicId){
		$topicId = $this->getParameter("topicId");
		
		$database = $this->context->database;
		list($topic, $content, $access) = $this->checkTopicAccess($topicId, $this->user);				
		
		if ($this->user->isInRole("admin"))
		{
			if($topic["Pin"]==1){ $pin = 0; }
			else{ $pin = 1; }
			$database->table('Topics')->where('Id', $topicId)->update(array(
				"Pin" => $pin
			));
			$this->redirect('Forum:topic', $topicId);
		}
		else
		{
			throw new Nette\Application\BadRequestException('Nejsi administrátor!');
		}
	}

	public function renderEdit($topicId)
	{
		$database = $this->context->database;
		list($topic, $content, $access) = $this->checkTopicAccess($topicId, $this->user);

		if ($access['IsOwner'] == true or $access['CanEditContentAndAttributes'] == true )
		{
			$this->template->Name    = $topic["Name"];
			$this->template->topicId = $topicId;
			$this->template->Owner   = $access['IsOwner'];
			if($topic["CategoryId"] == NULL)
			{
				$topic["CategoryId"]=0;
			}

			$this['editTopicForm']->setDefaults(array(
				"Name"                => $topic["Name"],
				"IsForRegisteredOnly" => $content["IsForRegisteredOnly"],
				"IsForAdultsOnly"     => $content["IsForAdultsOnly"],
				"IsFlame"             => $topic["IsFlame"],
				"Category"            => $topic["CategoryId"],
				"TopicId"             => $topicId,
				"ContentId"           => $topic["ContentId"]
			));

			$this['ownerTopicForm']->setDefaults(array(
				"TopicId"   => $topicId,
				"ContentId" => $content["Id"]
			));
			
			$this['deleteTopicForm']->setDefaults(array(
				"TopicId"   => $topicId,
				"ContentId" => $content["Id"]
			));
		}
		else
		{
			throw new Nette\Application\BadRequestException('Nejsi vlastník nebo pověřený správce topicu!');
		}
	}



	public function createComponentDeleteTopicForm()
	{
		$form = new UI\Form;
		$form->addHidden('TopicId');
		$form->addHidden('ContentId');
		$form->onSuccess[] = $this->processValidatedDeleteTopicForm;
		$form->addSubmit('SubmitDeleteTopic', 'Smazat toto téma');
		$form->addSubmit('SubmitDeleteSetTopic', 'Označit ke smazání');
		return $form;
	}



	public function createComponentOwnerTopicForm()
	{
		$form = new UI\Form;
		$form->addHidden('OwnerId');
		$form->addHidden('TopicId');
		$form->addHidden('ContentId');
		$form->onSuccess[] = $this->processValidatedOwnerTopicForm;
		$form->addSubmit('SubmitOwnerTopic', 'Předat práva');
		return $form;
	}

	public function processValidatedDeleteTopicForm($form)
	{
		$values = $form->getValues();
		$database = $this->context->database;
		
		$topicId = $this->getParameter("topicId");
		list($topic, $content, $access) = $this->checkTopicAccess($topicId, $this->user);
		
		if ( ( $access['IsOwner'] == true or $this->user->isInRole("admin") ) and isset($_POST["SubmitDeleteSetTopic"]))
		{
			$database->table('Topics')->where('ContentId', $values["ContentId"])->update(array(
				"Delete" => ($topic["Delete"]==1?0:1)
			));
			$this->redirect('Forum:topic', $topicId);
		}
		else if ($this->user->isInRole("admin") and isset($_POST["SubmitDeleteTopic"]))
		{
			$database->table('Content')->where('Id', $values["ContentId"])->update(array(
				"Deleted" => 1
			));
			$this->redirect('Forum:default');
		}
		else
		{
			throw new Nette\Application\BadRequestException('Nejsi vlastník topicku!');
		}
	}

	public function processValidatedOwnerTopicForm($form)
	{
		$values = $form->getValues();
		$database = $this->context->database;
		
		$topicId = $this->getParameter("topicId");
		list($topic, $content, $access) = $this->checkTopicAccess($topicId, $this->user);

		if ($access['IsOwner'] == true )
		{
			$database->table('Ownership')->where('ContentId', $values["ContentId"])->update(array(
				"UserId" => $values["OwnerId"]
			));
			$this->redirect('Forum:topic', $values["TopicId"]);
		}
		else
		{
			throw new Nette\Application\BadRequestException('Nejsi vlastník topicu!');
		}
	}



	public function createComponentEditTopicForm()
	{
		// Check access
		if (!($this->user->isInRole('member') || $this->user->isInRole('admin')))
		{
			throw new Nette\Application\ForbiddenRequestException(
				'Pouze registrovaní uživatelé mohou editovat diskusní témata');
		}

		$form = new UI\Form;

		// Topic name
		$form->addText('Name', 'Název * :')
			->setRequired('Je nutné zadat název tématu')
			->getControlPrototype()->class = 'Wide';

		// Flags
		$form->addCheckbox('IsForRegisteredOnly', 'Jen pro registrované')->setValue(false);
		$form->addCheckbox('IsForAdultsOnly', '18+')->setValue(false);
		$form->addCheckbox('IsFlame', 'Flamewar')->setValue(false);

		// Category
		$categories = $this->context->database->table('TopicCategories');
		$radioList = array('0' => 'Žádná');
		foreach ($categories as $category)
		{
			$p = Html::el('p');
			$p->class = 'ForumCategoryRadioItem';
			$p->add(Html::el('strong')->text($category['Name']));
			$p->add($category['Description']);
			$radioList[$category['Id']] = $p;
		}
		$form->addRadioList('Category', 'Sekce:', $radioList)->setValue(0);
		// Submit
		$form->addHidden('TopicId');
		$form->addHidden('ContentId');
		$form->onSuccess[] = $this->processValidatedEditTopicForm;
		$form->addSubmit('SubmitNewTopic', 'Upravit');

		return $form;
	}


	public function renderPermision($topicId)
	{
		list($topic, $content, $access) = $this->checkTopicAccess($topicId, $this->user);
		if (! $access["CanEditPermissions"])
		{
			throw new BadRequestException("Nemáte oprávnění upravovat přístupová práva");
		}

		$this->template->setParameters(array(
			"Name" => $topic["Name"],
			"topicId" => $topicId,
		));
	}



	public function processValidatedEditTopicForm($form)
	{
		$values = $form->getValues();
		$database = $this->context->database;

		$topicId = $this->getParameter("topicId");
		list($topic, $content, $access) = $this->checkTopicAccess($topicId, $this->user);

		if ($access['IsOwner'] == true or $access['CanEditContentAndAttributes'] == true )
		{
			if(!$this->user->isInRole('admin')){ $values['IsFlame'] = $topic["IsFlame"]; }
			$topic->update(array(
				'Name'       => $values['Name'],
				'IsFlame'    => $values['IsFlame'],
				'CategoryId' => $values['Category'] == 0 ? null : $values['Category']
			));

			$topic->ref('ContentId')->update(array(
				'IsForRegisteredOnly' => $values['IsForRegisteredOnly'],
				'IsForAdultsOnly'     => $values['IsForAdultsOnly']
			));

			$this->redirect('Forum:topic', $values["TopicId"]);
		}
		else
		{
			throw new Nette\Application\BadRequestException('Nejsi vlastník nebo pověřený správce topicu!');
		}
	}



	public function renderHeader($topicId)
	{
		$database = $this->context->database;
		list($topic, $content, $access) = $this->checkTopicAccess($topicId, $this->user);
		if ($access['IsOwner'] == true or $access['CanEditHeader'] == true )
		{
			$this->template->Name = $topic["Name"];
			$this->template->topicId = $topicId;
			$this['headerEdit']->setDefaults(array(
				"Header"                   => $topic->ref('CmsPages', 'Header')["Text"],
				"HeaderForDisallowedUsers" => $topic->ref('CmsPages', 'HeaderForDisallowedUsers')["Text"],
				"TopicId"                  => $topicId,
				"ContentId"                => $topic["ContentId"]
			));
		}
		else
		{
			throw new Nette\Application\BadRequestException('Nejsi vlastník nebo pověřený správce topicu!');
		}
	}



	public function createComponentHeaderEdit()
	{
		$form = new UI\Form;

		// Headers
		$form->addTextArea('Header', 'Hlavička:', 2, 18)
			->setAttribute('placeholder', 'Tvůj příspěvek ...')
			->setAttribute('class', 'tinimce')
			->setAttribute('style', 'height:440px;');
		$form->addTextArea('HeaderForDisallowedUsers', 'Hlavička pro nepovolený přístup:', 2, 10)
			->setAttribute('placeholder', 'Tvůj příspěvek ...')
			->setAttribute('class', 'tinimce')
			->setAttribute('style', 'height:200px;');
		$form->addHidden('TopicId');
		$form->addHidden('ContentId');
		// Submit
		$form->onSuccess[] = $this->processValidatedHeaderEdit;
		$form->addSubmit('SubmitNewTopic', 'Upravit');

		return $form;
	}



	public function processValidatedHeaderEdit($form)
	{
		$values = $form->getValues();

		$topicId = $this->getParameter("topicId");
		list($topic, $content, $access) = $this->checkTopicAccess($topicId, $this->user);

		if ($access['IsOwner'] == true or $access['CanEditHeader'] == true )
		{		
			$cmsPage = $topic->ref("Header");
			$cmsPage->update(array('Text' => $_POST['Header']));
			
			if($_POST['HeaderForDisallowedUsers'] != ""){
				if($topic["HeaderForDisallowedUsers"] == NULL){
					$headerCms = $database->table('CmsPages')->insert(array(
						'Name' => 'Topic alt. header (ContentId: ' . $content['Id'] . ')',
						'Text' => Fcz\SecurityUtilities::processCmsHtml($_POST['HeaderForDisallowedUsers'])
					));
					$topic->update(array("HeaderForDisallowedUsers" => $headerCms['Id']));
				}else{
					$cmsPage = $topic->ref("HeaderForDisallowedUsers");
					$cmsPage->update(array('Text' => $_POST['HeaderForDisallowedUsers']));
				}
			}

			$this->redirect('Forum:topic', $topic["Id"]);
		}
		else
		{
			throw new BadRequestException('Nejsi vlastník nebo pověřený správce topicu!');
		}
	}



	public function createComponentDiscussion()
	{
		$topicId = $this->getParameter('topicId');
		list($topic, $content, $access) = $this->checkTopicAccess($topicId, $this->user);
		$baseUrl = $this->presenter->getHttpRequest()->url->baseUrl;

		return new Fcz\Discussion($this, $content, $topicId, $baseUrl, $access, $this->getParameter('page'), null);
	}



	public function createComponentPermissions()
	{
		$database = $this->context->database;

		$topic = $database->table("Topics")->select("Id, ContentId")->where("Id", $this->getParameter("topicId"))->fetch();
		if ($topic === false)
		{
			throw new BadRequestException("Toto diskusní téma neexistuje", 404);
		}
		$data = array(
			"Permisions" => array(  //Permision data
				"CanListContent" => array("L","Může topic vidět v seznamu","","CanViewContent","",1), //$Zkratka 1 písmeno(""==Nezobrazí), $Popis, $BarvaPozadí, $Parent(""!=Nezobrazí), $Zařazení práv, $default check
				"CanReadPosts" => array("R","Může topic číst","","","",1),
				"CanViewContent" => array("","","","CanReadPosts","Context",1),
				"CanEditContentAndAttributes" => array("E","Může topic upravit","D80093","","Context - Správce",0),
				"CanEditHeader" => array("H","Může upravit hlavičku","D80093","","Context - Správce",0),
				"CanEditPermissions" => array("S","Může upravit práva","D80093","","Context - Správce - NEBEZEPEČNÉ",0),
				"CanDeleteOwnPosts" => array("","","","CanEditOwnPosts","",1),				
				"CanWritePosts" => array("P","Může psát příspěvky","61ADFF","","Context",1),
				"CanDeletePosts" => array("D","Může mazat a editovat všechny příspěvky","007AFF","","Moderátor",0),
				"CanEditPolls" => array("EP","Muže upravit ankety","007AFF","","Moderátor",0),
				"CanEditOwnPosts" => array("F","'Frozen', pokud nebude zaškrtnuto, uživatel nebude moci editovat a mazat vlastní příspěvky.","F00","","",1)
				),
			"Description" => "!", // "!" means NULL here
			"Visiblity" => array(
				"Public" => "Vidí všichni",
				"Private" => "Nevidí nikdo je třeba přidelit práva",
				"Hidden" => "Nezobrazí se v seznamu všech topiků, je třeba přidelit práva"
				),
			"DefaultShow" => true
		);
		return new Fcz\Permissions($this, $content = $topic->ref("ContentId"), $this->getAuthorizator(), $data);
	}



	public function createComponentNewTopicForm()
	{
		$form = new UI\Form;

		// Topic name
		$form->addText('Name', 'Název * :')
			->setRequired('Je nutné zadat název tématu')
			->getControlPrototype()->class = 'Wide';

		// Flags
		$form->addCheckbox('IsForRegisteredOnly', 'Jen pro registrované')->setValue(false);
		$form->addCheckbox('IsForAdultsOnly', '18+')->setValue(false);
		$form->addCheckbox('IsFlame', 'Flamewar')->setValue(false);

		// Category
		$categories = $this->context->database->table('TopicCategories');
		$radioList = array('0' => 'Žádná');
		foreach ($categories as $category)
		{
			$p = Html::el('p');
			$p->class = 'ForumCategoryRadioItem';
			$p->add(Html::el('strong')->text($category['Name']));
			$p->add($category['Description']);
			$radioList[$category['Id']] = $p;
		}
		$form->addRadioList('Category', 'Sekce:', $radioList)->setValue(0);

		// Permissions
		$form->addCheckbox('CanListContent', 'Vidí téma')->setValue(true);
		$form->addCheckbox('CanViewContent', 'Může téma navštívit')->setValue(true);
		$form->addCheckbox('CanEditContentAndAttributes', 'Může měnit název a atributy')->setValue(false);
		$form->addCheckbox('CanEditHeader', 'Může měnit hlavičku')->setValue(false);
		$form->addCheckbox('CanEditOwnPosts', 'Může upravovat vlastní příspěvky')->setValue(true);
		$form->addCheckbox('CanDeleteOwnPosts', 'Může mazat vlastní příspěvky')->setValue(true);
		$form->addCheckbox('CanDeletePosts', 'Může mazat a upravovat jakékoli příspěvky')->setValue(false);
		$form->addCheckbox('CanWritePosts', 'Může psát příspěvky')->setValue(true);
		$form->addCheckbox('CanEditPermissions', 'Může spravovat oprávnění')->setValue(false);
		$form->addCheckbox('CanEditPolls', 'Může spravovat ankety')->setValue(false);

		// Headers
		$form->addTextArea('Header', 'Hlavička:', 2, 10); // Small rows/cols values to allow css scaling
		$form->addTextArea('HeaderForDisallowedUsers', 'Hlavička pro nepovolený přístup:', 2, 10);

		// Submit
		$form->onSuccess[] = $this->processValidatedNewTopicForm;
		$form->addSubmit('SubmitNewTopic', 'Vytvořit');

		return $form;
	}



	public function processValidatedNewTopicForm($form)
	{
		if (! $this->user->isInRole("approved"))
		{
			throw new BadRequestException("Pouze schválení uživatelé mohou zakládat nová diskusní témata");
		}

		$values = $form->getValues();
		$database = $this->context->database;
		$database->beginTransaction();

		/*try
		{*/
			// Create default permission
			$defaultPermission = $database->table('Permissions')->insert(array(
				'CanListContent' => $values['CanListContent'],
				'CanViewContent' => $values['CanViewContent'],
				'CanEditContentAndAttributes' => $values['CanEditContentAndAttributes'],
				'CanEditHeader' => $values['CanEditHeader'],
				'CanEditOwnPosts' => $values['CanEditOwnPosts'],
				'CanDeleteOwnPosts' => $values['CanDeleteOwnPosts'],
				'CanDeletePosts' => $values['CanDeletePosts'],
				'CanWritePosts' => $values['CanWritePosts'],
				'CanEditPermissions' => $values['CanEditPermissions'],
				'CanEditPolls' => $values['CanEditPolls'],
				'CanReadPosts' => $values['CanViewContent']
			));

			// Create content
			$content = $database->table('Content')->insert(array(
				'Type' => 'Topic',
				'TimeCreated' => new DateTime,
				'IsForRegisteredOnly' => $values['IsForRegisteredOnly'],
				'IsForAdultsOnly' => $values['IsForAdultsOnly'],
				'DefaultPermissions' => $defaultPermission['Id']
			));

			// Create permission for owner
			$database->table('Ownership')->insert(array(
				'ContentId' => $content['Id'],
				'UserId' => $this->user->id
			));

			// Create header CMS
			$headerCmsId = null;
			if ($values['Header'] != '')
			{
				$headerCms = $database->table('CmsPages')->insert(array(
					'Name' => 'Topic header (ContentId: ' . $content['Id'] . ')',
					'Text' => Fcz\SecurityUtilities::processCmsHtml($values['Header'])
				));
				$headerCmsId = $headerCms['Id'];
			}

			// Create header CMS for disallowed
			$altHeaderCmsId = null;
			if ($values['HeaderForDisallowedUsers'] != '')
			{
				$headerCms = $database->table('CmsPages')->insert(array(
					'Name' => 'Topic alt. header (ContentId: ' . $content['Id'] . ')',
					'Text' => Fcz\SecurityUtilities::processCmsHtml($values['HeaderForDisallowedUsers'])
				));
				$altHeaderCmsId = $headerCms['Id'];
			}

			// Create topic
			$database->table('Topics')->insert(array(
				'ContentId' => $content['Id'],
				'CategoryId' => $values['Category'] == 0 ? null : $values['Category'],
				'Header' => $headerCmsId,
				'HeaderForDisallowedUsers' => $altHeaderCmsId,
				'IsFlame' => $values['IsFlame'],
				'Name' => $values['Name']
			));

			$database->commit();
		/*}
		catch(Exception $exception)
		{
			$database->rollBack();
			Nette\Diagnostics\Debugger::log($exception);
		}*/

		$this->flashMessage('Diskusní téma bylo vytvořeno', 'ok');
		$this->redirect('Forum:default');
	}



	/**
	* @param int $topicId Topic ID
	* @param int $page Page number
	* @param int $findPost ID of topic to find find and highlight.
	*/
	public function renderTopic($topicId, $page, $findPost)
	{
		$database = $this->context->database;
	
		list($topic, $content, $access) = $this->checkTopicAccess($topicId, $this->user);
		
		$this->getContentManager()->updateLastVisit($content, $this->user);

		// Setup template
		$this->template->setParameters(array(
			'topic' => $topic,
			'content' => $content,
			'access' => $access,
			'book' => $this->getContentManager()->bookIs($topicId, $this->user->id),
			'polls' => $database->table('Polls')->where('ContentId = ?', $topic["ContentId"])
		));
	}



	public function renderDelete($topicId, $postId)
	{
		$database = $this->context->database;
		// TODO: Add permission check!
	}



	public function renderInfo($topicId)
	{
		$database = $this->context->database;

		$users = $database->table('Users');
		foreach($users as $user)
		{
			$allUserWithInfo[$user["Id"]] = array($user["Nickname"], $user["AvatarFilename"], $user["Username"]);
		}

		$topic = $database->table('Topics')->where('Id', $topicId)->fetch();
		$conte = $topic->ref('Content');

		$this->template->Name = $topic["Name"];

		$this->template->topicId = $topicId;
		$this->template->create = $conte["TimeCreated"];
		if ($topic['CategoryId'] != null)
		{
			$this->template->sekce = $topic->ref('TopicCategories')->select('Name');
		}
		else
		{
			$this->template->sekce = array('Name' => 'Nezařazeno', 'Id' => 0);
		}
		$owner = $database->table('Ownership')->where('ContentId', $conte["Id"])->fetch();
		$this->template->owner = array(
			$owner["UserId"],
			$allUserWithInfo[$owner["UserId"]][0],
			$allUserWithInfo[$owner["UserId"]][2]
		);
		$this->template->posts = count($database->table('Posts')->where("ContentId",$conte["Id"])->where("Deleted",0));
		
		$userPosts = null;
		$infa = $database->table('Posts')->where("ContentId",$conte["Id"])->where("Deleted",0);
		foreach($infa as $inf)
		{
			if(!isset($userPosts[$inf["Author"]])){ $userPosts[$inf["Author"]]=array("Name" => $allUserWithInfo[$inf["Author"]][0],"Posts" => 0); }
			$userPosts[$inf["Author"]]["Posts"]+=1;
		}
		if($userPosts!=null)
			arsort($userPosts);
		$this->template->userPosts = $userPosts;
	}



	/**
	* Fetches item from DB and checkes permissions.
	* @return array $topic, $content, $access
	* @throws BadRequestException If the topic isn't found.
	*/
	private function checkTopicAccess($topicId, $user)
	{
		$database = $this->context->database;
		// Fetch item
		$topic = $database->table("Topics")->where("Id", $topicId)->fetch();
		if ($topic === false)
		{
			throw new BadRequestException("Diskusní téma nenalezeno");
		}

		$content = $topic->ref("Content");
		if ($content === false)
		{
			throw new ApplicationException("Database/Image (Id: {$topicId}) has no asociated Database/Content");
		}

		$access = $this->getAuthorizator()->authorize($content, $user);
		return array($topic, $content, $access);
	}
}
