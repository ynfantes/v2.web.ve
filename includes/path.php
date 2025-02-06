<?php
/**
 * @version		$Id: path.php 20196 2011-01-09 02:40:25Z ian $
 * @package		Joomla.Framework
 * @subpackage	FileSystem
 * @copyright	Copyright (C) 2005 - 2011 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

// no direct access
//defined('JPATH_BASE') or die;

/** boolean True if a Windows based host */
//define('JPATH_ISWIN', (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN'));

/** boolean True if a Mac based host */
//define('JPATH_ISMAC', (strtoupper(substr(PHP_OS, 0, 3)) === 'MAC'));

//if (!defined('DS')) {
	/** string Shortcut for the DIRECTORY_SEPARATOR define */
//	define('DS', DIRECTORY_SEPARATOR);
//}

//if (!defined('JPATH_ROOT')) {
//	/** string The root directory of the file system in native format */
//	define('JPATH_ROOT', JPath::clean(JPATH_SITE));
//}

/**
 * A Path handling class
 *
 * @package		Joomla.Framework
 * @subpackage	FileSystem
 * @since		1.5
 */
class JPath
{
	/**
	 * Checks if a path's permissions can be changed
	 *
	 * @param	string	Path to check
	 * @return	boolean	True if path can have mode changed
	 * @since	1.5
	 */
	public static function canChmod($path)
	{
		$perms = fileperms($path);
		if ($perms !== false) {
			if (@chmod($path, $perms ^ 0001)) {
				@chmod($path, $perms);
				return true;
			}
		}
		return false;
	}

	/**
	 * Chmods files and directories recursivly to given permissions
	 *
	 * @param	string	Root path to begin changing mode [without trailing slash]
	 * @param	string	Octal representation of the value to change file mode to [null = no change]
	 * @param	string	Octal representation of the value to change folder mode to [null = no change]
	 * @return	boolean	True if successful [one fail means the whole operation failed]
	 * @since	1.5
	 */
	public static function setPermissions($path, $filemode = '0644', $foldermode = '0755')
	{
		// Initialise return value
		$ret = true;

		if (is_dir($path)) {
			$dh = opendir($path);

			while ($file = readdir($dh)) {
				if ($file != '.' && $file != '..') {
					$fullpath = $path.'/'.$file;
					if (is_dir($fullpath)) {
						if (!JPath::setPermissions($fullpath, $filemode, $foldermode)) {
							$ret = false;
						}
					} else {
						if (isset ($filemode)) {
							if (!@ chmod($fullpath, octdec($filemode))) {
								$ret = false;
							}
						}
					} // if
				} // if
			} // while
			closedir($dh);
			if (isset ($foldermode)) {
				if (!@ chmod($path, octdec($foldermode))) {
					$ret = false;
				}
			}
		} else {
			if (isset ($filemode)) {
				$ret = @ chmod($path, octdec($filemode));
			}
		} // if
		return $ret;
	}

	/**
	 * Searches the directory paths for a given file.
	 *
	 * @param	array|string	An path or array of path to search in
	 * @param	string			The file name to look for.
	 * @return	mixed			The full path and file name for the target file, or boolean false if the file is not found in any of the paths.
	 * @since	1.5
	 */
	public static function find($paths, $file)
	{
		settype($paths, 'array'); //force to array

		// start looping through the path set
		foreach ($paths as $path) {
			// get the path to the file
			$fullname = $path.'/'.$file;

			// is the path based on a stream?
			if (strpos($path, '://') === false) {
				// not a stream, so do a realpath() to avoid directory
				// traversal attempts on the local file system.
				$path = realpath($path); // needed for substr() later
				$fullname = realpath($fullname);
			}

			// the substr() check added to make sure that the realpath()
			// results in a directory registered so that
			// non-registered directores are not accessible via directory
			// traversal attempts.
			if (file_exists($fullname) && substr($fullname, 0, strlen($path)) == $path) {
				return $fullname;
			}
		}

		// could not find the file in the set of paths
		return false;
	}
}