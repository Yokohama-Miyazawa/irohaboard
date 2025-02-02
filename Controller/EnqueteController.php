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
App::uses("Enquete", "Enquete");
App::uses("Sanitize", "Utility");

class EnqueteController extends AppController
{
    public $helpers = ["Html", "Form"];

    public $components = ["Paginator", "Search.Prg"];

    //public $presetVars = true;

    public $paginate = [
        "maxLimit" => 1000,
    ];

    public $presetVars = [
        [
            "name" => "name",
            "type" => "value",
            "field" => "User.name",
        ],
        [
            "name" => "username",
            "type" => "like",
            "field" => "User.username",
        ],
    ];

    public function index()
    {
        $this->loadModel("Group");
        $this->loadModel("User");

        //$existed = $this->Enquete->isEnqueteExist;

        //今ログインしているUserのidを確認
        $user_id = $this->Auth->user("id");
        $this->set("user_id", $user_id);

        //今日の日付を生成
        $today = date("Y/m/d");
        $this->set("today", $today);

        $conditions = [];
        $conditions["Enquete.user_id"] = $user_id;

        $conditions["Enquete.created BETWEEN ? AND ?"] = [
            $today,
            $today . " 23:59:59",
        ];

        $enquete_history = $this->Enquete->find("first", [
            "conditions" => $conditions,
        ]);

        $enquete_inputted = [];
        $enquete_inputted["Enquete"] = $enquete_history["Enquete"];
        $id = $enquete_history["Enquete"]["id"];

        $this->set("enquete_inputted", $enquete_inputted);

        //グループと、グループリーダー講師の顔写真リストを生成(公開状態のグループのみ)
        $group_data = $this->Group->find("all", [
            "fields" => ["id", "title", "User.pic_path"],
            "conditions" => [
                "status" => 1,
            ],
            "joins" => [[
                "table" => "ib_users",
                "alias" => "User",
                "type" => "LEFT",
                "conditions" => [
                    "User.id = Group.leader_id",
                ],
            ]],
            "recursive" => -1,
        ]);
        $group_leaders = array_map(function($e){
            return [
                "id" => $e["Group"]["id"],
                "title" => $e["Group"]["title"],
                "pic_path" => $e["User"]["pic_path"],
            ];
        }, $group_data);
        $this->set("group_leaders", $group_leaders);

        //今所属するグループのidを探す．
        $group_id = $this->User->findUserGroup($user_id);
        $this->set("group_id", $group_id);

        // 前回設定したゴールを検索
        $previous_next_goal = $this->Enquete->findPreviousNextGoal($user_id);
        if (!$previous_next_goal) {
            $previous_next_goal = "なし";
        }
        $this->set("previous_next_goal", $previous_next_goal);

        if ($this->request->is(["post", "put"])) {
            $this->Enquete->set($this->request->data);
            // もしvalidateに満たさない場合
            if (!$this->Enquete->validates()) {
                $errors = $this->Enquete->validationErrors;
                foreach ($errors as $error) {
                    $this->Session->setFlash($error[0]);
                    return;
                }
            } else {
                $request_data =
                    [
                        "id" => $id,
                        "user_id" => $user_id,
                    ] + $this->request->data;
                $save_data = $request_data;

                // 所属グループを更新
                $this->User->id = $user_id;
                $this->User->saveField("group_id", $request_data["group_id"]);

                if ($this->Enquete->save($save_data)) {
                    $this->Flash->success(
                        __("アンケートは提出されました，ありがとうございます")
                    );

                    $this->redirect("/users_courses");
                } else {
                    $this->Flash->error(
                        __("The enquete could not be saved. Please, try again.")
                    );
                }
            }
        }
    }

    public function records()
    {
        $this->loadModel("Group");
        $user_id = $this->Auth->user("id");

        $this->Prg->commonProcess();
        $conditions = $this->Enquete->parseCriteria($this->Prg->parsedParams());

        $conditions["User.id"] = $user_id;

        $this->Paginator->settings["conditions"] = $conditions;
        $this->Paginator->settings["order"] = "Enquete.created desc";
        $this->Enquete->recursive = 0;

        try {
            $result = $this->paginate();
        } catch (Exception $e) {
            $this->request->params["named"]["page"] = 1;
            $result = $this->paginate();
        }

        $this->set("records", $result);

        //$groups = $this->Group->getGroupList();

        $this->Group = new Group();
        //$this->Course = new Course();
        $this->User = new User();
        //debug($this->User);

        $this->set("groups", $this->Group->find("list"));
        //$this->set('courses',    $this->Course->find('list'));
        $this->set("group_id", $group_id);
        $this->set("name", $name);
        $this->set("period_list", Configure::read("period"));
        $this->set("period", $period);
        $this->set("TF_list", Configure::read("true_or_false"));
        $this->set("from_date", $from_date);
        $this->set("to_date", $to_date);
    }

