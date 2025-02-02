<?php
/**
 * iroha Board Project
 *
 * @author        Kotaro Miura
 * @copyright     2015-2016 iroha Soft, Inc. (http://irohasoft.jp)
 * @link          http://irohaboard.irohasoft.jp
 * @license       http://www.gnu.org/licenses/gpl-3.0.en.html GPL License
 */

App::uses("AppController", "Controller");

class GroupsController extends AppController
{
    public $components = [
        "Paginator",
        "Security" => [
            "csrfUseOnce" => false,
        ],
    ];

    /**
     * グループ一覧を表示
     */
    public function admin_index()
    {
        $this->Group->recursive = 0;
        $this->Group->virtualFields["course_title"] =
            "GroupCourse.course_title"; // 外部結合テーブルのフィールドによるソート用

        $this->Paginator->settings = [
            "fields" => ["Group.id", "Group.title", "Group.comment", "Group.status", "Group.leader_id", "Group.created", "Group.modified",
                         "GroupCourse.course_title", "User.name"
                        ],
            "limit" => 20,
            "order" => ["status desc", "created desc"],
            "joins" => [
                [
                    "table" => "ib_users",
                    "alias" => "User",
                    "type" => "LEFT",
                    "conditions" => "User.id = Group.leader_id",
                ],
                [
                    "type" => "LEFT OUTER",
                    "alias" => "GroupCourse",
                    "table" =>
                        '(SELECT gc.group_id, group_concat(c.title order by c.id SEPARATOR \', \') as course_title FROM ib_groups_courses gc INNER JOIN ib_courses c ON c.id = gc.course_id  GROUP BY gc.group_id)',
                    "conditions" => "Group.id = GroupCourse.group_id",
                ],
            ],
        ];

        $this->set("groups", $this->Paginator->paginate());
    }

    /**
     * グループの追加
     */
    public function admin_add()
    {
        $this->admin_edit();
        $this->render("admin_edit");
    }

    /**
     * グループの編集
     * @param int $group_id 編集するグループのID
     */
    public function admin_edit($group_id = null)
    {
        $this->loadModel("User");

        if ($this->action == "edit" && !$this->Group->exists($group_id)) {
            throw new NotFoundException(__("Invalid group"));
        }
        if ($this->request->is(["post", "put"])) {
            if ($this->Group->save($this->request->data)) {
                $this->Flash->success(__("グループ情報を保存しました"));
                return $this->redirect([
                    "action" => "index",
                ]);
            } else {
                $this->Flash->error(
                    __("The group could not be saved. Please, try again.")
                );
            }
        } else {
            $options = [
                "conditions" => [
                    "Group." . $this->Group->primaryKey => $group_id,
                ],
            ];
            $this->request->data = $this->Group->find("first", $options);
        }

        $admins = $this->User->getAdminList();
        $this->set(compact("admins"));

        $courses = $this->Group->Course->find("list");
        $this->set(compact("courses"));
    }

    /**
     * グループの削除
     * @param int $group_id 削除するグループのID
     */
    public function admin_delete($group_id = null)
    {
        $this->Group->id = $group_id;
        if (!$this->Group->exists()) {
            throw new NotFoundException(__("Invalid group"));
        }
        $this->request->allowMethod("post", "delete");
        if ($this->Group->delete()) {
            $this->Flash->success(__("グループ情報を削除しました"));
        } else {
            $this->Flash->error(
                __("The group could not be deleted. Please, try again.")
            );
        }
        return $this->redirect([
            "action" => "index",
        ]);
    }
}
