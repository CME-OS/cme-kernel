<?php
/**
 * Created by PhpStorm.
 * User: Okechukwu
 * Date: 5/18/2015
 * Time: 8:56 PM
 */

namespace CmeKernel\Data;

class BrandData extends Data
{
  public $id;
  public $brandName;
  public $brandSenderEmail;
  public $brandSenderName;
  public $brandWebsiteUrl;
  public $brandDomainName;
  public $brandUnsubscribeUrl;
  public $brandLogo;
  public $brandCreated;
  public $brandDeletedAt;

  public function getFields()
  {
    return [
      "id",
      "brand_name",
      "brand_sender_email",
      "brand_sender_name",
      "brand_website_url",
      "brand_domain_name",
      "brand_unsubscribe_url",
      "brand_logo",
      "brand_created",
      "brand_deleted_at"
    ];
  }
}