    /**
     * アンケート一覧（講師側）
     */
    public function admin_index()
    {
        $this->loadModel("Date");
        $this->loadModel("Group");

        $this->Prg->commonProcess();

        $my_id = $this->Auth->user("id");
        $my_group = $this->Group->find("first", [
            "fields" => [
                "id",
                "leader_id"
            ],
            "conditions" => [
                "leader_id" => $my_id,
                "status" => 1,
            ],
            "recursive" => -1,
        ]);
        $my_group_id = empty($my_group) ? "" : $my_group["Group"]["id"];

        // Model の filterArgs に定義した内容にしたがって検索条件を作成
        // ただしアソシエーションテーブルには対応していないため、独自に検索条件を設定する必要がある
        $conditions = $this->Enquete->parseCriteria($this->Prg->parsedParams());

        $group_id = isset($this->request->query["group_id"])
            ? $this->request->query["group_id"]
            : $my_group_id;
        $period = isset($this->request->query["period"])
            ? $this->request->query["period"]
            : "";
        $name = isset($this->request->query["name"])
            ? $this->request->query["name"]
            : "";

        // グループが指定されている場合、記入当時指定したグループに所属していたユーザの履歴を抽出
        if ($group_id != "") {
            $conditions["Enquete.group_id"] = $group_id;
        }

        if ($name != "") {
            $conditions["OR"] = [
                "User.name like" => "%$name%",
                "User.name_furigana like" => "%$name%",
                "User.username like" => "%$name%",
            ];
        }

        if ($period != "") {
            $conditions["User.period"] = $period;
        }

        $last_day = $this->Date->getLastClassDate("Y-m-d");
        $from_date = isset($this->request->query["from_date"])
            ? $this->request->query["from_date"]
            : [
                "year" => date("Y", strtotime($last_day)),
                "month" => date("m", strtotime($last_day)),
                "day" => date("d", strtotime($last_day)),
            ];

        $to_date = isset($this->request->query["to_date"])
            ? $this->request->query["to_date"]
            : ["year" => date("Y"), "month" => date("m"), "day" => date("d")];

        // 学習日付による絞り込み
        $conditions["Enquete.created BETWEEN ? AND ?"] = [
            implode("/", $from_date),
            implode("/", $to_date) . " 23:59:59",
        ];

        // CSV出力モードの場合
        if (@$this->request->query["cmd"] == "csv") {
            $this->autoRender = false;

            // メモリサイズ、タイムアウト時間を設定
            ini_set("memory_limit", "512M");
            ini_set("max_execution_time", 60 * 10);

            // Content-Typeを指定
            $this->response->type("csv");

            header("Content-Type: text/csv");
            header(
                'Content-Disposition: attachment; filename="enquete_records.csv"'
            );

            $fp = fopen("php://output", "w");

            $options = [
                "conditions" => $conditions,
                "order" => "Enquete.created desc",
            ];

            $this->Enquete->recursive = 0;
            $rows = $this->Enquete->find("all", $options);

            $header = [
                "受講日",
                "氏名",
                "担当講師",
                "所属",
                "今日の感想",
                "前回ゴールT/F",
                "前回ゴールF理由",
                "今日のゴール",
                "今日のゴールT/F",
                "今日のゴールF理由",
                "次回までゴール",
            ];

            mb_convert_variables("SJIS-WIN", "UTF-8", $header);
            fputcsv($fp, $header);

            foreach ($rows as $row) {
                if ($row["User"]["period"] == 0) {
                    $class_hour = "1限";
                } elseif ($row["User"]["period"] == 1) {
                    $class_hour = "2限";
                } else {
                    $class_hour = "時限未設定";
                }
                if ($row["Enquete"]["before_goal_cleared"]) {
                    $before_goal_cleared = "True";
                } else {
                    $before_goal_cleared = "False";
                }
                if ($row["Enquete"]["today_goal_cleared"]) {
                    $today_goal_cleared = "True";
                } else {
                    $today_goal_cleared = "False";
                }
                $row = [
                    Utils::getYMDHN($row["Enquete"]["created"]),
                    $row["User"]["name"],
                    $row["Group"]["title"],
                    $class_hour,
                    $row["Enquete"]["today_impressions"],
                    $before_goal_cleared,
                    $row["Enquete"]["before_false_reason"],
                    $row["Enquete"]["today_goal"],
                    $today_goal_cleared,
                    $row["Enquete"]["today_false_reason"],
                    $row["Enquete"]["next_goal"],
                ];

                mb_convert_variables("SJIS-WIN", "UTF-8", $row);

                fputcsv($fp, $row);
            }

            fclose($fp);
        } else {
            if (@$this->request->query["cmd"] == "today") {
                $from_date = [
                    "year" => date("Y"),
                    "month" => date("m"),
                    "day" => date("d"),
                ];
                $to_date = [
                    "year" => date("Y"),
                    "month" => date("m"),
                    "day" => date("d"),
                ];

                // 学習日付による絞り込み
                $conditions["Enquete.created BETWEEN ? AND ?"] = [
                    implode("/", $from_date),
                    implode("/", $to_date) . " 23:59:59",
                ];
            }

            $this->Paginator->settings["conditions"] = $conditions;
            $this->Paginator->settings["order"] = "User.username asc";
            $this->Paginator->settings["limit"] = 100;
            $this->Paginator->settings["maxLimit"] = 100;
            $this->Enquete->recursive = 0;

            try {
                $result = $this->paginate();
            } catch (Exception $e) {
                $this->request->params["named"]["page"] = 1;
                $result = $this->paginate();
            }

            $this->set("records", $result);

            //$groups = $this->Group->getGroupList();

            $this->Group = new Group();
            //$this->Course = new Course();
            $this->User = new User();
            //debug($this->User);

            $this->set("groups", $this->Group->find("list"));
            //$this->set('courses',    $this->Course->find('list'));
            $this->set("group_id", $group_id);
            $this->set("name", $name);
            $this->set("period_list", Configure::read("period"));
            $this->set("period", $period);
            $this->set("TF_list", Configure::read("true_or_false"));
            $this->set("from_date", $from_date);
            $this->set("to_date", $to_date);
        }
    }

