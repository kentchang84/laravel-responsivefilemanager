<?php
/**
 * @author Alberto Peripolli https://responsivefilemanager.com/#contact-section
 * @source https://github.com/trippo/ResponsiveFilemanager
 * 
 * Licenced under Attribution-NonCommercial 3.0 Unported (CC BY-NC 3.0) 
 * https://creativecommons.org/licenses/by-nc/3.0/
 * 
 * This work is licensed under the Creative Commons 
 * Attribution-NonCommercial 3.0 Unported License.
 * To view a copy of this license, visit 
 * http://creativecommons.org/licenses/by-nc/3.0/ or send a 
 * letter to Creative Commons, 444 Castro Street, Suite 900, 
 * Mountain View, California, 94041, USA.
 */

require_once __DIR__.'/include/utils.php';

use ResponsiveFileManager\RFM;

define('FM_mb_internal_encoding', 'UTF-8');
define('FM_mb_http_output', 'UTF-8');
define('FM_mb_http_input', 'UTF-8');
define('FM_mb_language', 'uni');
define('FM_mb_regex_encoding', 'UTF-8');
define('FM_ob_start', 'mb_output_handler');
define('FM_date_default_timezone_set', 'Europe/Rome');
define('setlocale', 'en_US');
setlocale(LC_CTYPE, 'en_US'); //correct transliteration

// ALLOW Crossscript for resource load
header("content-type: text/html; charset=UTF-8");
header("Access-Control-Allow-Origin: https://code.jquery.com");
header('Access-Control-Allow-Credentials: true');
header('Access-Control-Max-Age: 86400');    // cache for 1 day
header('Access-Control-Allow-Headers: X-Requested-With');

// Access-Control headers are received during OPTIONS requests
if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {

    if (isset($_SERVER['HTTP_ACCESS_CONTROL_REQUEST_METHOD']))
        header("Access-Control-Allow-Methods: GET, POST, OPTIONS");         

    if (isset($_SERVER['HTTP_ACCESS_CONTROL_REQUEST_HEADERS']))
        header("Access-Control-Allow-Headers:        {$_SERVER['HTTP_ACCESS_CONTROL_REQUEST_HEADERS']}");

    exit(0);
}

mb_internal_encoding(FM_mb_internal_encoding);
mb_http_output(FM_mb_http_output);
mb_http_input(FM_mb_http_input);
mb_language(FM_mb_language);
mb_regex_encoding(FM_mb_regex_encoding);
ob_start(FM_ob_start);
date_default_timezone_set(FM_date_default_timezone_set);

$config = config('rfm');

/**
 * Language init
 */
if ( ! session()->exists('RF.language')
	|| file_exists(__DIR__.'/lang/' . basename(session('RF.language')) . '.php') === false
	|| ! is_readable(__DIR__.'/lang/' . basename(session('RF.language')) . '.php')
)
{
	$lang = $config['default_language'];

	if (isset($_GET['lang']) && $_GET['lang'] != 'undefined' && $_GET['lang'] != '')
	{
		$lang = RFM::fix_get_params($_GET['lang']);
		$lang = trim($lang);
	}

	if (isset($_GET['langCode']) && $_GET['langCode'] != 'undefined' && $_GET['langCode'] != '')
	{
		$lang = RFM::fix_get_params($_GET['langCode']);
		$lang = trim($lang);
	}
	if ($lang != $config['default_language'])
	{
		$path_parts = pathinfo($lang);
		$lang = $path_parts['basename'];
		$languages = include __DIR__.'/lang/languages.php';
		$f = false;
		array_walk(
			$languages,
			function ($fulllanguage, $isocode) use (&$lang, &$f) {
				if(strpos($isocode, $lang) !== false) {
					$f = true;
					$lang = $isocode;
				}
			}
		);
		if(!$f) $lang = $config['default_language'];
	}

	// add lang file to session for easy include
	session()->put('RF.language', $lang);
}
else
{
	if(file_exists(__DIR__.'/lang/languages.php')){
		$languages = include __DIR__.'/lang/languages.php';
	}else{
		$languages = include __DIR__.'/../lang/languages.php';
	}

	if(array_key_exists(session('RF.language'),$languages)){
		$lang = session('RF.language');
	}else{
		RFM::response('Lang_Not_Found'.AddErrorLocation())->send();
		exit;
	}

}
if(file_exists(__DIR__.'/lang/' . $lang . '.php')){
	$GLOBALS['lang_vars'] = include __DIR__.'/lang/' . $lang . '.php';
}else{
	$GLOBALS['lang_vars'] = include __DIR__.'/../lang/' . $lang . '.php';
}

if ( ! is_array($GLOBALS['lang_vars']))
{
	$GLOBALS['lang_vars'] = array();
}
