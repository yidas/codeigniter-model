<?php

/**
 * My_model
 *
 * Based on BaseModel, My_model is customized for your web application with features, such as the 
 * verification of user ID and company ID for multiple user layers.
 * This example My_model assumes that a user is belong to a company, so each data row is belong to
 * a user with that company. The Model basic funcitons overrided BaseModel with user and company 
 * verification to implement the protection. 
 *
 * @author   Nick Tsai <myintaer@gmail.com>
 * @see      https://github.com/yidas/codeigniter-model
 * @since    BaseMdoel 0.15.0
 */
class My_model extends BaseModel
{
    /* Configuration by Inheriting */
    
    // Fill up with your DB key of Slave Databases if needed
    protected $databaseRead = false;

    // The regular PK Key in App
    protected $primaryKey = 'id';

    // Mainstream creating field name
    const CREATED_AT = 'created_at';

    // Mainstream updating field name
    const UPDATED_AT = 'updated_at';

    protected $timestamps = true;

    // Use unixtime for saving datetime
    protected $dateFormat = 'unixtime';

    // Record status for checking is deleted or not
    const RECORD_DELETED = 'is_deleted';

    // 0: actived, 1: deleted
    protected $recordDeletedFalseValue = '1';

    protected $recordDeletedTrueValue = '0';

    const DELETED_AT = 'deleted_at';


    /* Application Features */

    /**
     * @var string Auto Field for user SN
     */
    protected $userAttribute = 'user_id';

    /**
     * @var string Auto Field for company SN
     */
    protected $companyAttribute = 'company_id';

    /**
     * @var string Field for created user
     */
    protected $createdUserAttribute = 'created_by';

    /**
     * @var string Field for updated user
     */
    protected $updatedUserAttribute = 'updated_by';

    /**
     * @var string Field for deleted user
     */
    protected $deletedUserAttribute = 'deleted_by';

    /**
     * @var int Application ACL
     */
    protected $companyID;

    /**
     * @var int Application User
     */
    protected $userID;

    function __construct()
    {
        parent::__construct();

        // Assgin UserID and CompanyID from your own App mechanism
        $this->loadACL();
    }
    
    /**
     * Load ACL from application
     * 
     * @param int $companyID
     * @param int $userID
     */
    public function loadACL($companyID=NULL, $userID=NULL)
    {
        $this->companyID = ($companyID) ? $companyID : $this->config->item('sessionCompanyID');
        $this->userID = ($userID) ? $userID : $this->config->item('sessionUserID');
    }

    /**
     * Override _globalScopes with User & Company validation
     */
    protected function _globalScopes()
    {
        if ($this->companyAttribute) {
            
            $this->getBuilder()->where(
                $this->_field($this->companyAttribute), 
                $this->$companyID
                );
        }
        
        if ($this->userAttribute) {
            
            $this->getBuilder()->where(
                $this->_field($this->userAttribute), 
                $this->userID
                );
        }

        return parent::_globalScopes();
    }

    /**
     * Override _attrEventBeforeInsert()
     */
    protected function _attrEventBeforeInsert(&$attributes)
    {
        // Auto Company
        if ($this->companyAttribute && !isset($attr[$this->companyAttribute])) {
            
            $attributes[$this->companyAttribute] = $this->companySN;
        }

        // Auto User
        if ($this->userAttribute && !isset($attr[$this->userAttribute])) {
            
            $attributes[$this->userAttribute] = $this->userSN;
        }

        // Auto created_by
        if ($this->createdUserAttribute) {
            $attributes[$this->createdUserAttribute] = $this->userSN;
        }
        
        return parent::_attrEventBeforeInsert($attributes);
    }

    /**
     * Override _attrEventBeforeUpdate()
     */
    public function _attrEventBeforeUpdate(&$attributes)
    {
        // Auto updated_by
        if ($this->updatedUserAttribute) {
            $attributes[$this->updatedUserAttribute] = $this->userSN;
        }

        return parent::_attrEventBeforeUpdate($attributes);
    }

    /**
     * Override _attrEventBeforeDelete()
     */
    public function _attrEventBeforeDelete(&$attributes)
    {
        // Auto deleted_by
        if ($this->deletedUserAttribute) {
            $attributes[$this->deletedUserAttribute] = $this->userSN;
        }

        return parent::_attrEventBeforeDelete($attributes);
    }
}