    public function admin_submission_status()
    {
        $this->loadModel("User");
        $this->loadModel("Attendance");
        $this->loadModel("Date");

        $last_day = $this->Date->getLastClassDate("Y-m-d");

        $last_class_date_id = $this->Date->getLastClassId();
        //１限に出席した人のリスト
        $period_1_attendance_user_list = $this->Attendance->find("all", [
            "conditions" => [
                "Attendance.date_id" => $last_class_date_id,
                "Attendance.period" => 0,
                "Attendance.status" => 1,
            ],
            "order" => "Attendance.user_id ASC",
        ]);

        $period_1_attendance_ids = array_map(function($attended){
            return $attended["User"]["id"];
        }, $period_1_attendance_user_list);

        $today = date("Y-m-d");
        $from_date =
            date("w") == 0
                ? $today
                : date("Y-m-d", strtotime(" last sunday ", strtotime($today)));
        $to_date = date(
            "Y-m-d",
            strtotime(" next saturday ", strtotime($today))
        );

        /**
         * period_1_submitted = array(
         *   [Member] => array(
         *      string
         *   ),
         *   [cnt] => number
         * )
         */
        $period_1_submitted = [];
        $period_1_submitted["Member"] = "";
        $period_1_submitted["Count"] = 0;

        $period_1_unsubmitted = [];
        $period_1_unsubmitted["Member"] = "";
        $period_1_unsubmitted["Count"] = 0;

        $enquete_data_1 = $this->Enquete->find("all", [
            "fields" => [
                "DISTINCT User.id", "User.name", "Enquete.id", "Enquete.today_impressions"
            ],
            "conditions" => [
                "User.id" => $period_1_attendance_ids,
                "Enquete.created BETWEEN ? AND ?" => [
                    $from_date,
                    $to_date . " 23:59:59",
                ],
            ],
        ]);

        foreach ($enquete_data_1 as $enquete_datum) {
            if ($enquete_datum["Enquete"]["today_impressions"] != "") {
                $period_1_submitted["Member"] =
                    $period_1_submitted["Member"] .
                    $enquete_datum["User"]["name"] .
                    "<br>";
                $period_1_submitted["Count"] += 1;
            } else {
                $period_1_unsubmitted["Member"] =
                    $period_1_unsubmitted["Member"] .
                    $enquete_datum["User"]["name"] .
                    "<br>";
                $period_1_unsubmitted["Count"] += 1;
            }
        }

        $this->set(compact("period_1_submitted", "period_1_unsubmitted"));

        //２限に出席した人のリスト
        $period_2_attendance_user_list = $this->Attendance->find("all", [
            "conditions" => [
                "Attendance.date_id" => $last_class_date_id,
                "Attendance.period" => 1,
                "Attendance.status" => 1,
            ],
            "order" => "Attendance.user_id ASC",
        ]);

        $period_2_attendance_ids = array_map(function($attended){
            return $attended["User"]["id"];
        }, $period_2_attendance_user_list);

        /**
         * period_2_submitted = array(
         *   [Member] => array(
         *      string
         *   ),
         *   [cnt] => number
         * )
         */
        $period_2_submitted = [];
        $period_2_submitted["Member"] = "";
        $period_2_submitted["Count"] = 0;

        $period_2_unsubmitted = [];
        $period_2_unsubmitted["Member"] = "";
        $period_2_unsubmitted["Count"] = 0;

        $enquete_data_2 = $this->Enquete->find("all", [
            "fields" => [
                "DISTINCT User.id", "User.name", "Enquete.id", "Enquete.today_impressions"
            ],
            "conditions" => [
                "User.id" => $period_2_attendance_ids,
                "Enquete.created BETWEEN ? AND ?" => [
                    $from_date,
                    $to_date . " 23:59:59",
                ],
            ],
        ]);

        foreach ($enquete_data_2 as $enquete_datum) {
            if ($enquete_datum["Enquete"]["today_impressions"] != "") {
                $period_2_submitted["Member"] =
                    $period_2_submitted["Member"] .
                    $enquete_datum["User"]["name"] .
                    "<br>";
                $period_2_submitted["Count"] += 1;
            } else {
                $period_2_unsubmitted["Member"] =
                    $period_2_unsubmitted["Member"] .
                    $enquete_datum["User"]["name"] .
                    "<br>";
                $period_2_unsubmitted["Count"] += 1;
            }
        }

        $this->set(compact("period_2_submitted", "period_2_unsubmitted"));

        $this->set(compact("last_day", "last_class_date_id"));
    }

