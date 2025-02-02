<?php
/*
 * Ripple  Project
 *
 * @author        Enfu Guo
 * @copyright     NPO Organization uec support
 * @link          http://uecsupport.dip.jp/
 * @license       http://www.gnu.org/licenses/gpl-3.0.en.html GPL License
 */

App::uses("AppController", "Controller");
App::uses("RecordsQuestion", "RecordQuestion");
App::uses("UsersGroup", "UsersGroup");
App::uses("Course", "Course");
App::uses("User", "User");
App::uses("Group", "Group");

class SoapsController extends AppController
{
    public $helpers = ["Html", "Form"];

    public $components = ["Security"];


    public function beforeFilter() {
        $this->Security->blackHoleCallback = 'blackhole';
        parent::beforeFilter();
    }

    public function blackhole($type) {
        switch($type){
            case 'csrf':
                $this->Flash->error(__("CSRFエラーです。"));
                $this->redirect(["action" => "index"]);
                break;
            case 'secure':
                $this->Flash->error(__("保存に失敗しました。フォーム送信後のブラウザバックはおやめください。"));
                $this->redirect(["action" => "index"]);
                break;
            default:
                break;
        }
    }

    public function admin_index()
    {
    }

    public function admin_find_by_group()
    {
        $this->loadModel("Group");
        $groupData = $this->Group->findPublicGroup();
        $this->set("groupData", $groupData);
    }

    public function admin_find_by_student()
    {
        $this->loadModel("User");

        if ($this->request->is("post")) {
            $conditions = $this->request->data;
            $username = $conditions["Search"]["username"];
            $name = $conditions["Search"]["name"];

            $user_list = $this->User->findUserList($username, $name);
        } else {
            $user_list = $this->User->getUserList();
        }
        $this->set("user_list", $user_list);
    }

