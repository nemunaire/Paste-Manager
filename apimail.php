#!/usr/bin/php
<?php

require_once("common.php");

if (isset($argv[1]))
{
  $ffrom = $argv[1];
  if (preg_match("#([^<]+) <#ui", $argv[1], $out))
    $from = $out[1];
  else
    $from = $argv[1];
}
else
  $from = $ffrom = "";

if (isset($argv[2]))
  $subject = $argv[2];
else
  $subject = "";

$content = file("php://stdin");

$cnt = array();
$boundary = null;
$pass = false;
$i = -1;
foreach($content as $k => $line)
{
  if (substr($line, 0, 2 + strlen($boundary)) == "--".$boundary)
  {
    $cnt[] = "";
    $i++;
    $pass = true;
  }
  else if (($pass || empty($boundary)) && (trim($line) == "" || !empty($cnt[$i])))
  {
    if ($i < 0)
    {
      $cnt[] = "";
      $i++;
    }
    $cnt[$i] .= $line;
  }
  else if (preg_match("#Content-Type: [^;]+; boundary=\"(.+)\"#", $line, $out))
    $boundary = $out[1];
}

$paste = new Paste();
$paste->title = $subject;
$paste->author = $from;
$paste->date = time();
$paste->content = trim($cnt[$i-1]);

$link = $paste->save();
chmod(Paste::get_path($paste->filename), 0666);

$headers = 'From: paste@p0m.fr' . "\r\n" .
  'Content-Type: text/plain; charset="utf-8"' . "\r\n" .
  'X-Mailer: Paste.p0m.fr';

mail($ffrom, "Re: ".$subject, "Bonjour,\n\nVotre paste a bien été publié à l'adresse suivante :\nhttp://paste.p0m.fr/?".$link."\n\n-- \npaste.p0m.fr", $headers);
?>