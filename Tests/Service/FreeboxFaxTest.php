<?php

namespace Illicomedia\Freebox\FaxBundle\Tests\Service;

use Illicomedia\Freebox\FaxBundle\Service\FreeboxFax;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class FreeboxFaxTest extends WebTestCase
{/*
    public function testSend()
    {
        $client = static::createClient();

        /** @var FreeboxFax $fax *
        $fax = $client->getContainer()->get("freebox.fax");

        $fax->setRecipientNumber("0102030405")
            ->setWithEmailReport()
            ->setMaskMyNumber(false)
            ->setFilePath(__DIR__."/test.txt")
            ->send();
    }
*/
    public function testList()
    {
        $client = static::createClient();

        /** @var FreeboxFax $fax */
        $fax = $client->getContainer()->get("freebox.fax");

        $list = $fax->getFaxList();
    }
}
