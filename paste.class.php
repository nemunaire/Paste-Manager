<?php

require_once("common.php");

class Paste
{
  var $filename;
  var $fileref = NULL;
  var $title;
  var $language;
  var $author;
  var $date;
  var $content;
  var $ip;
  var $ref = NULL;
  var $hash;

  /**
   * Create a new paste or open a existing file
   */
  function __construct($filename=NULL)
  {
    if (!empty($filename))
    {
      $this->filename = Paste::get_path($filename);

      if ($this->load())
        $this->fileref = $filename;
    }
  }

  /**
   * Load or reload the file and set attributes
   */
  function load()
  {
    if (is_file($this->filename))
    {
      $doc = new DOMDocument();
      $doc->load($this->filename);

      $this->title = utf8_decode(
        $doc->getElementsByTagName("title")->item(0)->textContent);
      $this->language = strtolower(
        $doc->getElementsByTagName("language")->item(0)->textContent);
      $this->date = $doc->getElementsByTagName("date")->item(0)->textContent;
      $this->author = utf8_decode(
        $doc->getElementsByTagName("author")->item(0)->textContent);
      $this->content = $doc->getElementsByTagName("content")->item(0)->textContent;
      if ($doc->getElementsByTagName("ref")->length > 0)
        $this->ref = $doc->getElementsByTagName("ref")->item(0)->textContent;
      else
        $this->ref = NULL;
      $this->ip = $doc->getElementsByTagName("ip")->item(0)->textContent;
      $this->hash = $doc->getElementsByTagName("hash")->item(0)->textContent;

      //Check the lang exists
      if (empty($this->language) || !is_file(GESHI_DIR.$this->language.".php"))
        $this->language = "text";

      return TRUE;
    }
    else
      return FALSE;
  }

  static function get_path($filename)
  {
    return DESTINATION . "/" . $filename . ".xml";
  }

  static function speed_cmp($filename, $hash)
  {
    if (is_file($filename))
    {
      $doc = new DOMDocument();
      $doc->load($filename);

      return ($hash == $doc->getElementsByTagName("hash")->item(0)->textContent);
    }
    else
      return FALSE;    
  }

  /**
   * Save the current paste
   */
  function save($filename=NULL)
  {
    $this->date = time();
    $this->hash = base64_encode(sha1($this->content, true));
    $this->ip = $_SERVER["REMOTE_ADDR"];

    if (empty($filename))
    {
      $i = 0;
      do
      {
        $filename = substr(
        str_replace("+", "",
	  str_replace("/", "",
	    $this->hash)), $i++, NB_CHAR);
      }
      //If the file already exists, find another name if the content is different
      while(is_file(Paste::get_path($filename))
            && !Paste::speed_cmp(Paste::get_path($filename), $hash));
    }
    $this->filename = $filename;

    $xml = new DOMDocument('1.0', 'UTF-8');
    $xml->formatOutput   = true;

    $xml_paste = $xml->createElement("paste");

    $xml_paste->appendChild(
        $xml->createElement("title", $this->title));
    $xml_paste->appendChild(
        $xml->createElement("author", $this->author));
    $xml_paste->appendChild(
        $xml->createElement("language", $this->language));
    $xml_paste->appendChild(
        $xml->createElement("date", $this->date));
    $xml_paste->appendChild(
        $xml->createElement("ip", $this->ip));
    $xml_paste->appendChild(
        $xml->createElement("content", $this->content));

    if (!empty($this->ref))
      $xml_paste->appendChild(
          $xml->createElement("ref", $this->ref));

    $xml_paste->appendChild(
        $xml->createElement("hash", $this->hash));

    $xml->appendChild($xml_paste);

    if ($xml->save(Paste::get_path($this->filename)))
      return $this->filename;
    else
    {
      die ("Sorry, an error occured while saving the file. Please try again later.");
      return FALSE;
    }
  }

  function create($dict)
  {
    $this->title = $dict["title"];
    $this->author = $dict["author"];
    $this->language = $dict["lang"];
    if (isset($dict["ref"]))
      $this->ref = $dict["ref"];

    //TODO: allow uploading file
    $this->content = $dict["content"];
  }

  function get_subtitle()
  {
    if (empty($this->author))
      $author = "<em>un inconnu</em>";
    else
      $author = htmlentities($this->author);

    return "Posté par ".$author.", le ".strftime("%A %e %B %G à %H:%M:%S",
                                                 $this->date);
  }

  function get_ref($is_diff)
  {
    if (!empty($this->ref))
    {
      if (empty($is_diff))
        return '<a href="/?'.$this->ref.'">Voir l\'original</a> '.
        '<a href="/?'.$this->fileref.':'.$this->ref.'">Voir la différence</a>';
      else
        return '<a href="/?'.$this->fileref.'">Cacher les différences</a> ';
    }
    else
      return "";
  }

  function get_diff($diff)
  {
    require_once("geshi/geshi.php");
    require_once("simplediff.php");

    $geshi = new GeSHi(htmlDiff($diff->content, $this->content),
                       $this->language);

    return str_replace("&lt;ins&gt;", "<ins>",
           str_replace("&lt;/ins&gt;", "</ins>",
           str_replace("&lt;del&gt;", "<del>",
           str_replace("&lt;/del&gt;", "</del>",
                       $geshi->parse_code()))));
  }
}

?>