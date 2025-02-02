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
 * Group Model
 *
 * @property Content $Content
 * @property ContentsQuestion $ContentsQuestion
 * @property Course $Course
 * @property Record $Record
 * @property User $User
 */
class Group extends AppModel
{
    public $order = "Group.title";

    /**
     * Validation rules
     *
     * @var array
     */
    public $validate = [
        "title" => [
            "notBlank" => [
                "rule" => ["notBlank"],
                // 'message' => 'Your custom message here',
                // 'allowEmpty' => false,
                // 'required' => false,
                // 'last' => false, // Stop validation after this rule
                // 'on' => 'create', // Limit validation to 'create' or
                // 'update' operations
            ],
        ],
        "status" => [
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
     * hasMany associations
     *
     * @var array
     */
    /*
	public $hasMany = array(
			'User' => array(
					'className' => 'User',
					'foreignKey' => 'group_id',
					'dependent' => false,
					'conditions' => '',
					'fields' => '',
					'order' => '',
					'limit' => '',
					'offset' => '',
					'exclusive' => '',
					'finderQuery' => '',
					'counterQuery' => ''
			)
	);
	*/
    public $hasAndBelongsToMany = [
        "Course" => [
            "className" => "Course",
            "joinTable" => "groups_courses",
            "foreignKey" => "group_id",
            "associationForeignKey" => "course_id",
            "unique" => "keepExisting",
            "conditions" => "",
            "fields" => "",
            "order" => "",
            "limit" => "",
            "offset" => "",
            "finderQuery" => "",
        ],
    ];

    /**
     * 指定したグループに所属するユーザIDリストを取得
     *
     * @param int $group_id グループID
     * @return array ユーザIDリスト
     */
    public function getUserIdByGroupID($group_id)
    {
        App::import("Model", "User");
        $this->User = new User();
        $data = $this->User->find("all", [
            "fields" => ["id"],
            "conditions" => [
                "group_id" => $group_id,
            ],
            "recursive" => -1,
        ]);

        $list = [];
        for ($i = 0; $i < count($data); $i++) {
            $list[$i] = $data[$i]["User"]["id"];
        }
        return $list;
    }

    /**
     * グループ一覧を取得
     *
     * @return array グループ一覧
     */

    public function findGroup()
    {
        $data = $this->find("all", [
            "fields" => ["id", "title", "status"],
            "order" => ["status desc", "created desc"],
            "recursive" => -1,
        ]);
        //$this->log($data);
        return $data;
    }

    // 公開状態のグループのみ取得
    public function findPublicGroup()
    {
        $data = $this->find("all", [
            "fields" => ["id", "title"],
            "conditions" => [
                "status" => 1,
            ],
            "recursive" => -1,
        ]);
        //$this->log($data);
        return $data;
    }

    public function getGroupList()
    {
        $groups = $this->find("all");
        $data = ["0" => "全て"];

        for ($i = 0; $i < count($groups); $i++) {
            $data["" . $groups[$i]["Group"]["id"]] =
                $groups[$i]["Group"]["title"];
        }

        return $data;
    }
}
