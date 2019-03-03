<?php
class UserModel extends Model {
    public $insert_allow_values = ['Name', 'Email', 'Status'];
    public $update_allow_values = ['UUID', 'Name', 'Email', 'Status'];
    public $number_values = [];
    public $not_empty_values = [
        'Name' => 'User Name',
        'Email' => 'Email Address'
    ];
    public $oResponse = null;
    public $oRequest = null;
    public $oMysql = null;
    public function __construct($mysql, $request, $response)
    {
        $this->oMysql = $mysql;
        $this->oRequest = $request;
        $this->oResponse = $response;
    }

    public function add($data)
    {
        $uniqueID = new UniqueID();
        $data['UUID'] = $uniqueID->get_uuid('bct');
        $data['CreatedTime'] = date('Y-m-d H:i:s');
        $flag = $this->oMysql->insert('bo_user', $data);
        return $data['UUID'];
    }

    public function attach_groups($group_uuids, $uuid)
    {
        if(empty($group_uuids)) return false;
        $group_users = [];
        foreach ($group_uuids as $k => $v) {
             $tmp['GroupUUID'] = $v;
             $tmp['UserUUID'] = $uuid;
             $group_users[] = $tmp;
        }
        if(!empty($group_users))
        {
            $where = "UserUUID = '{$uuid}'";
            $this->oMysql->delete('bo_group_user', $where);
            $this->oMysql->insert_batch('bo_group_user', $group_users);
        }
    }
    public function update($uuid, $data)
    {
        $where = "UUID='{$uuid}'";
        return $this->oMysql->update('bo_user', $data, $where);
    }
}