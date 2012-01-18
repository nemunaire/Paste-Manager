<?php
require_once("../common.php");

if (!empty($_POST["content"]))
  {
    $xml = new DOMDocument('1.0', 'UTF-8');
    $xml->formatOutput   = true;

    $xml_paste = $xml->createElement("paste");

    $xml_paste->appendChild(
        $xml->createElement("title", $_POST["title"]));
    $xml_paste->appendChild(
        $xml->createElement("author", $_POST["author"]));
    $xml_paste->appendChild(
        $xml->createElement("language", $_POST["lang"]));
    $xml_paste->appendChild(
        $xml->createElement("date", time()));
    $xml_paste->appendChild(
        $xml->createElement("ip", $_SERVER["REMOTE_ADDR"]));
    $xml_paste->appendChild(
        $xml->createElement("content", $_POST["content"]));

    if (!empty($_POST["ref"]))
      $xml_paste->appendChild(
          $xml->createElement("ref", $_POST["ref"]));

    $hash = base64_encode(md5($_POST["content"], true));
    $xml_paste->appendChild(
        $xml->createElement("hash", $hash));

    $xml->appendChild($xml_paste);

    //Save the paste
    $filename = substr(
		  str_replace("+", "",
		    str_replace("/", "",
		      $hash)), 0, NB_CHAR);

    if ($xml->save(DESTINATION . "/" . $filename . ".xml"))
      {
	//Redirect the user to is paste
	header("Location: /?".$filename);
	exit;
      }
    else
      die ("Sorry, an error occured while saving the file. Please try again later.");
  }

header("Location: /");
?>