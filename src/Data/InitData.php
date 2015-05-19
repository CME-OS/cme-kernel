<?php
/**
 * @author  oke.ugwu
 */

namespace CmeKernel\Data;

class InitData
{
  public $dbDriver = 'mysql';
  public $dbHost;
  public $dbUsername;
  public $dbPassword;
  public $dbName;
  public $dbCharset = 'utf8';
  public $dbCollation = 'utf8_unicode_ci';
  public $dbPrefix = '';
}
