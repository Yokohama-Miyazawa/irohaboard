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
    /*
    @param int $group_id グループID
    グループでSOAPを記入する
  */
    public function admin_group_edit($group_id)
    {
        $this->loadModel("Course");
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

        $today = date("Y-m-d");
        $fdate =
            date("w") == 0
                ? $today
                : date("Y-m-d", strtotime(" last sunday ", strtotime($today)));

        $lecture_date_info = $this->Date->find("first", [
            "fields" => ["id", "date"],
            "conditions" => [
                "date >= " => $fdate,
            ],
            "recursive" => -1,
        ]);

        $created_day = $lecture_date_info["Date"]["date"];

        $edate = date(
            "Y-m-d",
            strtotime(" next saturday ", strtotime($created_day))
        );

        $conditions["Soap.created BETWEEN ? AND ?"] = [
            $created_day,
            $edate . " 23:59:59",
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

        //教材現状
        $course_list = $this->Course->find("list");
        $this->set("course_list", $course_list);

        //登録
        if ($this->request->is("post")) {
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

    public function admin_student_edit($user_id)
    {
        $this->loadModel("Course");
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

        $today = date("Y-m-d");
        $fdate =
            date("w", strtotime($today)) == 0  // 今日は日曜日か
                ? $today
                : date("Y-m-d", strtotime(" last sunday ", strtotime($today)));  // 日曜日でないなら直前の日曜日を取得

        $lecture_date_info = $this->Date->find("first", [
            "fields" => ["id", "date"],
            "conditions" => [
                "date >= " => $fdate,
            ],
            "recursive" => -1,
        ]);

        $created_day = $lecture_date_info["Date"]["date"];

        $edate = date(
            "Y-m-d",
            strtotime(" next saturday ", strtotime($created_day))
        );

        $conditions["Soap.created BETWEEN ? AND ?"] = [
            $created_day,
            $edate . " 23:59:59",
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
        $course_list = $this->Course->find("list");
        $this->set("course_list", $course_list);

        //登録
        if ($this->request->is("post")) {
            $this->loadModel("Record");
            $soaps = $this->request->data;
            $created =
                $today_date["year"] .
                "-" .
                $today_date["month"] .
                "-" .
                $today_date["day"];
            foreach ($soaps as &$soap) {
                if (
                    $soap["S"] == "" &&
                    $soap["O"] == "" &&
                    $soap["A"] == "" &&
                    $soap["P"] == ""
                ) {
                    continue;
                }
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

    public function admin_id_edit($soap_id)
    {
        $this->loadModel("Course");
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
        $course_list = $this->Course->find("list");
        $this->set("course_list", $course_list);

        //登録
        if ($this->request->is("post")) {
            $this->loadModel("Record");
            $soap = $this->request->data["Soap"];
            // SOAP記入日で最後に勉強した教材を取得
            $inputed = $soap["today_date"];
            $input_date = $inputed["year"] . "-" . $inputed["month"] . "-" . $inputed["day"];
            $soap["studied_content"] = $this->Record->studiedContentOnTheDate(
                $soap["user_id"],
                $input_date
            );
            if ($this->Soap->save($soap)) {
                $this->Flash->success(__("更新しました、ありがとうございます"));
                return $this->redirect([
                    "controller" => "soaprecords",
                    "action" => "admin_index",
                ]);
            }
            $this->Flash->error(__("更新は失敗しました、もう一回やってください。"));
        }
    }
}

?>
