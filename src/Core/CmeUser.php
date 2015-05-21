<?php
/**
 * @author  oke.ugwu
 */

namespace CmeKernel\Core;

use CmeData\UserData;

class CmeUser
{
  private $_tableName = "users";

  public function exists($id)
  {
    $result = CmeDatabase::conn()->select(
      "SELECT id FROM " . $this->_tableName . " WHERE id = " . $id
    );
    return ($result)? true : false;
  }

  /**
   * @param $id
   *
   * @return bool| UserData
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
      $data = UserData::hydrate(head($user));
    }
    return $data;
  }

  /**
   * @param bool $includeDeleted
   *
   * @return UserData[];
   */
  public function all($includeDeleted = false)
  {
    $return = [];
    if($includeDeleted)
    {
      $result = CmeDatabase::conn()->table($this->_tableName)->get();
    }
    else
    {
      $result = CmeDatabase::conn()->table($this->_tableName)->whereNull(
        'deleted_at'
      )->get();
    }

    foreach($result as $row)
    {
      $return[] = UserData::hydrate($row);
    }

    return $return;
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
      ->insertGetId($data->toArray());

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
      ->update($data->toArray());

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
