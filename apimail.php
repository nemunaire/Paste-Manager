#!/usr/bin/php
<?php

require_once("common.php");

// Search an author (in the first arg)
if (isset($argv[1]))
  $ffrom = $argv[1];
else
  $ffrom = "";

// Search a title for the paste (in the second arg)
if (isset($argv[2]))
  $subject = $argv[2];
else
  $subject = "";

// Receive mail content
$content = file("php://stdin");


$cnt = array();
$boundary = null;
$pass = false;
$i = -1;

foreach($content as $k => $line)
{
  // Separate body email content
  if (substr($line, 0, 2 + strlen($boundary)) == "--".$boundary)
  {
    $cnt[] = "";
    $i++;
    $pass = true;
  }

  // Don't save headers
  else if (($pass || empty($boundary)) && (trim($line) == "" || !empty($cnt[$i])))
  {
    if ($i < 0)
    {
      $cnt[] = "";
      $i++;
    }
    $cnt[$i] .= $line;
  }

  // Save email part separator
  else if (preg_match("#^Content-Type: [^;]+; boundary=\"(.+)\"#", $line, $out))
    $boundary = $out[1];

  // Read From field if $ffrom is empty
  else if (empty($ffrom) && preg_match("#^From: (.+)#", $line, $out))
    $ffrom = $out[1];

  // Read Subject field if $subject is empty
  else if (empty($subject) && preg_match("#^Subject: (.+)#", $line, $out))
    $subject = $out[1];
}


// Extract username instead of email adress if it exists
if (preg_match("#([^<]+) <#ui", $ffrom, $out))
  $from = $out[1];
else
  $from = $ffrom;


// Create the paste
$paste = new Paste();
$paste->title = $subject;
$paste->author = $from;
$paste->date = time();
$paste->content = utf8_encode(trim($cnt[max(0,$i-1)]));

// Save the paste and give read right to all users (if mail user is different from php one)
$link = $paste->save();
chmod(Paste::get_path($paste->filename), 0644);


// Send confirmation email
$headers = 'From: paste@p0m.fr' . "\r\n" .
  'Content-Type: text/plain; charset="utf-8"' . "\r\n" .
  'X-Mailer: '.ucfirst(HTTP_URL);
mail($ffrom, "Re: ".$subject, "Bonjour,\n\nVotre paste a bien été publié à l'adresse suivante :\nhttp://".HTTP_URL."/?".$link."\n\n-- \n".HTTP_URL, $headers);
?>