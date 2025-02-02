<?php
/**
 * iroha Board Project
 *
 * @author        Kotaro Miura
 * @copyright     2015-2016 iroha Soft, Inc. (http://irohasoft.jp)
 * @link          http://irohaboard.irohasoft.jp
 * @license       http://www.gnu.org/licenses/gpl-3.0.en.html GPL License
 */

App::uses("AppModel", "Model");

/**
 * UsersGroup Model
 *
 * @property User $User
 * @property Group $Group
 */
class UsersGroup extends AppModel
{
    /**
     * Validation rules
     *
     * @var array
     */
    public $validate = [
        "user_id" => [
            "numeric" => [
                "rule" => ["numeric"],
                // 'message' => 'Your custom message here',
                // 'allowEmpty' => false,
                // 'required' => false,
                // 'last' => false, // Stop validation after this rule
                // 'on' => 'create', // Limit validation to 'create' or
                // 'update' operations
            ],
        ],
        "group_id" => [
            "numeric" => [
                "rule" => ["numeric"],
                // 'message' => 'Your custom message here',
                // 'allowEmpty' => false,
                // 'required' => false,
                // 'last' => false, // Stop validation after this rule
                // 'on' => 'create', // Limit validation to 'create' or
                // 'update' operations
            ],
        ],
    ];

    // The Associations below have been created with all possible keys, those
    // that are not needed can be removed

    /**
     * belongsTo associations
     *
     * @var array
     */
    public $belongsTo = [
        "User" => [
            "className" => "User",
            "foreignKey" => "user_id",
            "conditions" => "",
            "fields" => "",
            "order" => "",
        ],
        "Group" => [
            "className" => "Group",
            "foreignKey" => "group_id",
            "conditions" => "",
            "fields" => "",
            "order" => "",
        ],
    ];

    public function findUserGroup($user_id)
    {
        $data = $this->find("all", [
            "fields" => ["group_id"],
            "conditions" => ["user_id" => $user_id],
            "recursive" => -1,
        ]);
        //$this->log($data);
        return $data[0]["UsersGroup"]["group_id"];
    }
}
