<?php


/**
 * Antares API Class
 */
class Antares {
  private $PLATFORM_URL = 'https://platform.antares.id:8443';
  private $ACCESS_KEY = '65f708123a858355:7084ef0d7c21f8cd';
  private $cseId = "antares-cse";
  private $cseName = "antares-id";

  private function __construct() {
  }
  
  private static $instance = null;
  
  /**
   * Initiate Antares API.
   * @param array KeyValuePair of 
   *  - PLATFORM_URL: Platform URL
   *  - ACCESS_KEY: Access Key
   *  - cseId: cse id
   *  - cseName: cse name
   * @return Antares
   */
  public static function init($options = []) {
    if (self::$instance == null) {
      self::$instance = new Antares();
      foreach ($options as $key => $value) {
        self::$instance->$key = $value;
      }
    }
  }

  /**
   * Get instance of Antares API.
   * @return Antares
   */
  public static function getInstance() {
    return self::$instance;
  }

  /**
   * get OM2M resource by url.
   * @return string
   */
  private function curl_get($url) {
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
    curl_setopt($ch, CURLOPT_HTTPHEADER, array(
      'Content-Type: application/json',
      'X-M2M-Origin: ' . $this->ACCESS_KEY,
      'Accept: application/json',
    ));
    $result = curl_exec($ch);
    $responseCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    if ($responseCode >= 200 && $responseCode < 300) {
      return $result;
    } else {
      $err = new Exception($result);
      $err->responseCode = $responseCode;
      throw $err;
    }
  }

  /**
   * create OM2M resource.
   * @param string $url url of parent resource
   * @param string $body body of resource
   * @return string
   */
  private function curl_post($url, $data, $resourceType) {
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
    $tyParam = '';
    if ($resourceType) {
      $tyParam = ';ty=' . $resourceType;
    }
    curl_setopt($ch, CURLOPT_HTTPHEADER, array(
      'Content-Type: application/json'.$tyParam,
      'X-M2M-Origin: ' . $this->ACCESS_KEY,
      'Accept: application/json',
    ));
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
    $result = curl_exec($ch);
    $responseCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    if ($responseCode >= 200 && $responseCode < 300) {
      return $result;
    } else {
      $err = new Exception($result);
      $err->responseCode = $responseCode;
      throw $err;
    }
  }

  /**
   * delete OM2M resource.
   * @param string $url url of resource
   * @return string
   */
  private function curl_delete($url) {
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
    curl_setopt($ch, CURLOPT_HTTPHEADER, array(
      'Content-Type: application/json',
      'X-M2M-Origin: ' . $this->ACCESS_KEY,
      'Accept: application/json',
    ));
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'DELETE');
    $result = curl_exec($ch);
    $responseCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    if ($responseCode >= 200 && $responseCode < 300) {
      return $result;
    } else {
      $err = new Exception($result);
      $err->responseCode = $responseCode;
      throw $err;
    }
  }

  /**
   * update OM2M resource.
   * @param string $url url of resource
   * @param string $body body of resource containing new values
   * @return string
   */
  private function curl_put($url, $data) {
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
    curl_setopt($ch, CURLOPT_HTTPHEADER, array(
      'Content-Type: application/json',
      'X-M2M-Origin: ' . $this->ACCESS_KEY,
      'Accept: application/json',
    ));
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'PUT');
    curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
    $result = curl_exec($ch);
    $responseCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    if ($responseCode >= 200 && $responseCode < 300) {
      return $result;
    } else {
      $err = new Exception($result);
      $err->responseCode = $responseCode;
      throw $err;
    }
  }

  /**
   * create Hierarchical Uri using resource names .
   * @param string $segments segments of hierarchical uri starting from AE name, eg. ['AE', 'Container', 'ContentInstance']
   * @return string
   */
  public function getHierarchicalUri(...$segments) {
    $uri = '/' . $this->cseId . '/' . $this->cseName;
    foreach ($segments as $segment) {
      $uri .= '/' . $segment;
    }
    return $uri;
  }

  /**
   * create OM2M resource.
   * @param string $resourceId parent resource id to insert to
   * @param string $data body of resource
   * @param int $resourceType resource type, eg: [2, 3, 4, 23]
   * @return string
   */
  public function create($resourceId, $data, $resourceType) {
    $url = $this->PLATFORM_URL . '/~' . $resourceId;
    $result = $this->curl_post($url, $data, $resourceType);
    $r = json_decode($result);
    return $this->resultToObject($r);
  }

  /**
   * get OM2M resource.
   * @param string $resourceId resource id to update
   * @return Om2mResource
   */
  public function get($resourceId) {
    $url = $this->PLATFORM_URL . '/~' . $resourceId;
    $result = $this->curl_get($url);
    $r = json_decode($result);
    return $this->resultToObject($r);
  }

  /**
   * convert standard object to Om2Resource or it's childs.
   * @param string $r result of curl_get or curl_post
   * @return Om2mResource
   */
  private function resultToObject($r) {
    if (isset($r->{'m2m:ae'})) {
      $res = $r->{'m2m:ae'};
      $resp = new AE($res->ri, $res->pi, $res->rn, $res->ct, isset($res->lbl) ? $res->lbl : null);
    } else if (isset($r->{'m2m:cnt'})) {
      $res = $r->{'m2m:cnt'};
      $resp = new Container($res->ri, $res->pi, $res->rn, $res->ct, isset($res->lbl) ? $res->lbl : null, $res->ol, $res->la);
    } else if (isset($r->{'m2m:cin'})) {
      $res = $r->{'m2m:cin'};
      $resp = new ContentInstance($res->ri, $res->pi, $res->rn, $res->ct, $res->cnf, $res->con);
    } else if (isset($r->{'m2m:sub'})) {
      $res = $r->{'m2m:sub'};
      $resp = new Subscription($res->ri, $res->pi, $res->rn, $res->ct, isset($res->nu) ? $res->nu : [], $res->nct);
    } else{
      $resp = $r;
    }
    return $resp;
  }

  /**
   * delete OM2M resource.
   * @param string $resourceId resource id to delete
   * @return Om2mResource
   */
  public function delete($resourceId) {
    $url = $this->PLATFORM_URL . '/~' . $resourceId;
    $result = $this->curl_delete($url);
    return $result;
  }

  /**
   * discover OM2M resources uris.
   * @param string $resourceId resource id to update
   * @param DiscoveryParam $params discovery params
   * @return Om2mResource
   */
  public function discover($resourceId, $params = null) {
    $url = $this->PLATFORM_URL . '/~' . $resourceId;
    if ($params) {
      $url .= '?' . $params->toQueryString();
    }
    $result = $this->curl_get($url);
    return json_decode($result, true);
  }
}

