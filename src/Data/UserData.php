<?php
/**
 * Created by PhpStorm.
 * User: Okechukwu
 * Date: 5/18/2015
 * Time: 8:38 PM
 */

namespace CmeKernel\Data;

class UserData extends Data
{
  public $id;
  public $email;
  public $password;
  public $active;
  public $deletedAt;
  public $rememberToken;
  public $createdAt;
  public $updatedAt;
}
