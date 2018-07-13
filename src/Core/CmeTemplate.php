<?php
/**
 * @author  oke.ugwu
 */

namespace CmeKernel\Core;

use CmeData\TemplateData;
use CmeKernel\Exceptions\InvalidDataException;

class CmeTemplate
{
  private $_tableName = "templates";

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
      throw new \Exception("Invalid Template ID");
    }
  }

  /**
   * @param int $id
   *
   * @return bool|TemplateData
   * @throws \Exception
   */
  public function get($id)
  {
    if((int)$id > 0)
    {
      $template = CmeDatabase::conn()
        ->table($this->_tableName)
        ->where(['id' => $id])
        ->get();

      $data = false;
      if($template)
      {
        $data = TemplateData::hydrate($template->first());
      }
      return $data;
    }
    else
    {
      throw new \Exception("Invalid Template ID");
    }
  }

  /**
   * @param bool $includeDeleted
   *
   * @return TemplateData[];
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
      $return[] = TemplateData::hydrate($row);
    }

    return $return;
  }

  /**
   * @param string $field
   *
   * @return array
   * @throws \Exception
   */
  public function getKeyedListFor($field)
  {
    return CmeDatabase::conn()->table($this->_tableName)
      ->whereNull('deleted_at')
      ->orderBy('id', 'asc')->lists($field, 'id');
  }

  /**
   * @param TemplateData $data
   *
   * @return int $templateId
   * @throws \Exception
   * @throws InvalidDataException
   */
  public function create(TemplateData $data)
  {
    $data->id      = null;
    $data->created = time();
    if($data->validate())
    {
      $id = CmeDatabase::conn()
        ->table($this->_tableName)
        ->insertGetId(
          $data->toArray()
        );

      return $id;
    }
    else
    {
      throw new InvalidDataException();
    }
  }

  /**
   * @param TemplateData $data
   *
   * @return bool
   * @throws InvalidDataException
   * @throws \Exception
   */
  public function update(TemplateData $data)
  {
    if($data->validate())
    {
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
      $data            = new TemplateData();
      $data->deletedAt = time();
      CmeDatabase::conn()->table($this->_tableName)
        ->where('id', '=', $id)
        ->update($data->toArray());

      return true;
    }
    else
    {
      throw new \Exception("Invalid Template ID");
    }
  }
}