/**
 * OM2M resource. Basic om2m resource class.
 * @package Om2m
 */
class Om2mResource {
  public $ri;
  public $pi;
  public $rn;
  public $ct;
  public $ty;

  public function __construct($ri, $pi, $rn, $ct, $ty) {
    $this->ri = $ri;
    $this->pi = $pi;
    $this->rn = $rn;
    $this->ct = $ct;
    $this->ty = $ty;
  }

  /**
   * convert om2m resource to json string.
   * @return array
   */
  public function toJson() {
    return json_encode($this);
  }

  /**
   * get resource name.
   * @return string
   */
  public function getName() {
    return $this->rn;
  }

  /**
   * get resource id.
   * @return string
   */
  public function getResourceId() {
    return $this->ri;
  }
  
  /**
   * get parent id.
   * @return string
   */
  public function getParentId() {
    return $this->pi;
  }

  /**
   * get creation time with format YYYYMMDDTHHmmss.
   * @return int
   */
  public function getCreationTime() {
    return $this->ct;
  }

  /**
   * get resource type.
   * @return int
   */
  public function getResourceType() {
    return $this->ty;
  }

  /**
   * get list child resource of this resource.
   * @param int $resourceType child's resource type, eg: [2, 3, 4, 23]
   * @param string $limit
   * @param string $offset
   * @return string
   */
  protected function listChildUris($ty, $limit = 100, $offset = 0) {
    $params = new DiscoveryParam();
    $params->ty = $ty;
    $params->lim = $limit;
    $params->ofst = $offset;
    $result = Antares::getInstance()->discover($this->getResourceId(), $params);
    return $arr = $result['m2m:uril'];
  }

  /**
   * get parent resource of this resource.
   * @return Om2mResource
   */
  public function getParent() {
    if ($this->pi) {
      return Antares::getInstance()->get($this->pi);
    }
    return null;
  }

  /**
   * delete OM2M resource.
   */
  public function delete() {
    return Antares::getInstance()->delete($this->getResourceId());
  }
}

/**
 * OM2M AE resource.
 * @package Om2m
 */
class AE extends Om2mResource {
  public $lbl;

  public function __construct($ri, $pi, $rn, $ct, $lbl = []) {
    parent::__construct($ri, $pi, $rn, $ct, 2);
    $this->lbl = $lbl;
  }

  /**
   * get AE's Container child resources's uri.
   * @param int $limit limit of child resources
   * @param int $offset offset of child resources
   * @return array
   */
  public function listContainerUris($limit = 100, $offset = 0) {
    return $this->listChildUris(3, $limit, $offset);
  }

