<?php
/**
 * (c) Frédéric VIALLET <contact@illicomedia.com>
 *
 * @author Frédéric VIALLET <contact@illicomedia.com>
 *
 * Creation Date: 09/01/14 10:14
 */
namespace Illicomedia\Freebox\FaxBundle\Service;

class FreeboxFax
{
    const LOGIN_ENDPOINT        = "https://subscribe.free.fr/login/login.pl";
    const FAX_SEND_ENDPOINT     = "https://adsl.free.fr/tel_ulfax.pl";
    const FAX_LIST_ENDPOINT     = "https://adsl.free.fr/tel_fax.pl";

    /** @var  string */
    protected $login;

    /** @var  string */
    protected $password;

    /** @var  bool */
    protected $mask_my_number;

    /** @var  string */
    protected $recipient_number;

    /** @var  bool */
    protected $with_email_report;

    /** @var  string */
    protected $file_path;

    /**
     * @param string $login
     * @param string $password
     */
    function __construct($login, $password)
    {
        $this->login = $login;
        $this->password = $password;

        $this->mask_my_number = false;
        $this->with_email_report = false;
    }

    /**
     * @param string $login
     *
     * @return FreeboxFax
     */
    public function setLogin($login)
    {
        $this->login = $login;

        return $this;
    }

    /**
     * @param string $password
     *
     * @return FreeboxFax
     */
    public function setPassword($password)
    {
        $this->password = $password;

        return $this;
    }

    /**
     * @param boolean $mask_my_number
     *
     * @return FreeboxFax
     */
    public function setMaskMyNumber($mask_my_number = true)
    {
        $this->mask_my_number = $mask_my_number;

        return $this;
    }

    /**
     * @return boolean
     */
    public function getMaskMyNumber()
    {
        return $this->mask_my_number;
    }

    /**
     * @param boolean $with_email_report
     *
     * @return FreeboxFax
     */
    public function setWithEmailReport($with_email_report = true)
    {
        $this->with_email_report = $with_email_report;

        return $this;
    }

    /**
     * @return boolean
     */
    public function isWithEmailReport()
    {
        return $this->with_email_report;
    }

    /**
     * @param $file_path
     *
     * @return $this
     * @throws \ErrorException
     */
    public function setFilePath($file_path)
    {
        if (!file_exists($file_path))
            throw new \ErrorException("File not found : ".$file_path);

        $this->file_path = realpath($file_path);

        return $this;
    }

    /**
     * @return string
     */
    public function getFilePath()
    {
        return $this->file_path;
    }

    /**
     * @param string $recipient_number
     *
     * @return FreeboxFax
     */
    public function setRecipientNumber($recipient_number)
    {
        $this->recipient_number = $recipient_number;

        return $this;
    }

    /**
     * @return string
     */
    public function getRecipientNumber()
    {
        return $this->recipient_number;
    }

    /**
     * @return bool
     * @throws \LogicException
     * @throws \ErrorException
     */
    public function send()
    {
        if (empty($this->recipient_number))
            throw new \LogicException("Recipient number must be set !");

        if (empty($this->file_path))
            throw new \LogicException("File must be set !");

        $session = $this->getSession();

        $cfile = curl_file_create($this->file_path, mime_content_type($this->file_path), basename($this->file_path));

        $fax = array(
            "id"    => $session["id"],
            "idt"   => $session["idt"],

            "masque"        => $this->mask_my_number ? "Y" : "",
            "destinataire"  => $this->recipient_number,
            "email_ack"     => $this->with_email_report ? 1 : 0,
            "document"      => $cfile
        );

        //print_r($fax);

        $ch = curl_init(self::FAX_ENDPOINT);
        curl_setopt($ch, CURLOPT_USERAGENT,         "Mozilla/5.0 (Windows NT 6.1; WOW64; rv:13.0");
        curl_setopt($ch, CURLOPT_POST,              true);
        curl_setopt($ch, CURLOPT_POSTFIELDS,        $fax);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER,    true);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION,    true);
        curl_setopt($ch, CURLOPT_TIMEOUT,           120);
        curl_setopt($ch, CURLOPT_HEADER,            false);
        curl_setopt($ch, CURLOPT_HTTPHEADER,        array("Content-Type: multipart/form-data"));
        curl_setopt($ch, CURLOPT_VERBOSE,           false);

        // SSL
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER,    false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST,    2);

        $response = curl_exec($ch);

        if(curl_errno($ch))
            throw new \ErrorException("Fail to send the fax (".curl_error($ch).")");

        return true;
    }

    /**
     * @return array
     * @throws \ErrorException
     */
    private function getSession()
    {
        $credentials = array(
            "login"     => $this->login,
            "pass"      => $this->password,
            "ok"        => "Envoyer"
        );

        $ch = curl_init(self::LOGIN_ENDPOINT);
        curl_setopt($ch, CURLOPT_POST,              true);
        curl_setopt($ch, CURLOPT_POSTFIELDS,        http_build_query($credentials));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER,    true);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION,    true);
        curl_setopt($ch, CURLOPT_TIMEOUT,           15);
        curl_setopt($ch, CURLOPT_HEADER,            false);
        curl_setopt($ch, CURLOPT_VERBOSE,           false);

        // SSL
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER,    false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST,    2);

        $homepage = curl_exec($ch);

        if(curl_errno($ch))
            throw new \ErrorException("Fail to sign in (".curl_error($ch).")");
        else
        {
            file_put_contents("m:fax.html", $homepage);

            $home_url = curl_getinfo($ch, CURLINFO_EFFECTIVE_URL);

            $query = parse_url($home_url, PHP_URL_QUERY);

            parse_str($query, $session);
            curl_close($ch);

            return $session;
        }
    }

    /**
     * @return array
     */
    public function getFaxList()
    {
        $session = $this->getSession();

        $page = file_get_contents(self::FAX_LIST_ENDPOINT."?".http_build_query($session));

        echo $page;

        $list = array();

        return $list;
    }

    public function getFaxStatus($fax_id)
    {
        return true;
    }
}
