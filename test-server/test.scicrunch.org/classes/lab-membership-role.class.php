<?php

class LabMembershipRole extends DBObject3 {
    protected static $_fields_definitions = null;
    protected static $_table_name = "lab_membership_roles";
    protected static $_primary_key_field = "id";

    public static function init() {
        self::$_fields_definitions = Array(
            "id"                => self::fieldDef("id", "i", true),
            "lab_membership_id" => self::fieldDef("lab_membership_id", "i", true),
            "timestamp"         => self::fieldDef("timestamp", "i", true),
            "role"              => self::fieldDef("role", "s", true, Array("allowed_values" => self::$ALLOWED_ROLES)),
        );
    }

    public static $ALLOWED_ROLES = Array(
        "Principal Investigator",
        "Manager",
        "Member",
    );

    public static function createNewObj(LabMembership $lab_membership, $role) {
        $timestamp = time();

        $obj = self::insertObj(Array(
            "id" => NULL,
            "lab_membership_id" => $lab_membership->id,
            "timestamp" => $timestamp,
            "role" => $role,
        ));

        return $obj;
    }

    static public function deleteObj($obj, User $user = NULL) {
        $obj->saveHistory("delete", $user);
        parent::deleteObj($obj);
    }

    public function updateDB(User $user = NULL) {
        $this->saveHistory("update", $user);
        parent::updateDB();
    }

    public function arrayForm() {
        return Array(
            "role" => $this->role,
        );
    }
}
LabMembershipRole::init();

?>
