<?php
/*
Inspired by an awk BibTeX parser written by Nelson H. F. Beebe over 20 years ago although little of that 
remains other than a highly edited braceCount().

Released through http://bibliophile.sourceforge.net under the GPL licence.
Do whatever you like with this -- some credit to the author(s) would be appreciated.

Mark Grimshaw 2004
(Amendments to file reading Daniel Pozzi for v1.1)

19/08/2004 Guillaume Gardey, Added string parsing feature.

*/
CLASS PARSEENTRIES
{
	function PARSEENTRIES()
	{
		$this->preamble = $this->strings = $this->entries = array();
		$this->count = 0;
		$this->fieldExtract = TRUE;
		$this->removeDelimit = TRUE;
	}
// Open bib file
	function openBib($file)
	{
		if(!is_file($file))
			die;
		$this->fid = fopen ($file,'r');
		$this->parseFile = TRUE;
	}
// Load a sting to parse
    function loadString($bibtex_string)
    {
        $this->string = $bibtex_string;
        $this->parseFile = FALSE;
        $this->currentLine = 0;
    }
// Close bib file
	function closeBib()
	{
		fclose($this->fid);
	}
// Get a line from bib file
    function getLine()
    {
        if($this->parseFile){
            if(!feof($this->fid))
                return trim(fgets($this->fid));
            return FALSE;
        }
        else{
            return ($this->currentLine != count($this->string)) ? $this->string[$this->currentLine++] : FALSE;
        }
	}
// Count entry delimiters
	function braceCount($line, $delimitStart)
	{
		if($delimitStart == '{')
			$delimitEnd = '}';
		else
		{
			$delimitStart = '(';
			$delimitEnd = ')';
		}
		$count = 0;
		$count = substr_count($line, $delimitStart);
		$count += 0 - substr_count($line, $delimitEnd);
		return $count;
	}
// Extract a field
	function fieldSplit($seg)
	{
		$array = preg_split("/,\s*(\w+)\s*={1}\s*/U", $seg, PREG_SPLIT_DELIM_CAPTURE);
		if(!array_key_exists(1, $array))
			return array($array[0], FALSE);
		return array($array[0], $array[1]);
	}
// Extract and format fields
	function reduceFields($oldString)
	{
		$oldString = rtrim($oldString, "}),");
		$split = preg_split("/=/", $oldString, 2);
		$string = $split[1];
		while($string)
		{
			list($entry, $string) = $this->fieldSplit($string);
			$values[] = $entry;
		}
		foreach($values as $value)
		{
			$pos = strpos($oldString, $value);
			$oldString = substr_replace($oldString, '', $pos, strlen($value));
		}
		$rev = strrev(trim($oldString));
		if($rev{0} != ',')
			$oldString .= ',';
		$keys = preg_split("/=,/", $oldString);
		array_pop($keys);
		foreach($keys as $key)
		{
			$value = trim(array_shift($values));
			if(!trim($value))
				continue;
			$this->entries[$this->count][strtolower(trim($key))] = trim($this->removeDelimiters(trim($value)));
		}
	}
// Start splitting a bibtex entry into component fields.
// Store the entry type and citation.
	function fullSplit($entry)
	{
		$matches = preg_split("/@(.*)\s*[{(](.*),/U", $entry, 2, PREG_SPLIT_DELIM_CAPTURE);
		$this->entries[$this->count]['type'] = strtolower($matches[1]);
		$this->entries[$this->count]['id'] = $matches[2];
		$matches = $this->reduceFields($matches[3]);
	}
// Grab a complete bibtex entry
	function getEntry($line)
	{
		$entry = '';
		$count = 0;
		$lastLine = FALSE;
		if(preg_match("/@(.*)\s*([{(])/", $line, $matches))
		{
			do
			{
				$count += $this->braceCount($line, $matches[2]);
				$entry .= ' ' . $line;
				if(($line = $this->getLine()) === FALSE)
					break;
				$lastLine = $line;
			}
			while($count);
		}
		else
		{
			$line .= $this->getLine();
			$this->getEntry($line);
		}
		if(!array_key_exists(1, $matches))
			return $lastLine;
		if(preg_match("/string/i", $matches[1]))
			$this->strings[] = $entry;
		else if(preg_match("/preamble/i", $matches[1]))
			$this->preamble[] = $entry;
		else
		{
			if($this->fieldExtract)
				$this->fullSplit($entry);
			else
				$this->entries[$this->count] = $entry;
			$this->count++;
		}
		return $lastLine;
	}
// Remove enclosures around entry field values.
	function removeDelimiters($string)
	{
		if(!$this->removeDelimit)
			return $string;
// Remove any enclosing double quotes or braces around entry field values
		if($string{0} == "\"")
		{
			$string = substr($string, 1);
			$string = substr($string, 0, -1);
		}
		else if($string{0} == "{")
		{
			$string = substr($string, 1);
			$string = substr($string, 0, -1);
		}
		return $string;
	}
// This method starts the whole process
	function extractEntries()
	{
        $lastLine = FALSE;
        if($this->parseFile)
        {
            while(!feof($this->fid))
            {
                $line = $lastLine ? $lastLine : $this->getLine();
                if(!preg_match("/^@/i", $line))
                    continue;
                if(($lastLine = $this->getEntry($line)) !== FALSE)
                    continue;
            }
        }
        else{
            while($this->currentLine < count($this->string))
            {
                $line = $lastLine ? $lastLine : $this->getLine();
                if(!preg_match("/^@/i", $line))
                    continue;
                $this->getEntry($line);
            }
        }
	}
// Return arrays of entries etc. to the calling process.
	function returnArrays()
	{
		foreach($this->preamble as $value)
		{
			preg_match("/.*[{(](.*)/", $value, $matches);
			$preamble = substr($matches[1], 0, -1);
			$preambles['bibtexPreamble'] = trim($this->removeDelimiters(trim($preamble)));
		}
		if(isset($preambles))
			$this->preamble = $preambles;
		if($this->fieldExtract)
		{
			foreach($this->strings as $value)
			{
				$matches = preg_split("/@string[{(]/i", $value, 2);
				$string = substr($matches[1], 0, -1);
				$string = explode("=", $string, 2);
				$strings[trim($string[0])] = trim($this->removeDelimiters(trim($string[1])));
			}
		}
		if(isset($strings))
			$this->strings = $strings;
		if(empty($this->preamble))
			$this->preamble = FALSE;
		if(empty($this->strings))
			$this->strings = FALSE;
		if(empty($this->entries))
			$this->entries = FALSE;
		return array($this->preamble, $this->strings, $this->entries);
	}
}
?>
