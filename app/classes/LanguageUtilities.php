<?php

namespace Fcz
{

class LanguageUtilities
{

	/** Spells count correctly in Czech (example: "lemon" = 1 citron, 2-4 citrony, 5 citronů)
	* @param int $count Number of lemons
	* @param array $wordForms {(common)"citro", (1)"n", (2-4)"ny", (5)"nů"}
	*/
	public static function czechCount( $count, array $wordForms )
	{
		if ($count == 1)
		{
			return $wordForms[0] . $wordForms[1];
		}
		else if ($count >= 2 and $count <= 4)
		{
			return $wordForms[0] . $wordForms[2];
		}
		else
		{
			return $wordForms[0] . $wordForms[3];
		}
	}
	
	/** Spells count correctly in Czech (example: "car" = 1 auto, 2-4 auta, 5 aut)
	* @param int $count Number of cars
	* @param array $wordForms {(common)(1)"auto", (2-4)"auta", (5)"aut"}
	*/
	public static function counter( $count, array $wordForms )
	{
		if ($count == 1)
		{
			return $wordForms[0];
		}
		else if ($count >= 2 and $count <= 4)
		{
			return $wordForms[1];
		}
		else
		{
			return $wordForms[2];
		}
	}
}

}
