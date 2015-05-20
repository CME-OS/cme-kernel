<?php
/**
 * @author  oke.ugwu
 */

namespace CmeKernel\Core;

use CmeKernel\Data\CampaignData;
use CmeKernel\Data\ListImportQueueData;
use CmeKernel\Data\ListData;
use CmeKernel\Data\SubscriberData;
use CmeKernel\Helpers\ListHelper;

class CmeList
{
  private $_tableName = "lists";

  public function exists($id)
  {
    $result = CmeDatabase::conn()->select(
      "SELECT id FROM " . $this->_tableName . " WHERE id = " . $id
    );
    return ($result) ? true : false;
  }

  /**
   * @param $id
   *
   * @return bool| ListData
   */
  public function get($id)
  {
    $list = CmeDatabase::conn()
      ->table($this->_tableName)
      ->where(['id' => $id])
      ->get();

    $data = false;
    if($list)
    {
      $data = CmeDatabase::hydrate(new ListData(), head($list));
    }

    $tableName = ListHelper::getTable($data->id);
    $size      = 0;
    //check if list table exists/
    if(CmeDatabase::schema()->hasTable($tableName))
    {
      $size = CmeDatabase::conn()->table($tableName)->count();
    }
    $data->setSize($size);

    return $data;
  }

  /**
   * @param bool $includeDeleted
   *
   * @return ListData[];
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
      $return[] = CmeDatabase::hydrate(new ListData(), $row);
    }

    return $return;
  }

  /**
   * @return ListData;
   * @throws \Exception
   */
  public function any()
  {
    $row = head(
      CmeDatabase::conn()->select(
        "SELECT * FROM " . $this->_tableName . " LIMIT 1"
      )
    );

    return CmeDatabase::hydrate(new ListData(), $row);
  }

  /**
   * @param ListData $data
   *
   * @return bool|int $id
   */
  public function create(ListData $data)
  {
    if((int)$data->refreshInterval == 0)
    {
      $data->refreshInterval = null;
    }

    $id = CmeDatabase::conn()
      ->table($this->_tableName)
      ->insertGetId(
        CmeDatabase::dataToArray($data)
      );

    return $id;
  }

  /**
   * @param ListData $data
   *
   * @return bool
   */
  public function update(ListData $data)
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
    $data            = new ListData();
    $data->deletedAt = time();
    CmeDatabase::conn()->table($this->_tableName)
      ->where('id', '=', $id)
      ->update(CmeDatabase::dataToArray($data));

    return true;
  }

  /**
   * @param int $listId
   * @param int $offset
   * @param int $limit
   *
   * @return SubscriberData[]
   */
  public function getSubscribers($listId, $offset = 0, $limit = 1000)
  {
    $tableName = ListHelper::getTable($listId);
    //check if list table exists/
    $subscribers = [];
    if(CmeDatabase::schema()->hasTable($tableName))
    {
      //if it does, fetch all subscribers and display
      if(CmeDatabase::conn()->table($tableName)->count())
      {
        $result = CmeDatabase::conn()
          ->table($tableName)
          ->skip($offset)
          ->take($limit)
          ->get();

        foreach($result as $row)
        {
          $subscribers[] = CmeDatabase::hydrate(new SubscriberData(), $row);
        }
      }
    }

    return $subscribers;
  }

  /**
   * @param $subscriberId
   * @param $listId
   *
   * @return bool|SubscriberData
   */
  public function getSubscriber($subscriberId, $listId)
  {
    $data = false;
    if($listId && $subscriberId)
    {
      $table  = ListHelper::getTable($listId);
      $result = CmeDatabase::conn()
        ->table($table)
        ->where(['id' => $subscriberId])
        ->get();

      if($result)
      {
        /**
         * @var SubscriberData $data
         */
        $data = CmeDatabase::hydrate(new SubscriberData(), head($result));
      }
    }

    return $data;
  }

  /**
   * @param SubscriberData $data
   * @param int            $listId
   *
   * @return bool
   */
  public function addSubscriber(SubscriberData $data, $listId)
  {
    unset($data->id);
    $data->dateCreated = date('Y-m-d H:i:s');
    $added             = false;
    if($listId)
    {
      $table = ListHelper::getTable($listId);
      if(!CmeDatabase::schema()->hasTable($table))
      {
        $columns = array_keys(CmeDatabase::dataToArray($data));
        $columns = array_diff($columns, ListHelper::inBuiltFields());
        ListHelper::createListTable($listId, $columns);
      }

      CmeDatabase::conn()->table($table)->insert(
        CmeDatabase::dataToArray($data)
      );

      $added = false;
    }

    return $added;
  }

  /**
   * @param int $subscriberId
   * @param int $listId
   *
   * @return mixed
   */
  public function deleteSubscriber($subscriberId, $listId)
  {
    $deleted = false;
    if($listId && $subscriberId)
    {
      $table = ListHelper::getTable($listId);
      CmeDatabase::conn()
        ->table($table)
        ->where(['id' => $subscriberId])
        ->delete();
      $deleted = true;
    }

    return $deleted;
  }

  public function getColumns($listId)
  {
    return CmeDatabase::schema()->getColumnListing(
      ListHelper::getTable($listId)
    );
  }

  public function import(ListImportQueueData $data)
  {
    unset($data->id);
    CmeDatabase::conn()->table('import_queue')->insert(
      CmeDatabase::dataToArray($data)
    );

    return true;
  }


  /**
   * @param int $id - List ID
   *
   * @return CampaignData[]
   */
  public function campaigns($id)
  {
    $campaigns = CmeDatabase::conn()->table('campaigns')
      ->where(['list_id' => $id])->get();

    $return = [];
    foreach($campaigns as $campaign)
    {
      $return[] = CmeDatabase::hydrate(new CampaignData(), $campaign);
    }

    return $return;
  }
}
