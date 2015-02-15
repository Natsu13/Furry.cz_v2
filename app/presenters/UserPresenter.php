<?php

use Nette\Application\UI;

/**
 * User accounts presenter
 */
class UserPresenter extends BasePresenter
{

	/// @var \Nette\Database\Table\ActiveRow
	/// Temporary variable; profile is loaded in render*() but form generated in createComponent*()
	private $userToEdit = null;



	/**
	 * Shows list of members
	 */
	public function renderDefault()
	{
		$timeout = $this->context->parameters['offlineStatusTimeoutMinutes'];

		$this->template->userList = $this->context->database->table('Users')
			->select('
				Id,
				Nickname,
				OtherNicknames,
				AvatarFilename,
				IsAdmin,
				IsBanned,
				IsFrozen,
				FurrySex,
				DATE_ADD(DateOfBirth, INTERVAL 18 YEAR) >= NOW() AS IsAdult,
				NOW() < DATE_ADD(LastActivityTime, INTERVAL ' . $timeout . ' MINUTE) AS IsOnline')
			->order('Nickname ASC')
			->where('IsApproved = 1');
	}



	/** Nette component factory function
	*/
	public function createComponentRegistrationForm()
	{
		$form = new UI\Form;

		// Basic

		$form->addGroup('Základní údaje');

		$form->addText('username', 'Přihlašovací jméno * :')
			->setRequired('Přihlašovací jméno je povinné.')
			->addRule(UI\Form::PATTERN, 'Přihlašovací jméno může obsahovat pouze malá písmena, číslice a podtržítko _', '^[0-9a-z_]*$')
			->addRule(callback($this->checkUsernameIsUnique), 'Zadané přihlašovací jméno již existuje.', true)
			->setOption('description', 'Povolena jsou malá písmena, číslice a podtržítko _');

		$form->addPassword('password', 'Heslo * :')
			->setRequired('Heslo je povinné.')
			->setOption('description', 'Min. délka je 8 znaků.')
			->addRule(UI\Form::MIN_LENGTH, 'Heslo musí mít minimálně %d znaků.', 8);
			
		$form->addPassword('password_2', 'Heslo znovu * :')
			->setRequired('Heslo znovu je povinné.')
			->setOption('description', 'Je třeba zadat znovu heslo pro ověření.')
			->addRule(UI\Form::MIN_LENGTH, 'Heslo musí mít minimálně %d znaků.', 8)
			->addRule(UI\Form::EQUAL, 'Zadané hesla nejsou stejné.', $form['password']);

		$form->addText('nickname', 'Zobrazované jméno * :')
			->setRequired('Zobrazované jméno je povinné.');

		$form->addCheckbox('agreedWithRules', '* Souhlasím s pravidly')
			->addRule(UI\Form::EQUAL, 'Je nutné odsouhlasit pravidla webu.', true)
			->setRequired('Je nutné odsouhlasit pravidla webu.');

		// Furry

		$form->addGroup('Furry');

		$form->addText('otherNicknames', 'Další přezdívky:');

		$form->addText('species', 'Druh:');

		$form->addSelect('furrySex', 'Pohlaví:', array(
			'NULL' => 'Neuvedeno',
			'Male' => 'Samec',
			'Female' => 'Samice',
			'Herm' => 'Oboupohlavní',
			'Sexless' => 'Bezpohlavní'));

		$form->addTextArea('profileForRegisteredUsers', 'Popis:'); // Will be transformed into CMS page

		// Real

		$form->addGroup('Reál');

		$form->addText('fullName', 'Celé jméno:');

		$form->addText('address', 'Bydliště:');

		$form->addSelect('realSex', 'Pohlaví:', array(
			'NULL' => 'Neuvedeno',
			'Male' => 'Muž',
			'Female' => 'Žena'));

		$form->addText('dateOfBirth', 'Datum narození * :')
			->setOption('description', 'Tento údaj nebude nikde zobrazen (Format: dd-mm-yyyy)')
			->setRequired('Datum narození je povinné')
			->addRule(callback($this->checkValidDate), 'Zadané datum narození je neplatné.')
			->addRule(
				UI\Form::PATTERN,
				'Musíte zadat platné datum ve formátu "15-5-2005"',
				// Doesn't check if date exists, i.e. "32-2-2010" passes
				// Complete validation is done by callback
				'\s*[0-3]{0,1}\s*[0-9]{1}\s*\-\s*[0-1]{0,1}\s*[0-9]{1}\s*\-\s*[1-2]{1}\s*[0-9]{1}\s*[0-9]{1}\s*[0-9]{1}\s*');
				//'0?[1-9]|[12][0-9]|3[01]\-?0?[1-9]|1[0-2]\-?20[0-9]{2}');
			

		$form->addText('email', 'E-mail * :')
			->setType('email') // HTML5 <input> type
			->addRule(UI\Form::EMAIL, 'Zadejte platnou e-mailovou adresu.')
			->setRequired('E-mail je povinný.');

		$form->addText('hobbies', 'Oblíbené činnosti:');

		$form->addText('favouriteWebsites', 'Oblíbené weby:');

		// Submit

		$form->setCurrentGroup(null);

		$form->addSubmit('register', 'Registrovat');

		// Rendering
		$renderer = $form->getRenderer();
		$renderer->wrappers['controls']['container'] = 'dl';
		$renderer->wrappers['pair']['container'] = NULL;
		$renderer->wrappers['label']['container'] = 'dt';
		$renderer->wrappers['control']['container'] = 'dd';

		// CSRF protection
		$form->addProtection('Doba platnosti formuláře vypršela, odešlete jej prosím znovu');

		$form->onSuccess[] = $this->processValidatedRegistrationForm;
		$form->onValidate[] = $this->validateRegistrationForm;
		return $form;
	}



	/** Form validation callback
	*/
	public function checkUsernameIsUnique(Nette\Forms\Controls\TextInput $usernameInput)
	{
		return (count($this->context->database->table('Users')->select('Id')->where('Username', $usernameInput->getValue())) == 0);
	}



	/** Form validation callback. Checks date validity.
	* Checks if the input is a date and if such date really exists in a calendar.
	*/
	public function checkValidDate(Nette\Forms\Controls\TextInput $dateInput)
	{
		try {
			$date = new DateTime(str_replace(' ', '', $dateInput->getValue()));
		} catch (Exception $exception) {
			return false;
		}
		return true;
	}

	public function validateRegistrationForm(UI\Form $form){
		$values = $form->getValues();
		
		$same = true;
		for($i=1;$i<strlen($values["password"]);$i++){ if(substr($values["password"],$i,1) != substr($values["password"],$i-1,1)){ $same=false; } }
		if($same){ 
			$form->addError("Zadejte prosím silnější heslo než jen skupinu stejných znaků"); 
		}
		$array = str_split($values["password"]);
		if(((int)max($array)-(int)min($array) == (count($array)-1))){
			$form->addError("Heslo nesmí být řada po sobě jdoucích čísel!");
		}
		if(Fcz\UserUtilities::testPassword($values["password"]) < 3){
			$form->addError("Heslo je příliš slabé! Přidej nějaká čísla, písmena či speciální znaky!");
		}
		if(!($values["password"] != $values["username"] and $values["password"] != $values["nickname"] and $values["password"] != $values["email"])){
			$form->addError("Zadané heslo nesmí být stejné jako uživatelké jméno, nick či emailová adresa!");
		}
	}

	public function processValidatedRegistrationForm(UI\Form $form)
	{
		$values = $form->getValues();
		// Security
		$salt = Fcz\SecurityUtilities::generateSalt();
		$hash = Fcz\SecurityUtilities::calculateHash($values['password'], $salt);

		// Create description CMS page (if entered)
		$profileForMembersId = null;
		if ( strlen($values['profileForRegisteredUsers']) > 0 ) {
			$profileResultRow = $this->context->database->table('CmsPages')->insert(array(
				'Name' => 'Profil: ' . $values['username'],
				'Description' => 'Profil uživatele "' . $values['username'] . '" (jen pro registrované)',
				'Text' => nl2br($values['profileForRegisteredUsers'])));
			$profileForMembersId = $profileResultRow['Id'];
		}
		
		$values['dateOfBirth'] = explode("-", $values['dateOfBirth']);
		$values['dateOfBirth'] = $values['dateOfBirth'][2]."-".$values['dateOfBirth'][1]."-".$values['dateOfBirth'][0];
			
		// Database insert
		$userResultRow = $this->context->database->table('Users')->insert(array(
			'Username' => $values['username'],
			'Password' => $hash,
			'AvatarFilename' => "0.jpg",
			'Salt' => $salt,
			'Nickname' => $values['nickname'],
			'OtherNicknames' => $values['otherNicknames'],
			'Species' => $values['species'],
			'FurrySex' => $values['furrySex'],
			'ProfileForMembers' => $profileForMembersId,
			'FullName' => $values['fullName'],
			'Address' => $values['address'],
			'RealSex' => $values['realSex'],
			'DateOfBirth' => $values['dateOfBirth'],
			'Email' => $values['email'],
			'Hobbies' => $values['hobbies'],
			'FavouriteWebsites' => $values['favouriteWebsites']
			));
		
		$this->presenter->getContentManager()->notifiRegisterNew($userResultRow["Id"]);
		$this->context->database->table('Notifications')->insert(array(
							"Parent"    => "welcome_0",
							"Time"      => date("Y-m-d H:i:s",time()),
							"IsNotifed" => 0,
							"IsView"    => 0,
							"UserId"    => $userResultRow["Id"],
							"Href"		=> "",
							"Image"		=> "",
							"Text"		=> ""
						));
		$this->redirect('CmsPage:showPage', 'registration-sent-ok');
	}



	/**
	* @param int $id UserId
	*/
	public function renderProfile($id)
	{
		$id = $id != null ? $id : $this->user->id;
		$profileRow = $this->context->database->table('Users')->where('Id', $id)->fetch();
		if ($profileRow === false) {
			throw new Nette\Application\BadRequestException();
		}
		$profile  = $profileRow->toArray();

		// Check login
		if ($this->user->isLoggedIn())
		{
			$profile['ShortDescription'] = $profile['ShortDescriptionForMembers'];
			$profile['ProfileHtml'] = $profileRow['ProfileForMembers'] != null ? $profileRow->ref('CmsPages', 'ProfileForMembers')['Text'] : null;
			$profile['ProfileHtml'] = str_replace("\n","<br>",$profile['ProfileHtml']);
			$profile['CanBeEdited'] = ($this->user->isInRole('member') && $id == $this->user->id) || ($this->user->isInRole('admin'));
			$profile["RefTable"] = $profileRow->ref('CmsPages', 'ProfileForMembers');
		}
		else
		{
			$profile['ShortDescription'] = $profile['ShortDescriptionForGuests'];
			$profile['ProfileHtml'] = $profileRow['ProfileForGuests'] != null ? $profileRow->ref('CmsPages', 'ProfileForGuests')['Text'] : null;
			$profile['ProfileHtml'] = str_replace("\n","<br>",$profile['ProfileHtml']);
			$profile['CanBeEdited'] = false;
			$profile["RefTable"] = $profileRow->ref('CmsPages', 'ProfileForGuests');
		}
		unset($profile['ShortDescriptionForGuests']);
		unset($profile['ShortDescriptionForMembers']);
		unset($profile['ProfileForMembers']);
		unset($profile['ProfileForGuests']);

		// Check age
		$profile['Age'] = ($profileRow['DateOfBirth']->diff(new DateTime())->y >= 18) ? '18+' : '18-';

		// Check if 'Personal' section should be rendered
		$profile['HasAnyPersonalInfo'] = (
			$profile['FullName'] != null
			|| $profile['Address'] != null
			|| $profile['RealSex'] != null
			|| $profile['FavouriteWebsites'] != null
			|| $profile['ProfilePhotoFilename'] != null
			|| $profile['Hobbies'] != null
			|| $profile['DistanceFromPrague'] != null
			|| $profile['WillingnessToTravel'] != null);

		// Localize enums
		$furrySexValues = array(
			'Male' => 'Samec',
			'Female' => 'Samice',
			'Herm' => 'Oboupohlavní',
			'Sexless' => 'Bezpohlavní'
		);

		$realSexValues = array(
			'Male' => 'Muž',
			'Female' => 'Žena'
		);

		$willingnessToTravelValues = array(
			'Small' => 'Malá',
			'Medium' => 'Střední',
			'Big' => 'Velká'
		);

		$profile['FurrySex'] = ($profile['FurrySex'] != null && $profile['FurrySex'] != '')
			? $furrySexValues[$profile['FurrySex']]
			: null;

		$profile['RealSex'] = ($profile['RealSex'] != null && $profile['RealSex'] != '')
			? $realSexValues[$profile['RealSex']]
			: null;

		$profile['WillingnessToTravel'] = ($profile['WillingnessToTravel'] != null && $profile['WillingnessToTravel'] != '')
			? $willingnessToTravelValues[$profile['WillingnessToTravel']]
			: null;
			
		$profile['LastLogin'] = date("d. m. Y H:i", strtotime($profile["LastLogin"]));

		// Format favourite websites
		$favouries = array();
		foreach (explode(' ', $profile['FavouriteWebsites']) as $fav)
		{
			$hasHttp = substr($fav, 0, 7) == 'http://';
			$hasHttps = substr($fav, 0, 8) == 'https://';

			if (!$hasHttp && !$hasHttps)
			{
				$favourites['http://' . $fav] = $fav;
			}
			else if ($hasHttp)
			{
				// Trim http://www. if present
				$favourites[$fav] = (substr($fav, 7, 4) == 'www.') ? substr($fav, 11) : substr($fav, 7);
			}
			else
			{
				// Trim https://www. if present
				$favourites[$fav] = (substr($fav, 8, 4) == 'www.') ? substr($fav, 12) : substr($fav, 8);
			}
		}
		$profile['FavouriteWebsites'] = $favourites;

		$this->template->profile = $profile;
	}



	/**
	* @param int $id UserId
	*/
	public function renderEdit($id)
	{
		$id = $id != null ? $id : $this->user->id;

		// Check permissions
		if (
			!($this->user->isInRole('member') && $this->user->id == $id) // Member can edit his profile
			&&
			!($this->user->isInRole('admin')) // Admin can edit all profiles
		)
		{
			throw new Nette\Application\ForbiddenRequestException("Nejste oprávněn(a) editovat tento profil");
		}

		// Load the profile
		$profileRow = $this->context->database->table('Users')->where('Id', $id)->fetch();
		if ($profileRow === false)
		{
			throw new Nette\Application\BadRequestException();
		}

		// Save the profile for edit form factory function
		$this->userToEdit = $profileRow;

		$this->template->profile = array(
			'Nickname' => $profileRow['Nickname']
		);
	}



	/** Nette component factory function
	*/
	public function createComponentEditProfileForm()
	{
		$config = $this->context->parameters['fileUploads'];
		if (isset($config['userAvatar']['maxFilesize']))
		{
			$avatarMaxFilesize = $config['userAvatar']['maxFilesize'];
		}
		else
		{
			$avatarMaxFilesize = 1024 * 900;
		}

		// Create form
		$form = new UI\Form();

		$form->addHidden('username');

		$form->addHidden('userId');

		// Basic

		$form->addText('nickname', 'Přezdívka:')
			->setRequired('Zobrazované jméno je povinné.');

		// Furry

		$form->addText('otherNicknames', 'Další přezdívky:');

		$form->addText('species', 'Druh:');

		$form->addSelect('furrySex', 'Pohlaví:', array(
			'NULL' => 'Neuvedeno',
			'Male' => 'Samec',
			'Female' => 'Samice',
			'Herm' => 'Oboupohlavní',
			'Sexless' => 'Bezpohlavní'));

		$form->addUpload('avatarImage', 'Avatar:');//->addRule(UI\Form::IMAGE, 'Soubor musí být JPEG, PNG nebo GIF.');

		$form->addTextArea('profileForMembersText', 'Popis (pro členy):'); // CMS page

		$form->addHidden('profileForMembersId');

		$form->addTextArea('profileForGuestsText', 'Popis (pro veřejnost):'); // CMS page

		$form->addHidden('profileForGuestsId');

		// Real

		$form->addText('fullName', 'Celé jméno:');

		$form->addText('address', 'Bydliště:');

		$form->addSelect('realSex', 'Pohlaví:', array(
			'NULL' => 'Neuvedeno',
			'Male' => 'Muž',
			'Female' => 'Žena'));

		$form->addText('email', 'E-mail:')
			->setType('email') // HTML5 <input> type
			->addRule(UI\Form::EMAIL, 'Zadejte platnou e-mailovou adresu.')
			->setRequired('E-mail je povinný.');

		$form->addUpload('profilePhoto', 'Fotka:');//->addRule(UI\Form::IMAGE, 'Soubor musí být JPEG, PNG nebo GIF.');

		$form->addTextArea('hobbies', 'Oblíbené činnosti:', 2, 10); // Small rows/cols to allow css scaling

		$form->addTextArea('favouriteWebsites', 'Oblíbené weby:', 2, 10); // Small rows/cols to allow css scaling

		$form->addSelect('willingnessToTravel', 'Ochota cestovat:', array(
				'NULL' => 'Neuvedeno',
				'Small' => 'Malá',
				'Medium' => 'Střední',
				'Big' => 'Velká'
			));

		// Submit

		$form->addSubmit('saveChanges', 'Uložit změny');
		$form->onValidate[] = $this->validateEditProfileForm;
		$form->onSuccess[] = $this->processValidatedEditProfileForm;

		// Default values

		if ($this->userToEdit != null) // Variable set by renderEdit()
		{
			$profileRow = $this->userToEdit;

			$profileForMembersRow = $profileRow->ref('CmsPages', 'ProfileForMembers');
			$profileForGuestsRow = $profileRow->ref('CmsPages', 'ProfileForGuests');

			$form->setDefaults(array(
				'username' => $profileRow['Username'],
				'userId' => $profileRow['Id'],
				'nickname' => $profileRow['Nickname'],
				'otherNicknames' => $profileRow['OtherNicknames'],
				'species' => $profileRow['Species'],
				'furrySex' => $profileRow['FurrySex'],
				'profileForMembersText' => $profileForMembersRow['Text'],
				'profileForGuestsText' => $profileForGuestsRow['Text'],
				'profileForMembersId' => $profileRow['ProfileForMembers'],
				'profileForGuestsId' => $profileRow['ProfileForGuests'],
				'fullName' => $profileRow['FullName'],
				'address' => $profileRow['Address'],
				'realSex' => $profileRow['RealSex'],
				'email' => $profileRow['Email'],
				'hobbies' => $profileRow['Hobbies'],
				'favouriteWebsites' => preg_replace( '/\s+/', "\n", $profileRow['FavouriteWebsites']), // Replace whitespace with newlines
				'willingnessToTravel' => $profileRow['WillingnessToTravel']
			));
		}

		return $form;
	}



	/** Form validation callback
	*/
	public function validateEditProfileForm(UI\Form $form)
	{
		// Validate avatar upload
		$uploadComponent = $form->getComponent('avatarImage', true);
		if ($uploadComponent->isFilled()) // If anything was uploaded...
		{
			list($result, $errMsg) = $this->getUploadHandler()->validateUpload($uploadComponent->getValue(), 'userAvatar');
			if ($result == false)
			{
				$form->addError('Avatar: ' . $errMsg);
			}
		}

		// Validate profile photo upload
		$uploadComponent = $form->getComponent('profilePhoto', true);		
		if($uploadComponent->isFilled()) // If anything was uploaded...
		{
			list($result, $errMsg) = $this->getUploadHandler()->validateUpload($uploadComponent->getValue(), 'profilePhoto');
			if ($result == false)
			{
				$form->addError('Fotka: ' . $errMsg);
			}
		}
	}



	/** Form callback (after submission + validation)
	*/
	public function processValidatedEditProfileForm(UI\Form $form)
	{
		$values = $form->getValues();

		// Check permissions
		if (
			!($this->user->isInRole('member') && $this->user->id == $values['userId']) // Member can edit his profile
			&&
			!($this->user->isInRole('admin')) // Admin can edit any profile
		)
		{
			throw new Nette\Application\ForbiddenRequestException("Nejste oprávněn(a) editovat tento profil");
		}

		// Create/Update/Delete CMS profile for members
		if (strlen($values['profileForMembersText']) > 0) {
			if ($values['profileForMembersId'] != null) {
				// Update
				$this->context->database
					->table('CmsPages')
					->where('Id', $values['profileForMembersId'])
					->update(array(
						'Text' => $values['profileForMembersText']
					));
			} else {
				// Insert
				$cmsRow = $this->context->database
					->table('CmsPages')
					->insert(array(
						'Text' => $values['profileForMembersText'],
						'Name' => "Profil (pro členy): " . $values['username'] . " (id: " . $values['userId'] . ")"
					));
				$values['profileForMembersId'] = $cmsRow['Id'];
			}
		} elseif ($values['profileForMembersId'] != null) {
			// Delete
			$this->context->database
				->table('CmsPages')
				->where('Id', $values['profileForMembersId'])
				->delete();
			$values['profileForMembersId'] = null;
		} else {
			$values['profileForMembersId'] = null; // Replace empty string
		}

		// Create/Update/Delete CMS profile for guests (public)
		if (strlen($values['profileForGuestsText']) > 0) {
			if ($values['profileForGuestsId'] != null) {
				// Update
				$this->context->database
					->table('CmsPages')
					->where('Id', $values['profileForGuestsId'])
					->update(array(
						'Text' => $values['profileForGuestsText']
					));
			} else {
				// Insert
				$cmsRow = $this->context->database
					->table('CmsPages')
					->insert(array(
						'Text' => $values['profileForGuestsText'],
						'Name' => "Profil (pro veřejnost): " . $values['username'] . " (id: " . $values['userId'] . ")"
					));
				$values['profileForGuestsId'] = $cmsRow['Id'];
			}
		} elseif ($values['profileForGuestsId'] != null) {
			// Delete
			$this->context->database
				->table('CmsPages')
				->where('Id', $values['profileForGuestsId'])
				->delete();
			$values['profileForGuestsId'] = null;
		} else {
			$values['profileForGuestsId'] = null; // Replace empty string
		}

		// Handle profile image uploads
		$uploadHandler = new Fcz\FileUploadHandler($this);
		$avatarFilename = $uploadHandler->handleProfileImageUpload($form->getComponent('avatarImage'), 'userAvatar', $values['userId']);
		$photoFilename = $uploadHandler->handleProfileImageUpload($form->getComponent('profilePhoto'), 'userProfilePhoto', $values['userId']);

		// Database update
		$update = array(
			'Nickname' => $values['nickname'],
			'OtherNicknames' => $values['otherNicknames'],
			'Species' => $values['species'],
			'FurrySex' => $values['furrySex'],
			'ProfileForMembers' => $values['profileForMembersId'],
			'ProfileForGuests' => $values['profileForGuestsId'],
			'FullName' => $values['fullName'],
			'Address' => $values['address'],
			'RealSex' => $values['realSex'],
			'Email' => $values['email'],
			'Hobbies' => $values['hobbies'],
			'FavouriteWebsites' => preg_replace( '/\s+/', ' ', $values['favouriteWebsites']), // Collapse whitespace
			'WillingnessToTravel' => $values['willingnessToTravel']
			);
		if ($avatarFilename != null)
		{
			$update['AvatarFilename'] = $avatarFilename;
		}
		if ($photoFilename != null)
		{
			$update['ProfilePhotoFilename'] = $photoFilename;
		}
		$this->context->database
			->table('Users')
			->where('Id', $values['userId'])
			->update($update);

		$this->flashMessage('Profil byl upraven', 'ok');

		$this->redirect('User:profile', $values['userId']);
	}
}