    /**
     * @param int $group_id グループID
     * グループでSOAPを記入する
     */
    public function admin_group_edit($group_id)
    {
        $this->loadModel("UsersCourse");
        $this->loadModel("User");
        $this->loadModel("Enquete");
        $this->loadModel("Attendance");
        $this->loadModel("Date");

        // SOAP入力欄の最大文字数
        $input_max_length = 200;
        $this->set("input_max_length", $input_max_length);

        $this->set("group_id", $group_id);

        // 最後の授業日
        $last_lecture_date_info = $this->Date->find("first", [
            "fields" => ["id", "date"],
            "conditions" => [
                "date <= " => date("Y-m-d"),  // 今日以前の授業日
            ],
            "order" => "date DESC",
            "recursive" => -1,
        ]);
        $last_lecture_date = $last_lecture_date_info["Date"]["date"];

        //メンバーリスト
        $user_list = $this->User->find("list", [
            "conditions" => [
                "role" => "user",
            ],
        ]);
        $this->set("user_list", $user_list);

        //グループ内の出席メンバーを探す
        //$members = $this->User->findAllStudentInGroup($group_id);
        $members = $this->Attendance->find("all", [
            "fields" => [
                "User.id",
                "User.group_id"
            ],
            "conditions" => [
                "User.group_id" => $group_id,
                "Attendance.status" => 1,
                "Attendance.login_time BETWEEN ? AND ?" => [
                    $last_lecture_date,
                    date("Y-m-d H:i:s"),
                ]
            ],
            "order" => [
                "User.username" => "ASC"
            ],
        ]);
        $this->set("members", $members);

        // グループ内ユーザのID一覧
        $members_ids = array_map(function($member){
                return $member['User']['id'];
            },
            $members
        );

        // user_idとpic_pathの配列
        $group_pic_paths = $this->User->findGroupPicPaths($members);
        $this->set("group_pic_paths", $group_pic_paths);

        // 公開状態のグループ一覧を作り，配列の形を整形する
        $group_list = $this->Group->find("list", [
            "conditions" => [
                "status" => 1,
            ],
        ]);
        $this->set("group_list", $group_list);

        //日付リスト
        $today_date = isset($this->request->query["today_date"])
            ? $this->request->query["today_date"]
            : ["year" => date("Y"), "month" => date("m"), "day" => date("d")];
        $this->set("today_date", $today_date);

        //提出したアンケートを検索（直近の授業日付）
        $conditions = [];
        $conditions["Enquete.user_id"] = $members_ids;
        $conditions["Enquete.created BETWEEN ? AND ?"] = [
            $last_lecture_date,
            date("Y-m-d H:i:s"),
        ];
        $enquete_history = $this->Enquete->find("all", [
            "conditions" => $conditions,
        ]);

        $enquete_inputted = [];
        foreach ($enquete_history as $history) {
            $his_user_id = $history["Enquete"]["user_id"];
            $enquete_inputted["$his_user_id"] = $history["Enquete"];
        }
        $this->set("enquete_inputted", $enquete_inputted);

        //入力したSOAPを検索（前回の授業から）
        $conditions = [];
        $conditions["Soap.user_id"] = $members_ids;
        $conditions["Soap.created BETWEEN ? AND ?"] = [
            $last_lecture_date,
            date("Y-m-d H:i:s"),
        ];
        $soap_history = $this->Soap->find("all", [
            "conditions" => $conditions,
        ]);

        $soap_inputted = [];
        foreach ($soap_history as $history) {
            $his_user_id = $history["Soap"]["user_id"];
            $soap_inputted["$his_user_id"] = $history["Soap"];
        }
        $this->set("soap_inputted", $soap_inputted);

        // 教材現状
        $users_courses = $this->UsersCourse->find("all", [
            "fields" => ["User.id", "Course.id", "Course.title"],
            "conditions" => [
                "UsersCourse.user_id" => $members_ids,
                "Course.status" => 1,
            ],
            "order" => [
                "User.username" => "ASC",
                "Course.category_id" => "ASC",
                "Course.sort_no" => "ASC",
            ],
            "recursive" => 0,
        ]);

        $users_course_list = [];
        foreach ($users_courses as $users_course) {
            $his_user_id = $users_course["User"]["id"];
            $users_course_id = $users_course["Course"]["id"];
            $users_course_list["$his_user_id"]["$users_course_id"] = $users_course["Course"]["title"];
        }
        $this->set("users_course_list", $users_course_list);

        //登録
        if ($this->request->is(["post", "put"])) {
            $soaps = $this->request->data;

            $created =
                $today_date["year"] .
                "-" .
                $today_date["month"] .
                "-" .
                $today_date["day"];

            foreach ($soaps as &$soap) {
                if (  // S・O・A・Pのいずれも書かれていないものは保存しない
                    $soap["S"] == "" &&
                    $soap["O"] == "" &&
                    $soap["A"] == "" &&
                    $soap["P"] == ""
                ) {
                    continue;
                }

                // 所属グループを更新
                $this->User->id = $soap["user_id"];
                $this->User->saveField("group_id", $soap["group_id"]);

                $inputed = $soap["today_date"];
                $input_date =
                    $inputed["year"] .
                    "-" .
                    $inputed["month"] .
                    "-" .
                    $inputed["day"];
                $input_date =
                    date("w", strtotime($input_date)) == 0
                        ? $input_date
                        : date(
                            "Y-m-d",
                            strtotime(" last sunday ", strtotime($input_date))
                        );

                $soap["created"] = $input_date . date(" H:i:s");

                if ($this->Soap->save($soap)) {
                    $this->Soap->create(false); //これがないと，ループ内での保存はできない
                    continue;
                }
                $this->Flash->error(
                    __("提出は失敗しました、もう一回やってください。")
                );
            }
            $this->Flash->success(__("提出しました、ありがとうございます"));
            return $this->redirect(["action" => "index"]);
        }
    }

