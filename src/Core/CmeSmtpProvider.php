<?php
/**
 * @author  oke.ugwu
 */

namespace CmeKernel\Core;

use CmeData\CampaignData;
use CmeData\SmtpProviderData;
use CmeKernel\Exceptions\InvalidDataException;
use Illuminate\Encryption\Encrypter;

class CmeSmtpProvider
{
  private $_tableName = "smtp_providers";

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
      throw new \Exception("Inavalid SMTP Provider ID");
    }
  }

  /**
   * @param $id
   *
   * @return bool|SmtpProviderData
   * @throws \Exception
   */
  public function get($id)
  {
    if((int)$id > 0)
    {
      $provider = CmeDatabase::conn()
        ->table($this->_tableName)
        ->where(['id' => $id])
        ->get();

      $data = false;
      if($provider)
      {
        $data = SmtpProviderData::hydrate(head($provider));
      }
      return $data;
    }
    else
    {
      throw new \Exception("Invalid SMTP Provider ID");
    }
  }

  /**
   * @param bool $includeDeleted
   *
   * @return SmtpProviderData[];
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
      $return[] = SmtpProviderData::hydrate($row);
    }

    return $return;
  }

  /**
   * @param SmtpProviderData $data
   * @param string           $encryptionKey
   *
   * @return int
   * @throws InvalidDataException
   * @throws \Exception
   */
  public function create(SmtpProviderData $data, $encryptionKey)
  {
    if($encryptionKey)
    {
      if($data->validate())
      {
        $encrypter      = new Encrypter($encryptionKey);
        $data->username = $encrypter->encrypt($data->username);
        $data->password = $encrypter->encrypt($data->password);
        $id             = CmeDatabase::conn()
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
    else
    {
      throw new \Exception(
        "Encryption key is required to store SMTP Provider information"
      );
    }
  }

  /**
   * @param SmtpProviderData $data
   * @param string           $encryptionKey
   *
   * @return bool
   * @throws InvalidDataException
   * @throws \Exception
   */
  public function update(SmtpProviderData $data, $encryptionKey)
  {
    if($encryptionKey)
    {
      if($data->password == "")
      {
        //we set password to null here so it does not get included
        // in the updated column
        $data->password = null;
      }

      if($data->validate())
      {
        $encrypter      = new Encrypter($encryptionKey);
        $data->password = $encrypter->encrypt($data->password);
        $data->username = $encrypter->encrypt($data->username);

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
    else
    {
      throw new \Exception(
        "Encryption key is required to store SMTP Provider information"
      );
    }
  }

  /**
   * @param int $id
   *
   * @return bool
   */
  public function delete($id)
  {
    $data            = new SmtpProviderData();
    $data->deletedAt = time();
    CmeDatabase::conn()->table($this->_tableName)
      ->where('id', '=', $id)
      ->update($data->toArray());

    return true;
  }

  public function setAsDefault($id)
  {
    //reset all
    CmeDatabase::conn()->table('smtp_providers')
      ->update(['default' => 0]);

    //set smtp provide with matching $id as default
    CmeDatabase::conn()->table('smtp_providers')
      ->where('id', '=', $id)
      ->update(['default' => 1]);

    return true;
  }


  /**
   * @param $smtpProviderId
   *
   * @return CampaignData[]
   * @throws \Exception
   */
  public function campaigns($smtpProviderId)
  {
    if((int)$smtpProviderId > 0)
    {
      $campaigns = CmeDatabase::conn()->table('campaigns')
        ->where(['smtp_provider_id' => $smtpProviderId])->get();

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
      throw new \Exception("Invalid SMTP Provider ID");
    }
  }
}
