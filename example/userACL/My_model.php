<?php

/**
 * My_model
 *
 * Based on yidas\Model, My_model is customized for your web application with schema such as 
 * primary key and column names for behavior setting. Futher, all of your model may need access 
 * features, such as the verification of user ID and company ID for multiple user layers.
 *
 * This example My_model assumes that a user is belong to a company, so each data row is belong to
 * a user with that company. The Model basic funcitons overrided BaseModel with user and company 
 * verification to implement the protection. 
 *
 * @author   Nick Tsai <myintaer@gmail.com>
 * @version  2.0.0
 * @see      https://github.com/yidas/codeigniter-model/tree/master/example
 * @since    \yidas\Mdoel 2.0.0
 * @see      https://github.com/yidas/codeigniter-model
 */
class My_model extends yidas\Model
{
    /* Configuration by Inheriting */
    
    // Fill up with your DB key of Slave Databases if needed
    protected $databaseRead = false;

    // The regular PK Key in App
    protected $primaryKey = 'id';

    protected $timestamps = true;

    // Mainstream creating field name
    const CREATED_AT = 'created_at';

    // Mainstream updating field name
    const UPDATED_AT = 'updated_at';

    // Use unixtime for saving datetime
    protected $dateFormat = 'unixtime';

    // Record status for checking is deleted or not
    const SOFT_DELETED = 'is_deleted';

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
                $this->companyID
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
        if ($this->companyAttribute && !isset($attributes[$this->companyAttribute])) {
            
            $attributes[$this->companyAttribute] = $this->companyID;
        }
        // Auto User
        if ($this->userAttribute && !isset($attributes[$this->userAttribute])) {
            
            $attributes[$this->userAttribute] = $this->userID;
        }
        // Auto created_by
        if ($this->createdUserAttribute && !isset($attributes[$this->createdUserAttribute])) {
            $attributes[$this->createdUserAttribute] = $this->userID;
        }
        
        return parent::_attrEventBeforeInsert($attributes);
    }
    /**
     * Override _attrEventBeforeUpdate()
     */
    public function _attrEventBeforeUpdate(&$attributes)
    {
        // Auto updated_by
        if ($this->updatedUserAttribute && !isset($attributes[$this->updatedUserAttribute])) {
            $attributes[$this->updatedUserAttribute] = $this->userID;
        }
        return parent::_attrEventBeforeUpdate($attributes);
    }
    /**
     * Override _attrEventBeforeDelete()
     */
    public function _attrEventBeforeDelete(&$attributes)
    {
        // Auto deleted_by
        if ($this->deletedUserAttribute && !isset($attributes[$this->deletedUserAttribute])) {
            $attributes[$this->deletedUserAttribute] = $this->userID;
        }
        return parent::_attrEventBeforeDelete($attributes);
    }
}


