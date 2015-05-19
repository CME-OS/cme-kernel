<?php
/**
 * @author  oke.ugwu
 */

namespace CmeKernel\Core;

use CmeKernel\Data\UserData;

class CmeUser
{
  private $_tableName = "users";

  /**
   * @param $id
   *
   * @return bool| BrandData
   */
  public function get($id)
  {
    $user = CmeDatabase::conn()
      ->table($this->_tableName)
      ->where(['id' => $id])
      ->get();

    $data = false;
    if($user)
    {
      $data = CmeDatabase::hydrate(new UserData(), head($user));
    }
    return $data;
  }

  /**
   * @param UserData $data
   *
   * @return bool|int $id
   */
  public function create(UserData $data)
  {
    $data->id        = null;
    $data->createdAt = time();
    $data->updatedAt = time();
    $id              = CmeDatabase::conn()
      ->table($this->_tableName)
      ->insertGetId(
        CmeDatabase::dataToArray($data)
      );

    return $id;
  }

  /**
   * @param UserData $data
   *
   * @return bool
   */
  public function update(UserData $data)
  {
    CmeDatabase::conn()->table($this->_tableName)
      ->where('id', '=', $data->id)
      ->update(CmeDatabase::dataToArray($data));

    return true;
  }

  /**
   * @param int $id
   *
   * @return bool
   */
  public function delete($id)
  {
    CmeDatabase::conn()->table($this->_tableName)
      ->delete($id);

    return true;
  }
}