    /**
     * @param int $user_id ユーザID
     * 個人単位でSOAPを記入する
    */
    public function admin_student_edit($user_id)
    {
        $this->loadModel("UsersCourse");
        $this->loadModel("User");
        $this->loadModel("Enquete");
        $this->loadModel("Attendance");
        $this->loadModel("Date");

        // SOAP入力欄の最大文字数
        $input_max_length = 200;
        $this->set("input_max_length", $input_max_length);

        $pic_path = $this->User->findUserPicPath($user_id);
        $this->set("pic_path", $pic_path);

        //日付リスト
        $today_date = isset($this->request->query["today_date"])
            ? $this->request->query["today_date"]
            : ["year" => date("Y"), "month" => date("m"), "day" => date("d")];
        $this->set("today_date", $today_date);

        // 最後の授業日
        $last_lecture_date_info = $this->Date->find("first", [
            "fields" => ["id", "date"],
            "conditions" => [
                "date <= " => date("Y-m-d"),  // 今日以前の授業日
            ],
            "order" => "date DESC",
            "recursive" => -1,
        ]);
        $last_lecture_date = $last_lecture_date_info["Date"]["date"];

        //提出したアンケートを検索（直近の授業日付）
        $conditions = [];
        $conditions["Enquete.user_id"] = $user_id;
        $conditions["Enquete.created BETWEEN ? AND ?"] = [
            $last_lecture_date,
            date("Y-m-d H:i:s"),
        ];
        $enquete_history = $this->Enquete->find("all", [
            "conditions" => $conditions,
        ]);

        $enquete_inputted = [];
        foreach ($enquete_history as $history) {
            $his_user_id = $history["Enquete"]["user_id"];
            $enquete_inputted["$his_user_id"] = $history["Enquete"];
        }
        $this->set("enquete_inputted", $enquete_inputted);

        //メンバーリスト
        $user_list = $this->User->find("list");
        $this->set("user_list", $user_list);

        //メンバーのグループを探す
        $group_id = $this->User->findUserGroup($user_id);

        $this->set("user_id", $user_id);

        // 公開状態のグループ一覧を作り，配列の形を整形する
        $group_list = $this->Group->find("list", [
            "conditions" => [
                "status" => 1,
            ],
        ]);
        $this->set("group_list", $group_list);

        $this->set("today_date", $today_date);

        //入力したSOAPを検索（先週の授業から）
        $conditions = [];
        $conditions["Soap.user_id"] = $user_id;
        $conditions["Soap.created BETWEEN ? AND ?"] = [
            $last_lecture_date,
            date("Y-m-d H:i:s"),
        ];
        $soap_history = $this->Soap->find("all", [
            "conditions" => $conditions,
        ]);

        $soap_inputted = [];
        foreach ($soap_history as $history) {
            $his_user_id = $history["Soap"]["user_id"];
            $soap_inputted["$his_user_id"] = $history["Soap"];
            $group_id = $history["Soap"]["group_id"];
        }
        $this->set("soap_inputted", $soap_inputted);

        $this->set("group_id", $group_id);

        //教材現状
        $course_list = $this->UsersCourse->find("list", [
            "fields" => ["Course.id", "Course.title"],
            "conditions" => [
                "UsersCourse.user_id" => $user_id,
                "Course.status" => 1,
            ],
            "order" => [
                "Course.category_id" => "ASC",
                "Course.sort_no" => "ASC",
            ],
            "recursive" => 0,
        ]);
        $this->set("course_list", $course_list);

        //登録
        if ($this->request->is(["post", "put"])) {
            $this->loadModel("Record");
            $soaps = $this->request->data;
            $created =
                $today_date["year"] .
                "-" .
                $today_date["month"] .
                "-" .
                $today_date["day"];
            foreach ($soaps as &$soap) {
                if (  // S・O・A・Pのいずれも書かれていないものは保存しない
                    $soap["S"] == "" &&
                    $soap["O"] == "" &&
                    $soap["A"] == "" &&
                    $soap["P"] == ""
                ) {
                    continue;
                }

                // 所属グループを更新
                $this->User->id = $soap["user_id"];
                $this->User->saveField("group_id", $soap["group_id"]);

                // SOAP記入日で最後に勉強した教材を取得
                $inputed = $soap["today_date"];
                $input_date =
                    $inputed["year"] .
                    "-" .
                    $inputed["month"] .
                    "-" .
                    $inputed["day"];
                $soap[
                    "studied_content"
                ] = $this->Record->studiedContentOnTheDate(
                    $soap["user_id"],
                    $input_date
                );
                $soap["created"] = $input_date . date(" H:i:s");
                if ($this->Soap->save($soap)) {
                    $this->Soap->create(false); //これがないと，ループ内での保存はできない
                    continue;
                }
                $this->Flash->error(
                    __("提出は失敗しました、もう一回やってください。")
                );
            }
            $this->Flash->success(__("提出しました、ありがとうございます"));
            return $this->redirect(["action" => "index"]);
        }
    }

