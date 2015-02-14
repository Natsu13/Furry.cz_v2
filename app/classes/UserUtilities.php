<?php

namespace Fcz;

class UserUtilities extends \Nette\Object
{
	protected $presenter;

	public function __construct(\Nette\Application\IPresenter $presenter)
	{
		$this->presenter = $presenter;
	}
	
	public $UserDataDefault = array(
			"postsOrdering" => "NewestOnTop",
			"postsPerPage" => 25
		);
	public $UserListSelect = 0;	
		
	/**
	 * @simple function to test password strength
	 * @param string $password
	 * @return int 
	 */
	public static function testPassword($password)
	{
		if ( strlen( $password ) == 0 )
		{
			return 1;
		}

		$strength = 0;

		/*** get the length of the password ***/
		$length = strlen($password);

		/*** check if password is not all lower case ***/
		if(strtolower($password) != $password)
		{
			$strength += 1;
		}
		
		/*** check if password is not all upper case ***/
		if(strtoupper($password) == $password)
		{
			$strength += 1;
		}

		/*** check string length is 8 -15 chars ***/
		if($length >= 8 && $length <= 15)
		{
			$strength += 1;
		}

		/*** check if lenth is 16 - 35 chars ***/
		if($length >= 16 && $length <=35)
		{
			$strength += 2;
		}

		/*** check if length greater than 35 chars ***/
		if($length > 35)
		{
			$strength += 3;
		}
		
		/*** get the numbers in the password ***/
		preg_match_all('/[0-9]/', $password, $numbers);
		$strength += count($numbers[0]);

		/*** check for special chars ***/
		preg_match_all('/[|!@#$%&*\/=?,;.:\-_+~^\\\]/', $password, $specialchars);
		$strength += sizeof($specialchars[0]);

		/*** get the number of unique chars ***/
		$chars = str_split($password);
		$num_unique_chars = sizeof( array_unique($chars) );
		$strength += $num_unique_chars * 2;

		/*** strength is a number 1-10; ***/
		$strength = $strength > 99 ? 99 : $strength;
		$strength = floor($strength / 10 + 1);

		return $strength;
	}	
		
	public function getData($userId,$DataName){
		$data = $this->presenter->context->database->table('UserSettings')->where("UserId = ? AND Name = ?",$userId,$DataName)->fetch();
		if(!$data){return @$this->UserDataDefault[$DataName];}
		return $data["Value"];
	}
	
	public function setData($userId,$DataName,$DataValue=""){
		if($DataValue==""){$DataValue = @$this->UserDataDefault[$DataName];}
		$data = $this->presenter->context->database->table('UserSettings')->where("UserId = ? AND Name = ?",$userId,$DataName)->fetch();
		if(!$data){ return $this->presenter->context->database->table('UserSettings')->insert(array("Value" => $DataValue, "UserId" => $userId, "Name" => $DataName)); }
		else{ return $this->presenter->context->database->table('UserSettings')->where("UserId = ? AND Name = ?",$userId,$DataName)->update(array("Value" => $DataValue)); }
	}
	
	public function drawUserSelect($input, $data = 0, $width = 200, $without = array(), $delete = "", $new = true){
		echo '<span href=# class="JS ContextMenu input withimage" dropdown="UserListSelect_'.$this->UserListSelect.'" dropdown-open="left" selectType="2" onChange="if(typeof value_ == \'undefined\'){value_=\'\';}$(\'#'.$input.'\').val(value_);$(\'#'.$input.'\').change();" dropdown-absolute="true" width='.$width.'>Zadej jmeno...</span>';
		echo '<div class="listDiv" id="UserListSelect_'.$this->UserListSelect.'">';
			echo '<div class="listBox" style="width:'.($width-2).'px;">';
				echo "<ul>";
					$userlist = $this->presenter->context->database->table('Users')->order('Nickname ASC');
					//->where('IsApproved = 1');
					foreach($userlist as $user){
						if($data==0){$val=$user["Id"];}elseif($data==1){$val=$user["Username"];}else{$val=$user["Nickname"];}
						if(!in_array($val, $without)) {		
							if($user["AvatarFilename"]==""){$user["AvatarFilename"]="0.jpg";}
							echo "<li value_='".$val."' data-image='".($this->presenter->context->httpRequest->url->baseUrl)."/images/avatars/".$user["AvatarFilename"]."'><a>".$user["Nickname"]."</a></li>";
						}
					}
				echo "</ul>";
			echo "</div>";
		echo "</div>";
		if($new)
		echo '<input type="hidden" name="'.$input.'__JS" id="'.$input.'" value="">';
		echo "<select name='".$input."' id='select_".$input."' style='width:".$width."px;'>";
			foreach($userlist as $user){
				if($data==0){$val=$user["Id"];}elseif($data==1){$val=$user["Username"];}else{$val=$user["Nickname"];}
				if(!in_array($val, $without)) {		
					echo "<option value='".$val."'>".$user["Nickname"]."</option>";
				}
			}
		echo "</select>";
		echo "<script>$(\"#select_".$input."\").remove();setTimeout(function(){ $(\"#".$delete."\").remove(); },100);$(\"#".$input."\").attr(\"name\",\"".$input."\")</script>";		
		$this->UserListSelect++;
	}
	
	public function getAllUsers(){
		$database = $this->presenter->context->database;
		$users = $database->table('Users')->order('Nickname');
		foreach($users as $user){
			if($user["AvatarFilename"]==""){$user["AvatarFilename"]="0.jpg";}
			$allUserWithInfo[$user["Id"]] = array($user["Nickname"], $user["AvatarFilename"], $user["Id"], $user["Username"]);
			$allUserWithInfo[$user["Username"]] = array($user["Nickname"], $user["AvatarFilename"], $user["Id"], $user["Username"]);
		}
		return $allUserWithInfo;
	}
}