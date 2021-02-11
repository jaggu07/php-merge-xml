<?php

/**
 * PHP MergeXML usage sample
 * merge multi-selected local xml files
 * 
 * @package     MergeXML
 * @author      Vallo Reima
 * @copyright   (C)2014, 2019
 */
date_default_timezone_set('UTC');
ini_set('display_errors', true);
ini_set('log_errors', true);
ini_set('memory_limit', '-1');


$filename = "export_" . date("Y.m.d") . ".xml";

require 'mergexml.php';    /* load the class */
$oMX = new MergeXML(['updn'=>true]);

if ($oMX->error->code == '') {
  $fls = !empty($_FILES) ? $_FILES : ['file0' => ['name' => '']];
  $rsp = FileMerge($oMX, $fls);
} else {
  $rsp = $oMX->error->text; /* missing feature */
}
$tmpCert = tmpfile();
file_put_contents($filename, (string)$rsp);
header('Content-type: application/xml');
header('Content-Disposition: attachment; filename="' . $filename . '"');
echo $rsp;
die;

/**
 * merge uploaded files
 * @param object $xml -- class instance
 * @param array $fls -- uploaded files
 * @return string
 */
function FileMerge(MergeXML $xml, $fls) {
  reset($fls);
  $key = key($fls); /* independently from attribute names */

  if (is_array($fls[$key]['name'])) { /* multiselect */
    for ($i = 0; $i < count($fls[$key]['name']); $i++) {
      $name = $fls[$key]['name'][$i];
      if (!empty($name) && !$xml->AddFile($fls[$key]['tmp_name'][$i])) {
        break;
      }
    }
  } else {
    foreach ($fls as $fle) {
      $name = $fle['name'];
      if (!empty($name) && !$xml->AddFile($fle['tmp_name'])) {
        break;
      }
    }
  }

  if ($xml->error->code != '') {
    $rtn = $xml->error->text . ': ' . $name;
  } else if ($xml->count < 2) {
    $rtn = 'Minimum 2 files are required';
  } else {
    $rtn = $xml->Get(1);
    header("Content-Type: text/plain; charset={$xml->dom->encoding}");
   }
// echo $rtn;
  return $rtn;
}
