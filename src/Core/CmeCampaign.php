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
use CmeKernel\Helpers\CampaignHelper;
use CmeKernel\Helpers\ListHelper;

class CmeCampaign
{
  private $_tableName = "campaigns";

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
   * @return CampaignData | bool
   */
  public function get($id)
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
        if(!$this->isValidFilters($filtersArray))
        {
          $data->filters = null;
        }
      }
    }
    return $data;
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
      $campaign->smtpProvider = CmeKernel::SmtpProvider()->get(
        $campaign->smtpProviderId
      );
      $return[]               = $campaign;
    }

    return $return;
  }

  /**
   * Returns the number of recipients for a given campaign and list combination
   *
   * @param $id
   * @param $listId
   *
   * @return mixed
   */
  public function getRecipientCount($id, $listId)
  {
    return ListHelper::count($listId, $id);
  }

  /**
   * @param $field
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
   * @return bool
   */
  public function create(CampaignData $data)
  {
    $data->created = time();
    if(is_array($data->filters))
    {
      $data->filters = json_encode($data->filters);
    }
    $result = CmeDatabase::conn()->table($this->_tableName)->insertGetId(
      $data->toArray()
    );

    return $result;
  }

  /**
   * @param CampaignData $data
   *
   * @return bool
   */
  public function update(CampaignData $data)
  {
    $campaign = $this->get($data->id);
    //if content changed, force user to test & preview campaign as a
    //safety measure
    if($campaign->htmlContent != $data->htmlContent)
    {
      $data->tested    = 0;
      $data->previewed = 0;
    }
    if($this->isValidFilters($data->filters))
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

  /**
   * Duplicate or copies a campaign to ease campaign creation.
   *
   * @param $id
   */
  public function copy($id)
  {
    $campaign            = $this->get($id);
    $campaign->id        = null;
    $campaign->name      = $campaign->name . ' (COPY)';
    $campaign->sendTime  = null;
    $campaign->tested    = 0;
    $campaign->previewed = 0;
    $campaign->status    = 'Pending';

    $this->create($campaign);
  }

  /**
   * @param $id
   *
   * @return bool
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
   * @param int $id Campaign ID
   *
   * @return bool
   */
  public function queue($id)
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

  /**
   * @param int $id Campaign ID
   *
   * @return bool
   */
  public function pause($id)
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

  /**
   * @param int $id Campaign ID
   *
   * @return bool
   */
  public function resume($id)
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

  /**
   * @param int $id - Campaign ID
   *
   * @return bool
   */
  public function abort($id)
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

  public function isValidFilters(array $filters)
  {
    return isset($filters['filter_field'])
    && isset($filters['filter_value'])
    && isset($filters['filter_operator']);
  }
}
