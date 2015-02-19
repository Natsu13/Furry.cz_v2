<?php
namespace Fcz;

use Nette\Image;

class CmsUtilities extends \Nette\Object
{

	protected $presenter;

	public function __construct(\Nette\Application\IPresenter $presenter)
	{
		$this->presenter = $presenter;
	}

	/**
	 * Loads CMS page for inserting into another page.
	 * Returns the page on success and error/disclaimer for inaccessible pages.
	 * @param int|string $idOrAlias
	 * @return string The HTML code to insert into page
	 */
	public function getCmsHtml($idOrAlias)
	{
		// Prepare query
		$where = NULL;
		if (is_int($idOrAlias) || (is_string($idOrAlias) && ctype_digit($idOrAlias[0]))) { // Aliases must not start with number
			$where = array('Id' => (int) $idOrAlias);
		} elseif (is_string($idOrAlias)) {
			$where = array('Alias' => $idOrAlias);
		} else {
			// TODO print error to log
			return "<p>[!] Nastala chyba serveru; CMS stránka je chybně odkazována</p>";
		}

		// Load the page
		$cmsPage = $this->presenter->context->database->table('CmsPages')->where($where)->fetch();

		// Check if page exists
		if ($cmsPage === false) {
			return "<p>[!] Odkazovaná CMS stránka neexistuje</p>";
		}

		// Check access TODO

		// Return the HTML
		return $cmsPage['Text'];
	}
	
	/**
	 * @param time $ptime
	 * @return time as text 'před 7 hodinami'
	 */
	public static function getTimeElapsedString($ptime)
	{
		$etime = time() - $ptime;

		if ($etime < 1)
		{
			return 'právě teď';
		}

		$a = array( 12 * 30 * 24 * 60 * 60  =>  array('rokem','roky'),
					30 * 24 * 60 * 60       =>  array('měsícem','měsíci'),
					24 * 60 * 60            =>  array('dnem','dny'),
					60 * 60                 =>  array('hodinou','hodinami'),
					60                      =>  array('minutou','minutami'),
					1                       =>  array('sekundou','sekundami')
					);

		foreach ($a as $secs => $str)
		{
			$d = $etime / $secs;
			if ($d >= 1)
			{
				$r = round($d);
				if($secs >= 86400 and $r>2) return Date("j.m.Y H:i", $ptime);
				return "před ".$r . ' ' . ($r > 1 ? $str[1] : $str[0]) . '';
			}
		}
	}
	
