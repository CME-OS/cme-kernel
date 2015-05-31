<?php
/**
 * @author  oke.ugwu
 */

namespace CmeKernel\Core;

use CmeData\UserData;
use CmeKernel\Exceptions\InvalidDataException;

class CmeUser
{
  private $_tableName = "users";

  /**
   * @param int $id
   *
   * @return bool
   * @throws \Exception
   */
  public function exists($id)
  {
    if((int)$id > 0)
    {
      $result = CmeDatabase::conn()->select(
        "SELECT id FROM " . $this->_tableName . " WHERE id = " . $id
      );
      return ($result) ? true : false;
    }
    else
    {
      throw new \Exception("Invalid User ID");
    }
  }

  /**
   * @param $id
   *
   * @return bool|UserData
   * @throws \Exception
   */
  public function get($id)
  {
    if((int)$id > 0)
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
    else
    {
      throw new \Exception("Invalid User ID");
    }
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
   * @return int $userId
   * @throws InvalidDataException
   * @throws \Exception
   */
  public function create(UserData $data)
  {
    $data->id        = null;
    $data->createdAt = date('Y-m-d H:i:s');
    $data->updatedAt = date('Y-m-d H:i:s');
    $data->active    = 1;
    if($data->validate())
    {
      $id = CmeDatabase::conn()
        ->table($this->_tableName)
        ->insertGetId($data->toArray());

      return $id;
    }
    else
    {
      throw new InvalidDataException();
    }
  }

  /**
   * @param UserData $data
   *
   * @return bool
   * @throws InvalidDataException
   * @throws \Exception
   */
  public function update(UserData $data)
  {
    if($data->validate())
    {
      //TODO: write logic for updating users
      //need to think of which fields should be updatable
      CmeDatabase::conn()->table($this->_tableName)
        ->where('id', '=', $data->id)
        ->update($data->toArray());

      return true;
    }
    else
    {
      throw new InvalidDataException();
    }
  }

  /**
   * @param int $id
   *
   * @return bool
   * @throws \Exception
   */
  public function delete($id)
  {
    if((int)$id > 0)
    {
      CmeDatabase::conn()->table($this->_tableName)
        ->delete($id);

      return true;
    }
    else
    {
      throw new \Exception("Invalid User ID");
    }
  }
}
