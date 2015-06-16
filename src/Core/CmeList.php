<?php
/**
 * @author  oke.ugwu
 */

namespace CmeKernel\Core;

use CmeData\CampaignData;
use CmeData\ListData;
use CmeData\ListImportQueueData;
use CmeData\SubscriberData;
use CmeData\UnsubscribeData;
use CmeKernel\Exceptions\InvalidDataException;
use CmeKernel\Helpers\ListHelper;
use Symfony\Component\Validator\Constraints\Collection;
use Symfony\Component\Validator\Constraints\Email;
use Symfony\Component\Validator\Constraints\NotBlank;

class CmeList
{
  private $_tableName = "lists";

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
      throw new \Exception("Invalid List ID");
    }
  }

  /**
   * @param int $id
   *
   * @return bool|ListData
   * @throws \Exception
   */
  public function get($id)
  {
    if((int)$id > 0)
    {
      $list = CmeDatabase::conn()
        ->table($this->_tableName)
        ->where(['id' => $id])
        ->get();

      $data = false;
      if($list)
      {
        $data      = ListData::hydrate(head($list));
        $tableName = ListHelper::getTable($data->id);
        $size      = 0;
        //check if list table exists/
        if(CmeDatabase::schema()->hasTable($tableName))
        {
          $size = CmeDatabase::conn()->table($tableName)->count();
        }
        $data->setSize($size);
      }
      return $data;
    }
    else
    {
      throw new \Exception("Invalid List ID");
    }
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
      $list      = ListData::hydrate($row);
      $size      = 0;
      $tableName = ListHelper::getTable($row['id']);
      if(CmeDatabase::schema()->hasTable($tableName))
      {
        $size = CmeDatabase::conn()
          ->table($tableName)
          ->count();
      }
      $list->setSize($size);
      $return[] = $list;
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

    return ListData::hydrate($row);
  }

  /**
   * @param ListData $data
   *
   * @return bool|int $id
   * @throws \Exception
   * @throws InvalidDataException
   */
  public function create(ListData $data)
  {
    if((int)$data->refreshInterval == 0)
    {
      $data->refreshInterval = null;
    }

    if($data->validate())
    {
      $id = CmeDatabase::conn()
        ->table($this->_tableName)
        ->insertGetId(
          $data->toArray()
        );

      //create default schema
      ListHelper::createListTable($id, ['email']);

      return $id;
    }
    else
    {
      throw new InvalidDataException();
    }
  }

  /**
   * @param ListData $data
   *
   * @return bool
   * @throws \Exception
   * @throws InvalidDataException
   */
  public function update(ListData $data)
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
      $data            = new ListData();
      $data->deletedAt = time();
      CmeDatabase::conn()->table($this->_tableName)
        ->where('id', '=', $id)
        ->update($data->toArray());

      return true;
    }
    else
    {
      throw new \Exception("Invalid List ID");
    }
  }

  /**
   * @param int $listId
   * @param int $offset
   * @param int $limit
   *
   * @return SubscriberData[]
   * @throws \Exception
   */
  public function getSubscribers($listId, $offset = 0, $limit = 1000)
  {
    if((int)$listId > 0)
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
            $subscribers[] = SubscriberData::hydrate($row, false);
          }
        }
      }

      return $subscribers;
    }
    else
    {
      throw new \Exception("Invalid List ID");
    }
  }

  /**
   * @param int $subscriberId
   * @param int $listId
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
        $data = SubscriberData::hydrate(head($result), false);
      }
    }

    return $data;
  }

  /**
   * @param SubscriberData $data
   * @param int            $listId
   *
   * @return bool
   * @throws \Exception
   * @throws InvalidDataException
   */
  public function addSubscriber(SubscriberData $data, $listId)
  {
    unset($data->id);
    $data->dateCreated = date('Y-m-d H:i:s');
    if($data->validate())
    {
      $added = false;
      if($listId)
      {
        $table     = ListHelper::getTable($listId);
        $dataArray = $data->toArray();

        $columns = array_keys($dataArray);
        //diff it, so we don't end up with duplicate column names
        $columns = array_diff($columns, ListHelper::inBuiltFields());
        ListHelper::createListTable($listId, $columns);

        $added = CmeDatabase::conn()->table($table)->insert($dataArray);
      }

      return $added;
    }
    else
    {
      throw new InvalidDataException();
    }
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

  /**
   * @param string $email
   *
   * @return bool
   * @throws \Exception
   */
  public function isUnsubscribed($email)
  {
    $validEmail = ListData::getValidator()->validate(
      ['email' => $email],
      $constraints = new Collection(
        ['email' => [new Email(), new NotBlank()]]
      )
    );

    if($validEmail)
    {
      $result = CmeDatabase::conn()->table('unsubscribes')
        ->where('email', '=', $email)->get(['email']);
      return ($result) ? true : false;
    }
    else
    {
      throw new \Exception("Invalid email address: " . $email);
    }
  }

  /**
   * @param UnsubscribeData $data
   *
   * @return bool
   * @throws \Exception
   * @throws InvalidDataException
   */
  public function unsubscribe(UnsubscribeData $data)
  {
    if($data->validate())
    {
      return CmeDatabase::conn()->table('unsubscribes')->insert(
        $data->toArray()
      );
    }
    else
    {
      throw new InvalidDataException();
    }
  }

  /**
   * @param int $listId
   *
   * @return array
   * @throws \Exception
   */
  public function getColumns($listId)
  {
    if((int)$listId > 0)
    {
      return CmeDatabase::schema()->getColumnListing(
        ListHelper::getTable($listId)
      );
    }
    else
    {
      throw new \Exception("Invalid List ID");
    }
  }

  public function import(ListImportQueueData $data)
  {
    unset($data->id);
    if($data->validate())
    {
      CmeDatabase::conn()->table('import_queue')->insert(
        $data->toArray()
      );
      return true;
    }
    else
    {
      throw new InvalidDataException();
    }
  }


  /**
   * @param int $listId
   *
   * @return CampaignData[]
   * @throws \Exception
   */
  public function campaigns($listId)
  {
    if((int)$listId > 0)
    {
      $campaigns = CmeDatabase::conn()->table('campaigns')
        ->where(['list_id' => $listId])->get();

      $return = [];
      foreach($campaigns as $campaign)
      {
        $campaign               = CampaignData::hydrate($campaign);
        $campaign->list         = CmeKernel::EmailList()->get(
          $campaign->listId
        );
        $campaign->brand        = CmeKernel::Brand()->get($campaign->brandId);
        $campaign->smtpProvider = CmeKernel::SmtpProvider()->get(
          $campaign->smtpProviderId
        );
        $return[]               = $campaign;
      }

      return $return;
    }
    else
    {
      throw new \Exception("Invalid List ID");
    }
  }
}