  /**
   * get AE's Subscriptions child resources's uri.
   * @param int $limit limit of child resources
   * @param int $offset offset of child resources
   * @return array
   */
  public function listSubscriptionUris($limit = 100, $offset = 0) {
    return $this->listChildUris(23, $limit, $offset);
  }
}

/**
 * OM2M Container resource.
 * @package Om2m
 */
class Container extends Om2mResource {
  public $lbl;
  public $ol;
  public $la;

  public function __construct($ri, $pi, $rn, $ct, $lbl, $ol, $la) {
    parent::__construct($ri, $pi, $rn, $ct, 3);
    $this->lbl = $lbl;
    $this->ol = $ol;
    $this->la = $la;
  }

  /**
   * list content instance uris.
   * @param int $limit limit of result (default 100)
   * @param int $offset
   * @return array
   */
  public function listContentInstanceUris($limit = 100, $offset = 0) {
    return $this->listChildUris(4, $limit, $offset);
  }

  /**
   * get latest content instance uri.
   * @return string
   */
  public function getLatestContentInstanceUri() {
    return $this->la;
  }

  /**
   * get latest content instance.
   * @return ContentInstance
   */
  public function getLatestContentInstace() {
    return Antares::getInstance()->get($this->la);
  }

  /**
   * get oldest content instance resourceId.
   * @return string
   */
  public function getOldestContentInstanceUri() {
    return $this->ol;
  }

  /**
   * get oldest content instance.
   * @return ContentInstance
   * @throws Exception
   */
  public function getOldestContentInstance() {
    return Antares::getInstance()->get($this->ol);
  }
  
  /**
   * get list of subscription resourceIds attached to this resource.
   * @param int $limit default: 100
   * @param int $offset
   * @return array
   */
  public function listSubscriptionUris($limit = 100, $offset = 0) {
    return $this->listChildUris(23, $limit, $offset);
  }

  /**
   * create content instance.
   * @param string $content content to create
   * @param string $contentType content format (text/plain, application/json, etc.)
   * @return ContentInstance
   */
  public function insertContentInstance($content, $contentFormat = '') {
    $cin = new stdClass();
    $cin->{'m2m:cin'} = new stdClass();
    $cin->{'m2m:cin'}->con = $content;
    $cin->{'m2m:cin'}->cnf = $contentFormat;
    $result = Antares::getInstance()->create($this->ri, json_encode($cin), 4);
    return $result;
  }
}

/**
 * OM2M ContentInstance resource.
 * @package Om2m
 * @see Container
 */
class ContentInstance extends Om2mResource {
  public $cnf;
  public $con;
  
  public function __construct($ri, $pi, $rn, $ct, $cnf, $con) {
    parent::__construct($ri, $pi, $rn, $ct, 4);
    $this->cnf = $cnf;
    $this->con = $con;
  }

  /**
   * get content instance content.
   * @return string
   */
  public function getContent() {
    return $this->con;
  }

  /**
   * get content instance content format.
   * @return string
   */
  public function getContentFormat() {
    return $this->cnf;
  }
}

/**
 * OM2M Subscription resource.
 * @package Om2m
 * @see Container
 * @see AE
 */
class Subcription extends Om2mResource {
  public $nu;
  public $nct;

  public function __construct($ri, $pi, $rn, $ct, $nu, $nct) {
    parent::__construct($ri, $pi, $rn, $ct, 5);
    $this->nu = $nu;
    $this->nct = $nct;
  }
}

/**
 * OM2M DiscoveryParam.
 * @package Om2m
 */
class DiscoveryParam {
  public $fu = 1;
  public $drt;
  public $ty;
  public $lbl;
  public $crb = null;
  public $cra = null;
  public $lim = null;
  public $ofst = null;

  /**
   * return KeyValue array of filter parameter.
   * @param string $uri
   */
  public function toQueryParam() {
    $query = array();
    if ($this->fu) {
      $query['fu'] = $this->fu;
    }
    if ($this->drt) {
      $query['drt'] = $this->drt;
    }
    if ($this->ty) {
      $query['ty'] = $this->ty;
    }
    if ($this->lbl && is_array($this->lbl)) {
      foreach ($this->lbl as $label) {
        $query['lbl'][] = $label;
      }
    }
    if ($this->crb) {
      $query['crb'] = $this->crb;
    }
    if ($this->cra) {
      $query['cra'] = $this->cra;
    }
    if ($this->lim) {
      $query['lim'] = $this->lim;
    }
    if ($this->ofst) {
      $query['ofst'] = $this->ofst;
    }
    return $query;
  }

  /**
   * return query string of filter parameter.
   * @return string
   */
  public function toQueryString() {
    return http_build_query($this->toQueryParam());
  }
}
