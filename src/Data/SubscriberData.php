<?php
/**
 * Created by PhpStorm.
 * User: Okechukwu
 * Date: 5/18/2015
 * Time: 8:21 PM
 */

namespace CmeKernel\Data;

class SubscriberData extends Data
{
  public $id;
  public $email;
  public $bounced = 0;
  public $unsubscribed = 0;
  public $testSubscriber = 0;
  public $dateCreated;
}