	public static function parseHTML($html,$content = null, $row_name = null, $url = ""){
		$html = str_replace("position:","",$html);
		$html = preg_replace_callback('/\<a href\=\"(.*)\">(.*)\<\/a\>/U', function($match){
			$time=time();
			$end = explode(".",$match[1]);
			if(substr(trim($match[1]), 0, strlen("http://www.youtube.com/"))=="http://www.youtube.com/" or substr(trim($match[1]), 0, strlen("https://www.youtube.com/"))=="https://www.youtube.com/"){
			   $data = explode("?",$match[1]);
			   $data = explode("&",$data[1]);
			   $i=0;$IdVidea="";
			   while(@$data[$i]!=""){
				$polo = explode("=",$data[$i]);
				if($polo[0]=="v" and $IdVidea==""){$IdVidea=$polo[1];}
				$i+=1;
			   }
			   if($IdVidea=="")
				return '<font color=red>V adrese se nenachází id videa!<br>'.$match[0].'</font>';
			   else{
				//return '<iframe width="'.$match[1].'" height="'.$match[2].'" src="http://www.youtube.com/embed/'.$match[3].'" frameborder="0" allowfullscreen></iframe>';
				return '<div class="YoutubeBox"><iframe width="300" height="200" src="http://www.youtube.com/embed/'.$IdVidea.'?rel=0" frameborder="0" allowfullscreen style="padding-top: 6px;"></iframe><div style="padding:4px;"><a href="'.$match[1].'" title="'.$match[2].'" target="_blank">'.$match[2].'</a></div></div>';
			   }	
			}elseif(end($end)=="swf"){
				return '<div class="YoutubeBox"><a href=# onClick="this.style.display=\'none\';$(\'#flash-open-'.\Nette\Utils\Strings::webalize($match[1]).'\').css(\'display\',\'block\');return false;" style="display: block;padding: 5px 30px;width: 159px;overflow: hidden;border-radius:3px;margin: 92px auto;">Zobrazit flash animaci</a><object id="flash-open-'.\Nette\Utils\Strings::webalize($match[1]).'" type="application/x-shockwave-flash" data="'.$match[1].'" width="300" height="200" id="flashcontent" style="display:none;visibility: visible; height: 200px; width: 300px;padding-top: 6px;padding-bottom: 6px;"></object><div style="padding:4px;"><a href="'.$match[1].'" title="'.$match[2].'" target="_blank">'.$match[2].'</a></div></div>';
			}else{
				return $match[0];
			}		
		}, $html);
		$extern = false;
		$html = preg_replace_callback('/\<img src="(.*)"(.*)>/U', function($match) use(&$extern,$url){
			$time=time();
			$end = explode(".",$match[1]);			
			if(end($end)=="gif"){				
				if(!file_exists('./images/gif/'.\Nette\Utils\Strings::webalize($match[1]).".png")){
					$image = Image::fromFile($match[1]);
					$image->save('./images/gif/'.\Nette\Utils\Strings::webalize($match[1]).".png");
				}else{
					$image = Image::fromFile('./images/gif/'.\Nette\Utils\Strings::webalize($match[1]).".png");
				}
				$ret = "<div id='gif-close-".\Nette\Utils\Strings::webalize($match[1])."' style='width:".$image->width."px;height:".$image->height."px;background:url(".($url)."/images/gif/".\Nette\Utils\Strings::webalize($match[1]).".png)'>";
				$ret.= '<div style="float: left;background-color: rgba(0, 0, 0, 0.44);padding: 4px;width: 98%;color: white;font-weight: bold;">#GIF</div>';
				$ret.= '<div onClick="$(\'#gif-close-'.\Nette\Utils\Strings::webalize($match[1]).'\').css(\'display\',\'none\');$(\'#gif-open-'.\Nette\Utils\Strings::webalize($match[1]).'\').css(\'display\',\'block\');return false;" class="play"></div>';
				$ret.= "</div>";
				$ret.= '<img id="gif-open-'.\Nette\Utils\Strings::webalize($match[1]).'" src="'.$match[1].'" style="display:none;visibility: visible;">';
				return $ret;
			}else{
				$width = 0;
				$height = 0;
				$a = 0;
				$b = 0;
				$match[0] = preg_replace_callback('/\ (.*)="(.*)"/U', function($match) use(&$a,&$b,&$width,&$height){
					if($match[1] == "width"){
						$width = trim(str_replace("px","",$match[2]));
						if($match[2] > 840 and $b == 0){			
							$a = 1;
							return " width='840px'";
						}
					}else if($match[1] == "height"){
						$height = trim(str_replace("px","",$match[2]));
						if($match[2] > 840 and $a == 0){		
							$b = 1;
							return " height='840px'";
						}
					}
					return $match[0];
				}, $match[0]);
				if($a == 0 and $b == 0){
					$image = Image::fromFile($match[1]);
					$width = $image->width;
					$height = $image->height;
					if($width > 840){ preg_replace('/width="(.*)"/i', "width='840px'", $match[0]); if($width > 0){$a=2;} }
					else if($height > 840){ preg_replace('/height="(.*)"/i', "height='840px'", $match[0]); if($height > 0){$b=2;} }
					$extern = true;
				}
				if($a==1 or $a==2){
					$org_percent = (840/($width/100));					
					if($a == 2)
						$match[0] = "<img src='".$match[1]."'".$match[2]." height='".(($height/100)*$org_percent)."px'>";
					else
						$match[0] = preg_replace('/height="(.*)"/i', "height='".(($height/100)*$org_percent)."px'", $match[0]);	
				}
				if($b==1 or $b == 2){
					$org_percent = (840/($height/100));
					if($b == 2)
						$match[0] = "<img src='".$match[1]."'".$match[2]." width='".(($width/100)*$org_percent)."px'>";
					else
						$match[0] = preg_replace('/width="(.*)"/i', "width='".(($width/100)*$org_percent)."px'", $match[0]);
				}
				return $match[0];
			}		
		}, $html);
		if($extern and $content!=null){
			//update on external links...
			//$presenter->context->database->table('Posts')->where('ContentId', $content['Id'])->
			$content->update(array($row_name => $html));
			//content
		}
		return $html;
	}

}