    /**
     * @param int $soap_id SOAPのID
     * 一度作成したSOAPを編集する
     */
    public function admin_id_edit($soap_id)
    {
        $this->loadModel("UsersCourse");
        $this->loadModel("User");

        $edited_soap = $this->Soap->find("first", [
            "fields" => [
                "Soap.id", "Soap.user_id", "Soap.group_id", "Soap.current_status", "Soap.S", "Soap.O", "Soap.A", "Soap.P", "Soap.comment", "Soap.created",
                "User.name", "User.pic_path",
                "Group.title"
            ],
            "conditions" => [
                "Soap.id" => $soap_id,
            ],
        ]);
        $this->set("edited_soap", $edited_soap["Soap"]);
        $this->set("user_info", $edited_soap["User"]);
        $this->set("gruup_info", $edited_soap["Group"]);

        // SOAP入力欄の最大文字数
        $input_max_length = 200;
        $this->set("input_max_length", $input_max_length);

        // 公開状態のグループ一覧を作り，配列の形を整形する
        $group_list = $this->Group->find("list", [
            "conditions" => [
                "status" => 1,
            ],
        ]);
        $this->set("group_list", $group_list);

        //教材現状
        $course_list = $this->UsersCourse->find("list", [
            "fields" => ["Course.id", "Course.title"],
            "conditions" => [
                "UsersCourse.user_id" => $edited_soap["Soap"]["user_id"],
                "Course.status" => 1,
            ],
            "order" => [
                "Course.category_id" => "ASC",
                "Course.sort_no" => "ASC",
            ],
            "recursive" => 0,
        ]);
        $this->set("course_list", $course_list);

        //登録
        if ($this->request->is(["post", "put"])) {
            $this->loadModel("Record");
            $soap = $this->request->data["Soap"];

            // 所属グループを更新
            $this->User->id = $soap["user_id"];
            $this->User->saveField("group_id", $soap["group_id"]);

            // SOAP記入日で最後に勉強した教材を取得
            $inputed = $soap["today_date"];
            $input_date = $inputed["year"] . "-" . $inputed["month"] . "-" . $inputed["day"];
            $soap["studied_content"] = $this->Record->studiedContentOnTheDate(
                $soap["user_id"],
                $input_date
            );

            if ($this->Soap->save($soap)) {
                $this->Flash->success(__("更新しました。"));
                return $this->redirect([
                    "controller" => "soaprecords",
                    "action" => "admin_index",
                ]);
            }
            $this->Flash->error(__("更新に失敗しました。もう一回やってください。"));
        }
    }

    /**
     * SOAPの削除
     * @param int $soap_id 削除するSOAPのID
     */
    public function admin_delete($soap_id = null)
    {
        if (Configure::read("demo_mode")) {
            return;
        }

        $this->Soap->id = $soap_id;
        if (!$this->Soap->exists()) {
            throw new NotFoundException(__("Invalid SOAP Data"));
        }
        $this->request->allowMethod("post", "delete");
        if ($this->Soap->delete()) {
            $this->Flash->success(__("SOAPが削除されました"));
        } else {
            $this->Flash->error(__("SOAPを削除できませんでした"));
        }
        return $this->redirect([
            "controller" => "soaprecords",
            "action" => "index",
        ]);
    }
}

?>
