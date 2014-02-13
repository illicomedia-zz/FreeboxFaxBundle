FreeFaxBundle
=============

Symfony2 bundle to send a FAX using your Freebox account.

Usage inside a Controller :


/** @var FreeboxFax $fax *
$fax = $this->get("freebox.fax");

$fax->setRecipientNumber("0102030405")
    ->setWithEmailReport()
    ->setMaskMyNumber(false)
    ->setFilePath(__DIR__."/my_fax.pdf")
    ->send();