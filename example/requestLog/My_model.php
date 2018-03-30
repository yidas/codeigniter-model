<?php

/**
 * My_model
 *
 * This My_model is for Log Model implement with IP and headers concern.
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

    // Mainstream creating field name
    const CREATED_AT = 'created_at';
    
    // Log has no updating
    const UPDATED_AT = null;

    protected $timestamps = true;

    // Use unixtime for saving datetime
    protected $dateFormat = 'unixtime';

    // Record status for checking is deleted or not
    const SOFT_DELETED = false;

    /* Application Features */

    /**
     * @var string Field for IP
     */
    public $createdIpAttribute = 'ip';
    
    /**
     * @var string Field for User Agent
     */
    public $createdUserAgentAttribute = '';

    /**
     * @var string Field for Request URI
     */
    public $createdRequestUriAttribute = '';
    
    /**
     * Request Headers based on $_SERVER
     * 
     * @var string Header => Field
     * @example
     *  ['HTTP_AUTHORIZATION' => 'header_auth']
     */
    public $createdHeaderAttributes = [];

    /**
     * Override _attrEventBeforeInsert()
     */
    protected function _attrEventBeforeInsert(&$attributes)
    {
        // Auto IP
        if ($this->createdIpAttribute && !isset($attributes[$this->createdIpAttribute])) {
            $attributes[$this->createdIpAttribute] = $this->input->ip_address();
        }
        // Auto User Agent
        if ($this->createdUserAgentAttribute && !isset($attributes[$this->createdUserAgentAttribute])) {
            $attributes[$this->createdUserAgentAttribute] = $this->input->user_agent();
        }
        // Auto Request URI (`$this->uri->uri_string()` couldn't include QUERY_STRING)
        if ($this->createdRequestUriAttribute && !isset($attributes[$this->createdRequestUriAttribute])) {
            $attributes[$this->createdRequestUriAttribute] = isset($_SERVER['REQUEST_URI']) 
                ? $_SERVER['REQUEST_URI'] : '';
        }
        // Auto Hedaers
        foreach ((array)$this->createdHeaderAttributes as $header => $field) {
            if ($field && !isset($attributes[$field])) {
                $attributes[$field] = isset($_SERVER[$header]) 
                    ? $_SERVER[$header] : '';
            }
        }
        
        return parent::_attrEventBeforeInsert($attributes);
    }
}
