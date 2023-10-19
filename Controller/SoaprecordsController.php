<?php
/**
 * Ripple Project
 *
 * @author        Osamu Miyazawa
 * @copyright     NPO Organization uec support
 * @link          http://uecsupport.dip.jp/
 * @license       http://www.gnu.org/licenses/gpl-3.0.en.html GPL License
 */

App::uses("AppController", "Controller");
App::uses("UsersGroup", "UsersGroup");
App::uses("User", "User");
App::uses("Group", "Group");
App::uses("Soap", "Soap");

/**
 * Records Controller
 *
 * @property Record $Record
 * @property PaginatorComponent $Paginator
 */
class SoapRecordsController extends AppController
{
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

    /**
     * SOAP一覧を表示
     */
    public function admin_index()
    {
        $this->loadModel("Soap");
        $this->loadModel("Date");

        // SearchPluginの呼び出し
        $this->Prg->commonProcess("Soap");
        // Model の filterArgs に定義した内容にしたがって検索条件を作成
        // ただしアソシエーションテーブルには対応していないため、独自に検索条件を設定する必要がある
        $conditions = $this->Soap->parseCriteria($this->Prg->parsedParams());

        $last_day = $this->Date->getLastClassDate("Y-m-d");

        $name = isset($this->request->query["name"])
            ? $this->request->query["name"]
            : "";
        $period = isset($this->request->query["period"])
            ? $this->request->query["period"]
            : "";
        $group_id = isset($this->request->query["group_id"])
            ? $this->request->query["group_id"]
            : "";
        $course_id = isset($this->request->query["course_id"])
            ? $this->request->query["course_id"]
            : "";
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

        if ($name != "") {
            $conditions["OR"] = [
                "User.name like" => "%$name%",
                "User.name_furigana like" => "%$name%",
                "User.username like" => "%$name%",
            ];
        }

        // グループが指定されている場合、記入当時指定したグループに所属していたユーザの履歴を抽出
        if ($group_id != "") {
            $conditions["Soap.group_id"] = $group_id;
        }

        if ($period != "") {
            $conditions["User.period"] = $period;
        }

        if ($course_id != "") {
            $conditions["Course.id"] = $course_id;
        }

        // 受講日による絞り込み
        $conditions["Soap.created BETWEEN ? AND ?"] = [
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
                'Content-Disposition: attachment; filename="soap_records.csv"'
            );

            $fp = fopen("php://output", "w");

            $options = [
                "conditions" => $conditions,
                "order" => "Soap.created desc",
            ];

            $this->Soap->recursive = 0;
            $rows = $this->Soap->find("all", $options);

            $header = [
                "受講日",
                "受講生名",
                "担当講師名",
                "1限 or 2限",
                "STEP教材 or 応用教材",
                "S",
                "O",
                "A",
                "P",
                "自由記述",
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
                $row = [
                    Utils::getYMD($row["Soap"]["created"]),
                    $row["User"]["name"],
                    $row["Group"]["title"],
                    $class_hour,
                    $row["Course"]["title"],
                    $row["Soap"]["S"],
                    $row["Soap"]["O"],
                    $row["Soap"]["A"],
                    $row["Soap"]["P"],
                    $row["Soap"]["comment"],
                ];

                mb_convert_variables("SJIS-WIN", "UTF-8", $row);

                fputcsv($fp, $row);
            }

            fclose($fp);
        } else {
            if (@$this->request->query["cmd"] == "today") {
                $this->log("work");

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
                $conditions["Soap.created BETWEEN ? AND ?"] = [
                    implode("/", $from_date),
                    implode("/", $to_date) . " 23:59:59",
                ];
            }
            $this->Paginator->settings["conditions"] = $conditions;
            $this->Paginator->settings["order"] = "User.username asc";
            $this->Paginator->settings["limit"] = 100;
            $this->Paginator->settings["maxLimit"] = 100;
            $this->Soap->recursive = 0;

            try {
                $result = $this->paginate("Soap");
            } catch (Exception $e) {
                $this->request->params["named"]["page"] = 1;
                $result = $this->paginate("Soap");
            }

            $this->set("records", $result);

            $this->Course = new Course();

            $this->set("period_list", ["1限", "2限"]);
            $this->set("groups", $this->Group->find("list"));
            $this->set("courses", $this->Course->find("list"));
            $this->set("name", $name);
            $this->set("period", $period);
            $this->set("group_id", $group_id);
            $this->set("course_id", $course_id);
            $this->set("from_date", $from_date);
            $this->set("to_date", $to_date);

            // 最も古いSoapが作られた年を取得
            $oldest_created_year = $this->Soap->getOldestCreatedYear();
            $this->set("oldest_created_year", $oldest_created_year);
        }
    }

    public function admin_submission_status()
    {
        $this->loadModel("User");
        $this->loadModel("Attendance");
        $this->loadModel("Date");
        $this->loadModel("Soap");

        $last_day = $this->Date->getLastClassDate("Y-m-d");

        $last_class_date_id = $this->Date->getLastClassId();

        //１限に出席した人のリスト
        $period_1_attendance_user_list = $this->Attendance->find("list", [
            "fields" => [
                "User.id",
                "User.name",
            ],
            "conditions" => [
                "Attendance.date_id" => $last_class_date_id,
                "Attendance.period" => 0,
                "Attendance.status" => 1,
            ],
            "order" => "Attendance.user_id ASC",
            "recursive" => 0,
        ]);

        $period_1_attendance_ids = array_keys($period_1_attendance_user_list);

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

        $soap_data_1 = $this->Soap->find("list", [
            "fields" => [
                "User.id",
                "User.name",
            ],
            "conditions" => [
                "User.id" => $period_1_attendance_ids,
                "Soap.created BETWEEN ? AND ?" => [
                    $from_date,
                    $to_date . " 23:59:59",
                ],
            ],
            "recursive" => 0,
        ]);

        $period_1_submitted_array = array_filter(
            $period_1_attendance_user_list,
            function($k) use($soap_data_1) {
                return isset($soap_data_1[$k]);
            },
            ARRAY_FILTER_USE_KEY
        );
        $period_1_submitted = [
            "Member" => implode("<br>", $period_1_submitted_array),
            "Count" => count($period_1_submitted_array),
        ];

        $period_1_unsubmitted_array = array_filter(
            $period_1_attendance_user_list,
            function($k) use($soap_data_1) {
                return !isset($soap_data_1[$k]);
            },
            ARRAY_FILTER_USE_KEY
        );
        $period_1_unsubmitted = [
            "Member" => implode("<br>", $period_1_unsubmitted_array),
            "Count" => count($period_1_unsubmitted_array),
        ];

        $this->set(compact("period_1_submitted", "period_1_unsubmitted"));

        //２限に出席した人のリスト
        $period_2_attendance_user_list = $this->Attendance->find("list", [
            "fields" => [
                "User.id",
                "User.name",
            ],
            "conditions" => [
                "Attendance.date_id" => $last_class_date_id,
                "Attendance.period" => 1,
                "Attendance.status" => 1,
            ],
            "order" => "Attendance.user_id ASC",
            "recursive" => 0,
        ]);

        $period_2_attendance_ids = array_keys($period_2_attendance_user_list);

        /**
         * period_2_submitted = array(
         *   [Member] => array(
         *      string
         *   ),
         *   [cnt] => number
         * )
         */

        $soap_data_2 = $this->Soap->find("list", [
            "fields" => [
                "User.id",
                "User.name",
            ],
            "conditions" => [
                "User.id" => $period_2_attendance_ids,
                "Soap.created BETWEEN ? AND ?" => [
                    $from_date,
                    $to_date . " 23:59:59",
                ],
            ],
            "recursive" => 0,
        ]);

        $period_2_submitted_array = array_filter(
            $period_2_attendance_user_list,
            function($k) use($soap_data_2) {
                return isset($soap_data_2[$k]);
            },
            ARRAY_FILTER_USE_KEY
        );
        $period_2_submitted = [
            "Member" => implode("<br>", $period_2_submitted_array),
            "Count" => count($period_2_submitted_array),
        ];

        $period_2_unsubmitted_array = array_filter(
            $period_2_attendance_user_list,
            function($k) use($soap_data_2) {
                return !isset($soap_data_2[$k]);
            },
            ARRAY_FILTER_USE_KEY
        );
        $period_2_unsubmitted = [
            "Member" => implode("<br>", $period_2_unsubmitted_array),
            "Count" => count($period_2_unsubmitted_array),
        ];

        $this->set(compact("period_2_submitted", "period_2_unsubmitted"));

        $this->set(compact("last_day", "last_class_date_id"));
    }
}
