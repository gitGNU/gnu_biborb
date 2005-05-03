<?php
/*
Released through http://bibliophile.sourceforge.net under the GPL licence.
Do whatever you like with this -- some credit to the author(s) would be appreciated.

A collection of PHP classes to manipulate bibtex files.

If you make improvements, please consider contacting the administrators at bibliophile.sourceforge.net so that your improvements can be added to the release package.

Mark Grimshaw 2004
http://bibliophile.sourceforge.net
*/

// For a quick command-line test (php -f PARSECREATORS.php) after installation, uncomment these lines:

/***********************
	$authors = "Mark N. Grimshaw and Bush III, G.W. & M. C. Hammer Jr. and von Frankenstein, Ferdinand Cecil, P.H. & Charles Louis 		Xavier Joseph de la Vallee Poussin and et. al";
	$creator = new PARSECREATORS();
	list($creatorArray, $etAl) = $creator->parse($authors);
	print_r($creatorArray);
	if($etAl)
		print "\netAl";
***********************/

class PARSECREATORS
{
	function PARSECREATORS()
	{
		$this->roman = array('I', 'II', 'III', 'IV', 'V', 'VI', 'VII', 'VIII', 'IX', 'X');
	}
// Create writer arrays from bibtex input.
// 'author field can be:
//	Surname, Initials, Firstname and|& Surname, Initials, Firstname
//	Firstname Initials Surname
// 	Firstname Initials Surname, Jr.,|Sr.||jr.,sr.
//	Surname, Jr., Initials, Firstname
	function parse($input)
	{
		$input = trim($input);
		if(($input == 'Anon') || ($input == 'Anonymous') || ($input == 'anon') || ($input == 'anonymous'))
			return array(FALSE, FALSE);
		$etAl = FALSE;
// split on space or &|and
		$authorArray = preg_split("/\s(and|&)\s/i", $input);
// check if there's anything that looks like et. al
		foreach($authorArray as $key => $value)
		{
			if((strtolower(trim($value) == 'et. al')) || (strtolower(trim($value)) == 'et. al.'))
			{
				$etAl = TRUE;
				unset($authorArray[$key]);
			}
		}
		foreach($authorArray as $value)
		{
			$apellation = $roman = '';
			$author = explode(",", preg_replace("/\s{2,}/", ' ', trim($value)));
			$size = sizeof($author);
// At least one ',' in author string?
			if($size > 1)
			{
				if($apellation = $this->grabApellation($author))
						$value = $author[0];
				else
					$size++;
			}
// Handles Firstname Initials Surname
			if($size <= 2)
			{
				$author = explode(" ", trim($value));
				if($size == 1)
					$apellation = $this->grabApellation($author);
				$item = trim(array_pop($author));
				if(!$roman = $this->grabRoman($item))
					array_push($author, $item);
				$surname = $this->grabSurname($author, 'post');
			}
// If $size is > 1, we're looking at something like Surname, Initials, Firstname
			else
			{
				$surname = $this->grabSurname($author, 'pre');
			}
			$initials = $this->grabInitials($author);
			$surname = $surname . $apellation . $roman;
// What is left of $author at this stage should be the firstname(s)
			$firstname = trim(implode(' ', $author));
			$creators[] = array($firstname, $initials, $surname);
		}
		return array($creators, $etAl);
	}
// Deal with Jr., Sr., etc
	function grabApellation(&$author)
	{
		$apellation = FALSE;
		foreach($author as $key => $value)
		{
			if(preg_match("/Sr\.|jr\./i", $value))
			{
				$apellation = trim($value);
				$keys[] = $key;
			}
			else
				$remainder[] = $value;
		}
		if($apellation)
		{
			if(isset($remainder))
				$author = $remainder;
			else
				$author = array();
			return ", " . $apellation;
		}
		else
			return FALSE;
	}
// Check for silly names such as G.W. Bush III
	function grabRoman($item)
	{
		if(array_key_exists($item, $this->roman))
			return " " . trim($item);
		return FALSE;
	}
// grab initials which may be of form "A.B.C." or "A. B. C. " etc.
	function grabInitials(&$author)
	{
		$initials = FALSE;
		foreach($author as $key => $value)
		{
			if(preg_match("/\./", $value))
			{
				$initials .= implode(" ", explode(".", trim($value)));
				$keys[] = $key;
			}
			else
				$remainder[] = $value;
		}
		if($initials)
		{
			if(isset($remainder))
				$author = $remainder;
			else
				$author = array();
			return $initials;
		}
		else
			return '';
	}
// surname may have title such as 'den', 'von', 'de la' etc. - characterised by first character lowercased
	function grabSurname(&$author, $remove)
	{
		$index = 0;
		$titleFound = FALSE;
// check for title
		foreach($author as $value)
		{
			$firstChar = substr($value, 0, 1);
			if((ord($firstChar) >= 97) && (ord($firstChar) <= 122))
			{
				$titleFound = TRUE;
				break;
			}
			$index++;
		}
		if($titleFound && ($remove == 'post'))
			return trim(implode(" ", $surname = array_splice($author, $index)));
		else if(($remove == 'post'))
			return trim(array_pop($author));
		else
			return trim(array_shift($author));
	}
}
?>
