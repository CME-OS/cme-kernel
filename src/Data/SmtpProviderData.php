<?php
/**
 * Created by PhpStorm.
 * User: Okechukwu
 * Date: 5/18/2015
 * Time: 8:34 PM
 */

namespace CmeKernel\Data;

class SmtpProviderData extends Data
{
  public $id;
  public $name;
  public $host;
  public $username;
  public $password;
  public $port;
  public $default;
  public $deletedAt;
}
