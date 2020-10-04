<?php
// ------------------------------------------------------------------------
// ----------------------- ENTER YOUR DETAILS HERE! -----------------------
// ------------------------------------------------------------------------
// quickmatch : Simplified version of what you are looking for
// fullmatch : RegEx pattern for specifics (permits negations/lookahead/lookbehind etc.)
// ignoreextensions : for efficiency/error avoidance, file types to skip looking at

$quickmatch = array("href", "window.location", "window.open");
$fullmatch = "(\b\.href\b|\"href\"|href=\"\'|href=\'\"|\bwindow.open\b|\bwindow.location\b)";
$ignoreextensions = array('png', 'gif', 'jpg', 'jpeg', 'webp', 'xml', 'doc', 'xls', 'pdf', 'txt');









// ------------------------------------------------------------------------
// ----------------------- DO NOT TOUCH BELOW HERE! -----------------------
// ------------------------------------------------------------------------

function scanFor($path, $quickmatch, $fullmatch, $ignoreextensions){
	$dir = new DirectoryIterator($path);
	$matches = array();

	// FILES FIRST
	$filesarray = array();
	foreach ($dir as $file){
		if(!$file->isDot()  && !$file->isDir()){ // NOT . or .. or /dir
			if (!in_array($file->getExtension(), $ignoreextensions)) {
				if($file->getPathname() != __FILE__){
					$filesarray[$file->getPathname()] = $file->getBasename()."<br/>";
				}
			}
		}
	}
	asort($filesarray);
	
	
	
	// DIRECTORIES SECOND
	$dirsarray = array();
	foreach ($dir as $file){
		if(!$file->isDot()  && $file->isDir()){ // NOT . or .. or /file
			$dirsarray[] = $file->getPathname();
		}
	}
	asort($dirsarray);
	$tempdirssarray = array();
	foreach($dirsarray as $recurse){
		$tempdirssarray = array_merge( $tempdirssarray, scanFor($recurse, $quickmatch, $fullmatch, $ignoreextensions) ); 
	}
	
	
	
	// COMBINE the various Arrays
	$myarray = array_merge($filesarray, $tempdirssarray);
	
	
	
	// Now we SEARCH in the various files.
	foreach($myarray as $filepath => $filename){ 
		$content = file_get_contents($filepath);

		$haveamatch = false;
		foreach($quickmatch as $qm){
			if (strpos($content, $qm) !== false){
				$haveamatch = true;
				break;
			}
		}

		
		if ($haveamatch) {
			// We have a Match!
			// Now the basic search has been done, let's do some REGEX for refinement
			
			
			$file = fopen($filepath, "r"); 
			$linecounter = 1;
			while (($line = fgets($file)) !== false) {  
    			if(preg_match_all($fullmatch, $line)){
					$matches[$filepath] = $linecounter;
				}
				$linecounter++;
			}  
	    } 
		
	}
	
	
	return $matches;
}





// ------------------------------------------------------------------------
// --------------------- OUTPUTTING RESULTS (if any!) ---------------------
// ------------------------------------------------------------------------

echo "Scanning for : ". implode(" , ", $quickmatch) ."<hr/>";
$results = scanFor(getcwd(), $quickmatch, $fullmatch, $ignoreextensions);

if($results){
	echo "<table>";
	echo "<thead><tr><th>File Path</th><th>Line No.</th></tr></thead>";
	echo "<tbody>";
	foreach($results as $key=>$result){
		echo "<tr><td style='padding-right:10px;'>". $key ."</td><td>". $result ."</td></tr>";
	}
	echo "</tbody>";
	echo "</table>";
}

