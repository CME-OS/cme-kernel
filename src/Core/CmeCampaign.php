<?php

/**
 * Created by PhpStorm.
 * User: Okechukwu
 * Date: 5/18/2015
 * Time: 7:58 PM
 */
namespace CmeKernel\Core;

use CmeData\CampaignData;
use CmeKernel\Enums\CampaignStatus;
use CmeKernel\Exceptions\InvalidDataException;
use CmeKernel\Helpers\CampaignHelper;
use CmeKernel\Helpers\FilterHelper;
use CmeKernel\Helpers\ListHelper;

class CmeCampaign
{
  private $_tableName = "campaigns";

  /**
   * @param int $id
   *
   * @return bool
   * @throws \Exception
   */
  public function exists($id)
  {
    if((int)$id)
    {
      $result = CmeDatabase::conn()->select(
        "SELECT id FROM " . $this->_tableName . " WHERE id = " . $id
      );
      return ($result) ? true : false;
    }
    else
    {
      throw new \Exception("Invalid Campaign ID");
    }
  }

  /**
   * @param int $id
   *
   * @return bool|CampaignData
   * @throws \Exception
   */
  public function get($id)
  {
    if((int)$id > 0)
    {
      $campaign = CmeDatabase::conn()
        ->table($this->_tableName)
        ->where(['id' => $id])
        ->get();

      $data = false;
      if($campaign)
      {
        $data    = CampaignData::hydrate(head($campaign));
        $filters = json_decode($data->filters);
        if($filters)
        {
          $filtersArray  = (array)$filters;
          $data->filters = $filtersArray;
          if(!FilterHelper::isValidFilters($filtersArray))
          {
            $data->filters = null;
          }
        }
        else
        {
          $data->filters = null;
        }
      }
      return $data;
    }
    else
    {
      throw new \Exception("Invalid Campaign ID");
    }
  }