    /**
     * @param int $enquete_id アンケートのID
     * 受講生が提出したアンケートを講師が編集する
     */
    public function admin_edit($enquete_id)
    {
        $this->loadModel("User");

        $edited_enquete = $this->Enquete->find("first", [
            "fields" => [
                "Enquete.id", "Enquete.user_id", "Enquete.group_id",
                "Enquete.before_goal_cleared", "Enquete.before_false_reason",
                "Enquete.today_goal", "Enquete.today_goal_cleared", "Enquete.today_false_reason",
                "Enquete.next_goal", "Enquete.today_impressions", "Enquete.created",
                "User.name", "User.pic_path",
                "Group.title",
            ],
            "conditions" => [
                "Enquete.id" => $enquete_id,
            ],
        ]);
        $this->set("edited_enquete", $edited_enquete["Enquete"]);
        $this->set("user_info", $edited_enquete["User"]);
        $this->set("gruup_info", $edited_enquete["Group"]);

        // 公開状態のグループ一覧を作り，配列の形を整形する
        $group_list = $this->Group->find("list", [
            "conditions" => [
                "status" => 1,
            ],
        ]);
        $this->set("group_list", $group_list);

        // 更新
        if ($this->request->is("post")) {
            $enquete = $this->request->data["Enquete"];

            // 所属グループを更新
            $this->User->id = $enquete["user_id"];
            $this->User->saveField("group_id", $enquete["group_id"]);

            if ($this->Enquete->save($enquete)) {
                $this->Flash->success(__("更新しました。"));
                return $this->redirect([
                    "controller" => "enquete",
                    "action" => "admin_index",
                ]);
            }
            $this->Flash->error(__("更新に失敗しました。もう一回やってください。"));
        }
    }

    /**
     * アンケートの削除
     *
     * @param int $enquete_id 削除するアンケートのID
     */
    public function admin_delete($enquete_id = null)
    {
        if (Configure::read("demo_mode")) {
            return;
        }

        $this->Enquete->id = $enquete_id;
        if (!$this->Enquete->exists()) {
            throw new NotFoundException(__("Invalid Emnquete Data"));
        }
        $this->request->allowMethod("post", "delete");
        if ($this->Enquete->delete()) {
            $this->Flash->success(__("アンケートが削除されました"));
        } else {
            $this->Flash->error(__("アンケートを削除できませんでした"));
        }
        return $this->redirect([
            "action" => "index",
        ]);
    }
}
?>
