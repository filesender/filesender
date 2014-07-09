<?php

/*
 * FileSender www.filesender.org
 * 
 * Copyright (c) 2009-2012, AARNet, Belnet, HEAnet, SURFnet, UNINETT
 * All rights reserved.
 * 
 * Redistribution and use in source and binary forms, with or without
 * modification, are permitted provided that the following conditions are met:
 * 
 * *	Redistributions of source code must retain the above copyright
 * 	notice, this list of conditions and the following disclaimer.
 * *	Redistributions in binary form must reproduce the above copyright
 * 	notice, this list of conditions and the following disclaimer in the
 * 	documentation and/or other materials provided with the distribution.
 * *	Neither the name of AARNet, Belnet, HEAnet, SURFnet and UNINETT nor the
 * 	names of its contributors may be used to endorse or promote products
 * 	derived from this software without specific prior written permission.
 * 
 * THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS "AS IS"
 * AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT LIMITED TO, THE
 * IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS FOR A PARTICULAR PURPOSE ARE
 * DISCLAIMED. IN NO EVENT SHALL THE COPYRIGHT HOLDER OR CONTRIBUTORS BE LIABLE
 * FOR ANY DIRECT, INDIRECT, INCIDENTAL, SPECIAL, EXEMPLARY, OR CONSEQUENTIAL
 * DAMAGES (INCLUDING, BUT NOT LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR
 * SERVICES; LOSS OF USE, DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER
 * CAUSED AND ON ANY THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY,
 * OR TORT (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE
 * OF THIS SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.
 */

/*
 * File Metadata functions
 */
class FileMetadata {
	
	const METADATA_SUFFIX = '.metadata';
	
	private $_filename;
	
	/**
	 * Constructor needs to know the filename of which this is metadata 
	 * @param String $filename full qualified path to the file, i.e. /usr/share/filesender/files/thefile
	 */
	function FileMetadata($filename) {
		$this->_filename = $filename.".metadata";
	}
	
	/**
	 * Create a new FileMetadata instance from the provided (full qualified) filename of a *metadata* file
	 * @param String $metadataFilename full qualified path to the file, i.e. /usr/share/filesender/files/thefile.metadata
	 */
	static function fromMetadataFilename($metadataFilename) {
		return new FileMetadata( static::sourceFilename($metadataFilename) );
	}
	
	/**
	 * Return the name of the file that the provided name of the metadata file describes
	 * @param String $metadatFilename name of the metadatafile - must be valid metadata file name!
	 */
	static function sourceFilename($metadataFilename) {
		$l = strlen($metadataFilename) - strlen(static::METADATA_SUFFIX);
		return substr($metadataFilename, 0, $l);
	}
	
	/**
	 * Return whether a provided filename is a FileSender metadata file
	 * @param unknown $filename
	 */
	static function isMetadataFile($filename) {
		return substr($filename, -strlen(static::METADATA_SUFFIX)) === static::METADATA_SUFFIX;
	}
	
	function create() {
		file_put_contents( $this->_filename, "");	// force empty
	}
	
	/**
	 * Rename the metadata file to match the new name of the new sourcefile
     * @param String $newfilename the new name of the sourcefile
     */
	function rename($newfilename) {
		return rename($this->_filename, $newfilename.static::METADATA_SUFFIX);
	}
	
	/**
	 * Static helper to rename a metadata file to match the new name of the new sourcefile
	 * @param String $oldfilename the old name of the sourcefile
	 * @param String $newfilename the new name of the sourcefile
	 */
	static function renameFileMetadataFile($oldfilename, $newfilename) {
		return rename($oldfilename.static::METADATA_SUFFIX, $newfilename.static::METADATA_SUFFIX);
	}
	
	/**
	 * Return the name of the metadata file without any path information
	 * @return string
	 */
	function getFilename() {
		return basename($this->_filename);
	}
	
	/**
	 * Return the absolute path and filename of the metadata file
	 * @return string
	 */
	function getAbsoluteFilename() {
		return $this->_filename;
	}
	
	/**
	 * Adds a chunk to the metadata
	 * @param unknown $nr first byte that is part of the chunk, inclusive (1-based)
	 * @param unknown $start last byte that is part of the chunk, exclusive
	 * @param unknown $end
	 */
	function addChunkData($nr, $start, $end) {
		$data = sprintf("chunk,%d,%d,%d\n", $nr, $start, $end);
		file_put_contents( $this->_filename, $data, FILE_APPEND ) or die("Error");
	}
	
	/**
	 * convert chunk to json structure:<br/>
	 * { "c" : [int: chunknr],<br/>
	 *   "s" : [int: position of beginning of chunk in file],<br/>
	 *   "e" : [int: position of end of chunk in file],<br/>
	 *   "cs: : [int: size of this chunk]<br/>
	 * }<br/>
	 *   
	 * @param string chunktype,chunknr,chunkbegin,chunkend
	 * @return NULL|string
	 */
	private function _chunkToJson($chunk) {
		$a = split ( ",", $chunk );
		
		if (count ( $a ) < 4) return null; // corrupt metadata
		return sprintf ( '{"c":%d,"s":%d,"e":%d,"cs":%d}', $a[1], $a[2], $a[3], $a[3]-$a[2] );
	}
	
	private function _chunkToArray($chunk) {
		$a = split ( ",", $chunk );
		
		if (count ( $a ) < 4) return null; // corrupt metadata
		return array('nr' => $a[1], 'start' => $a[2], 'end' => $a[3]);
	}
	
	/**
	 * Retrieve JSON data-structure as:<br/>
	 * [ chunk, chunk, ... , chunk ]
	 * @param string $onlychunkdata only include chunk-data in the metadata output (default true)
	 * @return string string representation of the json structure
	 */
	function getJsonContents($onlychunkdata = true) {
		if (! file_exists($this->_filename)) return '{"error":"no metadata exists"}';
		
		$filedata = file_get_contents($this->_filename);	// just get it as a whole
		$jsonresult = "[";	// hand-built json for performance reasons, only because simple input data allows it
		
		$separator = "\r\n";
		$line = strtok($filedata, $separator);
		$first = true;
		
		while ($line !== false) {
			if ($onlychunkdata && (! (strpos($line, 'chunk') === 0))) continue; 
		
			$jsonresult .= ($first?"":",") . FileMetadata::_chunkToJson($line);
			
			if ($first) $first=false;
			
			$line = strtok( $separator );
		}
		
		$jsonresult .= "]";
		
		return $jsonresult;
	}
	
	function getLastChunkRaw() {
		$filedata = file_get_contents($this->_filename);	// just get it as a whole
		$lastchunkpost = strrpos($filedata, 'chunk');
		$lastchunk = substr($filedata, $lastchunkpost);
		
		$separator = "\r\n";
		$line = strtok($lastchunk, $separator);
		
		return $line;
	}
	
	/**
	 * Retrieve the JSON-representation of the chunk that was registered the last
	 * @return Ambigous <NULL, string>
	 */
	function getJsonLastChunk() {
		return FileMetadata::_chunkToJson($this->getLastChunkRaw());
	}
	
	function addChunkDataChunksize($chunksize) {
		if (! intval($chunksize) > 0) return false;
		
		$lastchunk = $this->_chunkToArray($this->getLastChunkRaw());
		
		if ($lastchunk == null) {
			$this->addChunkData(1, 1, $chunksize);	// this is the first chunk, start counting with 1
		} else {
			$this->addChunkData($lastchunk['nr']+1, $lastchunk['end']+1, $lastchunk['end']+$chunksize);
		}
		return true;
	}
	
}