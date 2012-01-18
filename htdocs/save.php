<?php
require_once("../common.php");

if (!empty($_POST["content"]))
  {
    $xml = new DOMDocument('1.0', 'UTF-8');
    $xml->formatOutput   = true;

    $xml_paste = $xml->createElement("paste");

    $xml_paste->appendChild($xml->createElement("title", $_POST["title"]));
    $xml_paste->appendChild($xml->createElement("author", $_POST["author"]));
    $xml_paste->appendChild($xml->createElement("language", $_POST["lang"]));
    $xml_paste->appendChild($xml->createElement("date", time()));
    $xml_paste->appendChild($xml->createElement("ip", $_SERVER["REMOTE_ADDR"]));
    $xml_paste->appendChild($xml->createElement("content", $_POST["content"]));

    $xml->appendChild($xml_paste);

    $filename = substr(str_replace("+", "", str_replace("/", "", base64_encode(md5($xml->saveXML(), true)), 0, 5)));

    $xml->save(DESTINATION . "/" . $filename . ".xml");

    header("Location: /?".$filename);
    exit;
  }

header("Location: /");
?>