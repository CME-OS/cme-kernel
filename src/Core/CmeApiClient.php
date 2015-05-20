<?php
/**
 * @author  oke.ugwu
 */

namespace CmeKernel\Core;

class CmeApiClient
{
  private $_tableName = "api_clients";

  public function validate($key, $secret)
  {
    $result = CmeDatabase::conn()
      ->table($this->_tableName)
      ->where(['client_key' => $key, 'client_secret' => $secret])
      ->get();
    return ($result) ? true : false;
  }
}
