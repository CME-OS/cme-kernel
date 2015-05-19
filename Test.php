<?php
/**
 * Created by PhpStorm.
 * User: Okechukwu
 * Date: 5/18/2015
 * Time: 9:53 PM
 */
include_once 'vendor/autoload.php';
use CmeKernel\Core\CmeKernel;

$initData           = new \CmeKernel\Data\InitData();
$initData->dbHost     = 'localhost';
$initData->dbName = 'cme';
$initData->dbUsername = 'root';
$initData->dbPassword = '';
CmeKernel::init($initData);

function getCampaign()
{
  $x = CmeKernel::Campaign()->get(1);
  var_dump($x);
}

function pauseCampaign()
{
  $c = CmeKernel::Campaign();
  $c->pause(3);
}

function copyCampaign()
{
  $c = CmeKernel::Campaign();
  $c->copy(2);
}

function updateCampaign()
{
  $d              = new \CmeKernel\Data\CampaignData();
  $d->brandId     = 4;
  $d->listId      = 1;
  $d->name        = "Test Kernel222";
  $d->subject     = "Good Test";
  $d->htmlContent = "<p>hello</p>";
  $d->from        = "admin@sold.io";

  $c = CmeKernel::Campaign();
  $c->update($d);
}

function createCampaign()
{
  $d              = new \CmeKernel\Data\CampaignData();
  $d->brandId     = 4;
  $d->listId      = 1;
  $d->name        = "Test Kernel222";
  $d->subject     = "Good Test";
  $d->htmlContent = "<p>hello</p>";
  $d->from        = "admin@sold.io";

  CmeKernel::Campaign()->create($d);
}

function getSubscribers()
{
  $v = CmeKernel::EmailList()->getSubscribers(12, 0, 10);
  var_dump($v);
}

function createList()
{
  $l       = new \CmeKernel\Data\ListData();
  $l->name = "Kernel List";

  $x = CmeKernel::EmailList()->create($l);
  echo "List ID: " . $x . PHP_EOL;
}

function addSubscriberToList()
{
  $s            = new \CmeKernel\Data\SubscriberData();
  $s->email     = "tomtom@sold.io";
  $s->firstName = "Tom";
  $s->lastName  = "Tom";
  $s->age       = 30;
  $s->id        = 300;

  CmeKernel::EmailList()->addSubscriber($s, 13);
}

function getSubscriber()
{
  $x = CmeKernel::EmailList()->getSubscriber(1, 13);
  var_dump($x);
}

function getBrand()
{
  $x = CmeKernel::Brand()->get(1);
  var_dump($x);
}

getCampaign();