  /**
   * @param bool $includeDeleted
   *
   * @return CampaignData[];
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
      $campaign               = CampaignData::hydrate($row);
      $campaign->list         = CmeKernel::EmailList()->get($campaign->listId);
      $campaign->brand        = CmeKernel::Brand()->get($campaign->brandId);
      if($campaign->smtpProviderId)
      {
        $campaign->smtpProvider = CmeKernel::SmtpProvider()->get(
          $campaign->smtpProviderId
        );
      }
      $return[]               = $campaign;
    }

    return $return;
  }

  /**
   * Returns the number of recipients for a given campaign and list combination
   *
   * @param int $id - Campaign ID
   * @param int $listId
   *
   * @return mixed
   * @throws \Exception
   */
  public function getRecipientCount($id, $listId)
  {
    if((int)$id > 0 && (int)$listId > 0)
    {
      return ListHelper::count($listId, $id);
    }
    else
    {
      throw new \Exception("Invalid Campaign or List ID");
    }
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
   * @param CampaignData $data
   *
   * @return int $campaignId
   * @throws \Exception
   * @throws InvalidDataException
   */
  public function create(CampaignData $data)
  {
    $data->created = time();
    if($data->validate())
    {
      if(!FilterHelper::isValidFilters($data->filters))
      {
        $data->filters = json_encode($data->filters);
      }
      else
      {
        $data->filters = null;
      }
      $result = CmeDatabase::conn()->table($this->_tableName)->insertGetId(
        $data->toArray()
      );

      return $result;
    }
    else
    {
      throw new InvalidDataException();
    }
  }

  /**
   * @param CampaignData $data
   *
   * @return bool
   * @throws \Exception
   * @throws InvalidDataException
   */
  public function update(CampaignData $data)
  {
    if($data->validate())
    {
      $campaign = $this->get($data->id);
      //if content changed, force user to test & preview campaign as a
      //safety measure
      if($campaign->htmlContent != $data->htmlContent)
      {
        $data->tested    = 0;
        $data->previewed = 0;
      }
      if(FilterHelper::isValidFilters($data->filters))
      {
        $data->filters = json_encode($data->filters);
      }
      else
      {
        $data->filters = null;
      }

      $data->sendTime = is_int($data->sendTime)
        ? $data->sendTime : strtotime($data->sendTime);

      CmeDatabase::conn()->table($this->_tableName)
        ->where(['id' => $data->id])
        ->update(
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
   * Duplicate or copies a campaign to ease campaign creation.
   *
   * @param $id
   *
   * @return int
   * @throws \Exception
   * @throws InvalidDataException
   */
  public function copy($id)
  {
    if((int)$id)
    {
      $campaign            = $this->get($id);
      $campaign->id        = null;
      $campaign->name      = $campaign->name . ' (COPY)';
      $campaign->sendTime  = null;
      $campaign->tested    = 0;
      $campaign->previewed = 0;
      $campaign->status    = 'Pending';

      return $this->create($campaign);
    }
    else
    {
      throw new \Exception("Invalid Campaign ID");
    }
  }

  /**
   * @param $id
   *
   * @return bool
   * @throws \Exception
   * @throws \InvalidDataException
   */
  public function delete($id)
  {
    if($this->get($id))
    {
      $data            = new CampaignData();
      $data->id        = $id;
      $data->deletedAt = time();
      $this->update($data);
    }
    return true;
  }

  /**
   * @param int $id - Campaign ID
   *
   * @return bool
   * @throws \Exception
   */
  public function queue($id)
  {
    if((int)$id > 0)
    {
      if(CampaignHelper::buildQueueRanges($id))
      {
        //update status of campaign
        CmeDatabase::conn()->table($this->_tableName)
          ->where(['id' => $id])
          ->update(['status' => CampaignStatus::QUEUING]);
      }
      return true;
    }
    else
    {
      throw new \Exception("Invalid Campaign ID");
    }
  }

  /**
   * @param int $id - Campaign ID
   *
   * @return bool
   * @throws \Exception
   */
  public function pause($id)
  {
    if((int)$id > 0)
    {
      CmeDatabase::conn()
        ->table('message_queue')
        ->where(['campaign_id' => $id, 'status' => CampaignStatus::PENDING])
        ->update(['status' => CampaignStatus::PAUSED]);

      //update status of campaign
      CmeDatabase::conn()
        ->table($this->_tableName)
        ->where(['id' => $id])
        ->update(['status' => CampaignStatus::PAUSED]);

      return true;
    }
    else
    {
      throw new \Exception("Invalid Campaign ID");
    }
  }

  /**
   * @param int $id - Campaign ID
   *
   * @return bool
   * @throws \Exception
   */
  public function resume($id)
  {
    if((int)$id > 0)
    {
      CmeDatabase::conn()
        ->table('message_queue')
        ->where(['campaign_id' => $id, 'status' => CampaignStatus::PAUSED])
        ->update(['status' => CampaignStatus::PENDING]);

      //update status of campaign
      CmeDatabase::conn()
        ->table($this->_tableName)
        ->where(['id' => $id])->update(['status' => CampaignStatus::QUEUED]);

      return true;
    }
    else
    {
      throw new \Exception("Invalid Campaign ID");
    }
  }

  /**
   * @param int $id - Campaign ID
   *
   * @return bool
   * @throws \Exception
   */
  public function abort($id)
  {
    if((int)$id > 0)
    {
      //delete pending messages from the queue
      CmeDatabase::conn()
        ->table('message_queue')
        ->where(['campaign_id' => $id, 'status' => CampaignStatus::PENDING])
        ->delete();

      //update status of campaign
      CmeDatabase::conn()
        ->table($this->_tableName)
        ->where(['id' => $id])
        ->update(['status' => CampaignStatus::ABORTED]);

      return true;
    }
    else
    {
      throw new \Exception("Invalid Campaign ID");
    }
  }
}
